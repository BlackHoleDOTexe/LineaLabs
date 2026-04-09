<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once __DIR__ . '/auth.php';

exigirLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $pdo->beginTransaction();

        $sqlImagens = "SELECT arquivo FROM produto_imagens WHERE produto_id = :produto_id";
        $stmtImagens = $pdo->prepare($sqlImagens);
        $stmtImagens->execute([':produto_id' => $id]);
        $imagens = $stmtImagens->fetchAll();

        $diretorioUploads = dirname(__DIR__, 2) . '/private/uploads/products/';

        foreach ($imagens as $imagem) {
            $nomeArquivo = basename($imagem['arquivo']);
            $caminhoArquivo = $diretorioUploads . $nomeArquivo;

            if (is_file($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }
        }

        $sqlDeleteImagens = "DELETE FROM produto_imagens WHERE produto_id = :produto_id";
        $stmtDeleteImagens = $pdo->prepare($sqlDeleteImagens);
        $stmtDeleteImagens->execute([':produto_id' => $id]);

        $sqlDeleteProduto = "DELETE FROM produtos WHERE id = :id";
        $stmtDeleteProduto = $pdo->prepare($sqlDeleteProduto);
        $stmtDeleteProduto->execute([':id' => $id]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        exit('Erro ao excluir produto: ' . $e->getMessage());
    }
}

header('Location: admin-dashboard.php');
exit;