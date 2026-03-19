<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once __DIR__ . '/auth.php';

exigirLogin();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = trim($_POST['preco'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // DEBUG TEMPORÁRIO
    /*
    echo '<pre>';
    var_dump($_POST);
    var_dump($_FILES);
    echo '</pre>';
    exit;
    */

    if ($nome === '' || $preco === '') {
        $erro = 'Nome e preço são obrigatórios.';
    } elseif (!is_numeric($preco)) {
        $erro = 'Preço inválido.';
    } elseif (
        !isset($_FILES['imagens']) ||
        !isset($_FILES['imagens']['name']) ||
        count(array_filter($_FILES['imagens']['name'])) === 0
    ) {
        $erro = 'Envie pelo menos uma imagem.';
    } else {
        try {
            $pdo->beginTransaction();

            $sqlProduto = "INSERT INTO produtos (nome, descricao, preco, categoria, ativo)
                           VALUES (:nome, :descricao, :preco, :categoria, :ativo)";
            $stmtProduto = $pdo->prepare($sqlProduto);
            $stmtProduto->execute([
                'nome' => $nome,
                'descricao' => $descricao,
                'preco' => $preco,
                'categoria' => $categoria,
                'ativo' => $ativo
            ]);

            $produtoId = $pdo->lastInsertId();

            $uploadDir = dirname(__DIR__) . '/uploads/products/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $arquivos = $_FILES['imagens'];
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            $enviouAoMenosUma = false;

            for ($i = 0; $i < count($arquivos['name']); $i++) {
                if ($arquivos['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $nomeOriginal = $arquivos['name'][$i];
                $tmpName = $arquivos['tmp_name'][$i];
                $tamanho = $arquivos['size'][$i];

                $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

                if (!in_array($ext, $permitidas, true)) {
                    continue;
                }

                if ($tamanho > 5 * 1024 * 1024) {
                    continue;
                }

                $nomeArquivo = uniqid('produto_' . $produtoId . '_', true) . '.' . $ext;
                $caminhoFinal = $uploadDir . $nomeArquivo;

                if (move_uploaded_file($tmpName, $caminhoFinal)) {
                    $sqlImagem = "INSERT INTO produto_imagens (produto_id, arquivo, ordem)
                                  VALUES (:produto_id, :arquivo, :ordem)";
                    $stmtImagem = $pdo->prepare($sqlImagem);
                    $stmtImagem->execute([
                        'produto_id' => $produtoId,
                        'arquivo' => $nomeArquivo,
                        'ordem' => $i
                    ]);

                    $enviouAoMenosUma = true;
                }
            }

            if (!$enviouAoMenosUma) {
                throw new Exception('Nenhuma imagem válida foi enviada.');
            }

            $pdo->commit();

            header('Location: admin-dashboard.php');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Produto - Linea Labs</title>
    <link rel="stylesheet" href="../css/admin_login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Novo produto</h1>
        <a href="admin-dashboard.php" class="btn btn-outline-dark">Voltar</a>
    </div>

    <?php if ($erro !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card p-4">
    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Descrição</label>
        <textarea name="descricao" class="form-control" rows="4"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Preço</label>
        <input type="number" name="preco" class="form-control" step="0.01" min="0" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Categoria</label>
        <input type="text" name="categoria" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Imagens</label>
        <input type="file" name="imagens[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple required>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" name="ativo" class="form-check-input" id="ativo" checked>
        <label class="form-check-label" for="ativo">Produto ativo</label>
    </div>

    <button type="submit" class="btn btn-dark">Salvar produto</button>
</form>
</div>
</body>
</html>