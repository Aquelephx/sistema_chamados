<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Auth;
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Database;
use App\Services\UsuarioService;
use App\Repositories\UsuarioRepository;
use App\Repositories\SecretariaRepository;
use App\Repositories\LocalizacaoRepository;

class UsuarioController
{
    public function index(Request $request): string
    {
        if (!Auth::isAdmin()) { Url::redirect('/chamados'); exit; }

        $repo = new UsuarioRepository();
        $secRepo = new SecretariaRepository();
        $locRepo = new LocalizacaoRepository();
        
        $pagina = max(1, (int) $request->query('page', 1));
        $porPagina = 10;
        
        $total = $repo->countAll();
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;
        
        $offset = ($pagina - 1) * $porPagina;
        $usuarios = $repo->getAllPaginated($porPagina, $offset);

        // FIX: conta pendentes no banco inteiro, não só na página atual
        $pendentes = (int) Database::connection()
            ->query("SELECT COUNT(*) FROM usuarios WHERE ativo = 1 AND pediu_senha_em IS NOT NULL")
            ->fetchColumn();

        return View::render('usuarios.index', [
            'usuarios' => $usuarios, 
            'pendentes' => $pendentes,
            'pagina' => $pagina, 
            'totalPaginas' => $totalPaginas, 
            'total' => $total,
            'secretarias' => $secRepo->all(),
            'locais' => $locRepo->all()
        ], 'layouts.main');
    }

    public function atualizar(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/usuarios'); exit;
        }

        $usuarioId = (int) $id;
        if ($usuarioId === Auth::id()) {
            Session::flash('erro', 'Você não pode editar seus próprios dados por aqui. Use a página "Meu Perfil".');
            Url::redirect('/usuarios'); exit;
        }

        $perfil = $request->input('perfil');
        if (!in_array($perfil, ['solicitante', 'tecnico', 'admin', 'painel'], true)) {
            Session::flash('erro', 'Perfil inválido.');
            Url::redirect('/usuarios'); exit;
        }

        $secretariaId  = $request->input('secretaria_id')  ? (int)$request->input('secretaria_id')  : null;
        $localizacaoId = $request->input('localizacao_id') ? (int)$request->input('localizacao_id') : null;

        // FIX: valida FKs contra o banco
        if ($secretariaId !== null && (new SecretariaRepository())->find($secretariaId) === null) {
            Session::flash('erro', 'Secretaria inválida.');
            Url::redirect('/usuarios'); exit;
        }
        if ($localizacaoId !== null && (new LocalizacaoRepository())->find($localizacaoId) === null) {
            Session::flash('erro', 'Localização inválida.');
            Url::redirect('/usuarios'); exit;
        }

        $repo = new UsuarioRepository();
        $repo->update($usuarioId, [
            'perfil'          => $perfil,
            'secretaria_id'   => $secretariaId,
            'localizacao_id'  => $localizacaoId
        ]);

        Session::flash('sucesso', 'Usuário atualizado.');
        Url::redirect('/usuarios'); exit;
    }

    public function resetarSenha(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/usuarios'); exit;
        }

        $usuarioId = (int) $id;
        $service = new UsuarioService();
        $res = $service->gerarSenhaTemporaria($usuarioId);

        if ($res->success) {
            $usuarioAlvo = (new UsuarioRepository())->find($usuarioId);
            $nomeSeguro = htmlspecialchars($usuarioAlvo->nome, ENT_QUOTES, 'UTF-8');
            Session::flash('senha_temporaria', "A nova senha de <strong>{$nomeSeguro}</strong> é: <span style='font-size: 1.4em; color: #d9534f; font-family: monospace;'>{$res->data}</span> (anote e repasse agora).");
        } else {
            Session::flash('erro', $res->error);
        }

        Url::redirect('/usuarios'); exit;
    }

    public function alterarStatus(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/usuarios'); exit;
        }

        $usuarioId = (int) $id;
        if ($usuarioId === Auth::id()) {
            Session::flash('erro', 'Você não pode desativar a si mesmo.');
            Url::redirect('/usuarios'); exit;
        }

        $ativar = $request->input('acao') === 'ativar';
        
        $service = new UsuarioService();
        $res = $service->alterarStatus($usuarioId, $ativar);

        Session::flash($res->success ? 'sucesso' : 'erro', $res->success ? $res->data : $res->error);
        Url::redirect('/usuarios'); exit;
    }

    public function formPerfil(Request $request): string
{
    $usuario = Auth::user();
    
    // Busca os nomes reais da secretaria e do local
    $secRepo = new SecretariaRepository();
    $locRepo = new LocalizacaoRepository();
    
    $secretaria = $usuario->secretaria_id ? $secRepo->find($usuario->secretaria_id) : null;
    $local = $usuario->localizacao_id ? $locRepo->find($usuario->localizacao_id) : null;

    return View::render('usuarios.perfil', [
        'usuario' => $usuario,
        'secretaria_nome' => $secretaria ? $secretaria->nome : 'Não informada',
        'local_nome' => $local ? $local->nome : 'Não informado'
    ], 'layouts.main');
}

    public function atualizarPerfil(Request $request): void
    {
        if (!Csrf::validate($request->input('_token'))) {
            Session::flash('erro', 'Sessão expirada.');
            Url::redirect('/meu-perfil'); exit;
        }

        $nome = trim($request->input('nome', ''));
        $email = trim($request->input('email', ''));
        $bio = trim($request->input('bio', ''));
        $usuario = Auth::user();

        if (empty($nome) || empty($email)) {
            Session::flash('erro', 'Nome e e-mail são obrigatórios.');
            Url::redirect('/meu-perfil'); exit;
        }

        $repo = new UsuarioRepository();
        $outroUsuario = $repo->findByEmail($email);
        if ($outroUsuario && $outroUsuario->id !== $usuario->id) {
            Session::flash('erro', 'Este e-mail já está sendo usado por outro usuário.');
            Url::redirect('/meu-perfil'); exit;
        }

        $dadosUpdate = [
            'nome' => $nome,
            'email' => $email,
            'bio' => $bio
        ];

        $file = $request->file('foto');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                $image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
                if ($image) {
                    $largura = imagesx($image);
                    $altura = imagesy($image);
                    $tamanho = min($largura, $altura);
                    $x = ($largura - $tamanho) / 2;
                    $y = ($altura - $tamanho) / 2;
                    
                    $novaImagem = imagecreatetruecolor(200, 200);
                    imagecopyresampled($novaImagem, $image, 0, 0, $x, $y, 200, 200, $tamanho, $tamanho);
                    
                    $nomeArquivo = 'user_' . $usuario->id . '.jpg';
                    $caminhoPasta = dirname(__DIR__, 2) . '/public/assets/avatars/';
                    
                    if (!is_dir($caminhoPasta)) { mkdir($caminhoPasta, 0755, true); }
                    
                    imagejpeg($novaImagem, $caminhoPasta . $nomeArquivo, 85);
                    imagedestroy($novaImagem);
                    imagedestroy($image);
                    
                    $dadosUpdate['foto'] = $nomeArquivo;
                }
            } else {
                Session::flash('erro', 'Formato de imagem inválido. Use JPG, PNG ou WebP.');
                Url::redirect('/meu-perfil'); exit;
            }
        }

        $repo->update($usuario->id, $dadosUpdate);

        Session::flash('sucesso', 'Perfil atualizado com sucesso.');
        Url::redirect('/meu-perfil'); exit;
    }

    public function removerFoto(Request $request): void
    {
        if (!Csrf::validate($request->input('_token'))) {
            Session::flash('erro', 'Sessão expirada.');
            Url::redirect('/meu-perfil'); exit;
        }

        $usuario = Auth::user();
        if ($usuario->foto) {
            $caminhoPasta = dirname(__DIR__, 2) . '/public/assets/avatars/';
            $caminhoArquivo = $caminhoPasta . $usuario->foto;
            if (file_exists($caminhoArquivo)) { unlink($caminhoArquivo); }
            $repo = new UsuarioRepository();
            $repo->update($usuario->id, ['foto' => null]);
            Session::flash('sucesso', 'Foto de perfil removida com sucesso.');
        }
        Url::redirect('/meu-perfil'); exit;
    }

    public function formTrocarSenha(Request $request): string
    {
        return View::render('usuarios.trocar_senha', [], 'layouts.main');
    }

    public function trocarSenha(Request $request): string
    {
        if (!Csrf::validate($request->input('_token'))) {
            return View::render('usuarios.trocar_senha', ['erro' => 'Sessão expirada.'], 'layouts.main');
        }

        $service = new UsuarioService();
        $resultado = $service->alterarSenha(
            Auth::user(), 
            $request->input('senha_atual', ''), 
            $request->input('nova_senha', ''), 
            $request->input('confirmacao', '')
        );

        if (!$resultado->success) {
            return View::render('usuarios.trocar_senha', ['erro' => $resultado->error], 'layouts.main');
        }

        Url::redirect('/chamados'); exit;
    }
}
