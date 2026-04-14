<?php
/**
 * migrar_webp.php
 *
 * Converte todas as imagens não-WebP da pasta uploads para WebP,
 * atualiza os registros no banco de dados e apaga os originais.
 *
 * Uso: php migrar_webp.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script deve ser executado via CLI: php migrar_webp.php' . PHP_EOL);
}

// ─── Configuração ──────────────────────────────────────────────────────────────

$uploadDir  = __DIR__ . '/private/uploads/products/';
$maxLargura = 1200;
$qualidade  = 90;

// ─── Conexão com o banco ───────────────────────────────────────────────────────

require_once __DIR__ . '/private/config.php';
// $pdo disponível após o require

// ─── Helpers ───────────────────────────────────────────────────────────────────

function log_msg(string $msg): void
{
    echo $msg . PHP_EOL;
}

function converter_para_webp(string $caminhoOrigem, string $caminhoDestino, int $maxLargura, int $qualidade): bool
{
    $info = @getimagesize($caminhoOrigem);
    if ($info === false) {
        return false;
    }

    $mime = $info['mime'];
    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($mime, $mimesPermitidos, true)) {
        return false;
    }

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($caminhoOrigem),
        'image/png'  => @imagecreatefrompng($caminhoOrigem),
        'image/gif'  => @imagecreatefromgif($caminhoOrigem),
        default      => false,
    };

    if (!$src) {
        return false;
    }

    // Corrige rotação EXIF para JPEG
    if ($mime === 'image/jpeg') {
        $exif = @exif_read_data($caminhoOrigem);
        if ($exif && isset($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3: $src = imagerotate($src, 180, 0);  break;
                case 6: $src = imagerotate($src, -90, 0);  break;
                case 8: $src = imagerotate($src, 90, 0);   break;
            }
        }
    }

    // Redimensiona se necessário
    $larguraOrig = imagesx($src);
    $alturaOrig  = imagesy($src);

    if ($larguraOrig > $maxLargura) {
        $ratio       = $maxLargura / $larguraOrig;
        $novaLargura = $maxLargura;
        $novaAltura  = (int) round($alturaOrig * $ratio);

        $resized = imagecreatetruecolor($novaLargura, $novaAltura);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $bg = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $novaLargura - 1, $novaAltura - 1, $bg);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $novaLargura, $novaAltura, $larguraOrig, $alturaOrig);
        imagedestroy($src);
        $src = $resized;
    }

    $ok = imagewebp($src, $caminhoDestino, $qualidade);
    imagedestroy($src);

    return $ok;
}

// ─── Execução ──────────────────────────────────────────────────────────────────

log_msg('=== Migração de imagens para WebP ===');
log_msg('Diretório: ' . $uploadDir);
log_msg('');

if (!is_dir($uploadDir)) {
    log_msg('ERRO: diretório de uploads não encontrado.');
    exit(1);
}

$extensoesAlvo = ['jpg', 'jpeg', 'png', 'gif'];
$arquivos = scandir($uploadDir);

$convertidos = 0;
$erros       = 0;
$ignorados   = 0;

foreach ($arquivos as $arquivo) {
    if ($arquivo === '.' || $arquivo === '..') {
        continue;
    }

    $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

    if (!in_array($ext, $extensoesAlvo, true)) {
        continue; // já é webp ou outro arquivo
    }

    $caminhoOrigem  = $uploadDir . $arquivo;
    $nomeWebP       = pathinfo($arquivo, PATHINFO_FILENAME) . '.webp';
    $caminhoDestino = $uploadDir . $nomeWebP;

    // Verifica se já existe um webp com esse nome (conversão anterior)
    if (file_exists($caminhoDestino)) {
        log_msg("[IGNORADO] $arquivo → $nomeWebP já existe, pulando.");
        $ignorados++;
        continue;
    }

    log_msg("[CONVERTENDO] $arquivo → $nomeWebP ...");

    $ok = converter_para_webp($caminhoOrigem, $caminhoDestino, $maxLargura, $qualidade);

    if (!$ok) {
        log_msg("  ERRO: não foi possível converter $arquivo.");
        $erros++;
        continue;
    }

    // Atualiza o banco de dados
    $stmt = $pdo->prepare(
        "UPDATE produto_imagens SET arquivo = :novo WHERE arquivo = :antigo"
    );
    $stmt->execute([':novo' => $nomeWebP, ':antigo' => $arquivo]);
    $linhasAfetadas = $stmt->rowCount();

    if ($linhasAfetadas > 0) {
        log_msg("  DB: $linhasAfetadas registro(s) atualizado(s).");
    } else {
        log_msg("  DB: nenhum registro encontrado para '$arquivo' (arquivo órfão).");
    }

    // Apaga o original
    unlink($caminhoOrigem);
    log_msg("  Original apagado.");

    $convertidos++;
}

log_msg('');
log_msg('=== Concluído ===');
log_msg("Convertidos : $convertidos");
log_msg("Erros       : $erros");
log_msg("Ignorados   : $ignorados");
