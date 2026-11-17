<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Result;
use App\Repositories\AnexoRepository;

class AnexoService
{
    private AnexoRepository $repo;

    public function __construct()
    {
        $this->repo = new AnexoRepository();
    }

    public function processarUploads(array $files, int $chamadoId, int $usuarioId): Result
    {
        if (empty($files)) {
            return Result::error('Nenhum arquivo selecionado.');
        }

        $anexosAtuais = $this->repo->contarPorChamado($chamadoId);
        $vagas = 5 - $anexosAtuais;

        if ($vagas <= 0) {
            return Result::error('Este chamado já atingiu o limite de 5 anexos.');
        }

        $enviados = 0;
        $erros = [];
        $limiteBytes = 5 * 1024 * 1024; // 5 MB

        foreach ($files as $file) {
            if ($enviados >= $vagas) {
                $erros[] = "Limite de 5 fotos atingido.";
                break;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $erros[] = "Erro no upload do arquivo {$file['name']}.";
                continue;
            }

            // TRAVA DE SEGURANÇA: Limite de Tamanho
            if ($file['size'] > $limiteBytes) {
                $erros[] = "A imagem {$file['name']} excede o limite de 5MB. Tire uma foto com qualidade menor ou compacte a imagem.";
                continue;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

            if (!in_array($mime, $tiposPermitidos)) {
                $erros[] = "Formato inválido no arquivo {$file['name']}.";
                continue;
            }

            $image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
            if (!$image) {
                $erros[] = "Não foi possível processar a imagem {$file['name']}.";
                continue;
            }

            $largura = imagesx($image);
            $altura = imagesy($image);
            $maxSize = 1000;

            if ($largura > $maxSize || $altura > $maxSize) {
                $ratio = $largura > $altura ? $maxSize / $largura : $maxSize / $altura;
                $novaLargura = (int)($largura * $ratio);
                $novaAltura = (int)($altura * $ratio);
                
                $novaImagem = imagecreatetruecolor($novaLargura, $novaAltura);
                imagealphablending($novaImagem, false);
                imagesavealpha($novaImagem, true);
                imagecopyresampled($novaImagem, $image, 0, 0, 0, 0, $novaLargura, $novaAltura, $largura, $altura);
                imagedestroy($image);
                $image = $novaImagem;
            }

            ob_start();
            imagejpeg($image, null, 70);
            $conteudo = ob_get_clean();
            imagedestroy($image);

            $this->repo->create([
                'chamado_id' => $chamadoId,
                'usuario_id' => $usuarioId,
                'nome_original' => pathinfo($file['name'], PATHINFO_FILENAME) . '.jpg',
                'mime' => 'image/jpeg',
                'tamanho' => strlen($conteudo),
                'conteudo' => $conteudo
            ]);
            $enviados++;
        }

        if ($enviados > 0 && empty($erros)) {
            return Result::ok("{$enviados} foto(s) anexada(s) com sucesso.");
        } elseif ($enviados > 0 && !empty($erros)) {
            return Result::ok("{$enviados} foto(s) enviada(s). " . implode(' ', $erros));
        }

        return Result::error(implode(' ', $erros));
    }
}