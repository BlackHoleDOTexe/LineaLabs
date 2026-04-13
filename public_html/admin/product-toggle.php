<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once __DIR__ . '/auth.php';

exigirLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $sql = "UPDATE produtos
            SET ativo = CASE WHEN ativo = 1 THEN 0 ELSE 1 END
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
}

header('Location: admin-dashboard.php?aba=produtos');
exit;