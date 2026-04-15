<?php
require_once dirname(__DIR__, 3) . '/private/config.php';
require_once dirname(__DIR__, 2) . '/app/Service/Auth.php';
require_once dirname(__DIR__, 2) . '/app/Repository/ProductRepository.php';

exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?aba=produtos');
    exit;
}

verificarCsrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id && $id > 0) {
    try {
        $productRepo = new ProductRepository($pdo);
        $productRepo->toggleStatus($id);
        error_log('[toggle] Produto ' . $id . ' toggle realizado com sucesso');
    } catch (Throwable $e) {
        error_log('[toggle] Erro ao toggle produto ' . $id . ': ' . $e->getMessage());
        header('Location: ../index.php?aba=produtos&erro=toggle_falhou');
        exit;
    }
} else {
    error_log('[toggle] ID inválido ou não fornecido');
}

header('Location: ../index.php?aba=produtos');
exit;
