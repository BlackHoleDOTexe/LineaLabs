<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once __DIR__ . '/auth.php';

exigirLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $sql = "DELETE FROM produtos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
}

header('Location: admin-dashboard.php');
exit;