<?php
declare(strict_types=1);

$arquivo = $_GET['file'] ?? '';

if ($arquivo === '') {
    http_response_code(400);
    exit('Arquivo não informado.');
}

$nomeArquivo = basename($arquivo);

if (!preg_match('/^[a-zA-Z0-9._-]+$/', $nomeArquivo)) {
    http_response_code(400);
    exit('Nome de arquivo inválido.');
}

$caminhoBase = dirname(__DIR__, 2) . '/private/uploads/products/';
$caminhoCompleto = $caminhoBase . $nomeArquivo;

if (!is_file($caminhoCompleto)) {
    http_response_code(404);
    exit('Imagem não encontrada.');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $caminhoCompleto);
finfo_close($finfo);

$tiposPermitidos = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif'
];

if (!in_array($mime, $tiposPermitidos, true)) {
    http_response_code(403);
    exit('Tipo de arquivo não permitido.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($caminhoCompleto));
header('Cache-Control: public, max-age=2592000');
header('X-Content-Type-Options: nosniff');

readfile($caminhoCompleto);
exit;