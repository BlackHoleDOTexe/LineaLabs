<?php
/**
 * Helper de processamento de imagens.
 * Converte qualquer imagem enviada para WebP e redimensiona se necessário.
 */

/**
 * Processa uma imagem enviada via upload:
 * - Valida o tipo MIME real (via getimagesize, não extensão)
 * - Redimensiona para no máximo $maxLargura px de largura, mantendo proporção
 * - Converte e salva como .webp
 *
 * @param  string $tmpFile     Caminho do arquivo temporário ($_FILES[...]['tmp_name'])
 * @param  string $diretorio   Diretório de destino (com barra final)
 * @param  string $prefixo     Prefixo para o nome do arquivo gerado
 * @param  int    $maxLargura  Largura máxima em pixels (default: 1200)
 * @param  int    $qualidade   Qualidade WebP de 0–100 (default: 85)
 * @return string|false        Nome do arquivo .webp gerado, ou false em caso de erro
 */
function processarImagemWebP(
    string $tmpFile,
    string $diretorio,
    string $prefixo,
    int $maxLargura = 1200,
    int $qualidade  = 85
): string|false {

    $info = @getimagesize($tmpFile);

    if ($info === false) {
        return false;
    }

    $mime = $info['mime'];
    $mimePermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($mime, $mimePermitidos, true)) {
        return false;
    }

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmpFile),
        'image/png'  => @imagecreatefrompng($tmpFile),
        'image/webp' => @imagecreatefromwebp($tmpFile),
        'image/gif'  => @imagecreatefromgif($tmpFile),
        default      => false,
    };

    if (!$src) {
        return false;
    }

    $larguraOrig = imagesx($src);
    $alturaOrig  = imagesy($src);

    // Redimensiona se a largura exceder o máximo permitido
    if ($larguraOrig > $maxLargura) {
        $ratio       = $maxLargura / $larguraOrig;
        $novaLargura = $maxLargura;
        $novaAltura  = (int) round($alturaOrig * $ratio);

        $resized = imagecreatetruecolor($novaLargura, $novaAltura);

        // Preserva canal alfa (transparência de PNG/WebP)
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $bg = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $novaLargura - 1, $novaAltura - 1, $bg);

        imagecopyresampled(
            $resized, $src,
            0, 0, 0, 0,
            $novaLargura, $novaAltura,
            $larguraOrig, $alturaOrig
        );

        imagedestroy($src);
        $src = $resized;
    }

    $nomeArquivo  = uniqid($prefixo, true) . '.webp';
    $caminhoFinal = $diretorio . $nomeArquivo;

    $ok = imagewebp($src, $caminhoFinal, $qualidade);
    imagedestroy($src);

    return $ok ? $nomeArquivo : false;
}
