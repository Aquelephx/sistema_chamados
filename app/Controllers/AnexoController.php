<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Url;
use App\Core\Csrf;
use App\Repositories\AnexoRepository;
use App\Repositories\ChamadoRepository;

class AnexoController
{
    public function show(Request $request, string $id): void
    {
        $repo = new AnexoRepository();
        $anexo = $repo->find((int) $id);

        if ($anexo === null) { http_response_code(404); echo "Anexo não encontrado."; return; }

        $chamadoRepo = new ChamadoRepository();
        $chamado = $chamadoRepo->find($anexo->chamado_id);
        $usuario = Auth::user();

        if ($chamado->solicitante_id !== $usuario->id && $usuario->perfil === 'solicitante') {
            http_response_code(403); echo "Acesso negado."; return;
        }

        header('Content-Type: ' . $anexo->mime);
        header('Content-Length: ' . $anexo->tamanho);
        header('Cache-Control: max-age=86400');
        echo $anexo->conteudo;
        exit;
    }

    public function destroy(Request $request, string $id): void
    {
        // SEGURANÇA: Validar CSRF
        if (!Csrf::validate($request->input('_token'))) {
            Url::redirect('/chamados'); exit;
        }

        $repo = new AnexoRepository();
        $anexo = $repo->find((int) $id);

        if ($anexo === null) { Url::redirect('/chamados'); exit; }

        $usuario = Auth::user();
        // SEGURANÇA: Só o dono do anexo ou admin pode apagar
        if ($anexo->usuario_id !== $usuario->id && $usuario->perfil !== 'admin') {
            Url::redirect('/chamados/' . $anexo->chamado_id); exit;
        }

        $chamadoId = $anexo->chamado_id;
        $repo->delete($anexo->id);

        Url::redirect('/chamados/' . $chamadoId); exit;
    }
}
