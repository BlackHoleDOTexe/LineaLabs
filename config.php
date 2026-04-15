<?php

$env = parse_ini_file(__DIR__ . '/.env');

if (!$env) {
    die('Erro interno. Tente novamente mais tarde.');
}

$host = $env['DB_HOST'];
$dbname = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$charset = $env['DB_CHARSET'];

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('[config] DB connection error: ' . $e->getMessage());
    die('Erro interno. Tente novamente mais tarde.');
}

//versionamento para cache busting
define('APP_version', '3.3.0');

// Dados da Empresa (Identidade e Fiscal)
define('EMP_NOME_FANTASIA', 'Linea Labs');
define('EMP_RAZAO_SOCIAL', '66.043.362 EDUARDO FELIPE SCHMIDT DE GODOY'); // [cite: 1464]
define('EMP_CNPJ', '66.043.362/0001-78'); // 
define('EMP_IE', '91221792-20'); // 
define('EMP_ENDERECO', 'Rua Angelo Giachini, Toledo - PR'); // 
define('EMP_TELEFONE', '(44) 99755-4052');
define('EMP_WHATSAPP', '5544997554052');

// Segurança do painel admin
define('ADMIN_SESSION_TIMEOUT', 600); // 10 minutos de inatividade