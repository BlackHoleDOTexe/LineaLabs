<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once __DIR__ . '/auth.php';

exigirLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: admin-dashboard.php');
    exit;
}

$sql = "SELECT * FROM produtos WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch();

if (!$produto) {
    header('Location: admin-dashboard.php');
    exit;
}

$erro = '';

$sqlImagens = "SELECT * FROM produto_imagens WHERE produto_id = :produto_id ORDER BY ordem, id";
$stmtImagens = $pdo->prepare($sqlImagens);
$stmtImagens->execute([':produto_id' => $id]);
$imagens = $stmtImagens->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_imagem'])) {
    $imagemId = filter_input(INPUT_POST, 'imagem_id', FILTER_VALIDATE_INT);

    if ($imagemId) {
        $sqlImagem = "SELECT * FROM produto_imagens WHERE id = :id AND produto_id = :produto_id LIMIT 1";
        $stmtImagem = $pdo->prepare($sqlImagem);
        $stmtImagem->execute([
            ':id' => $imagemId,
            ':produto_id' => $id
        ]);
        $imagem = $stmtImagem->fetch();

        if ($imagem) {
            $caminhoArquivo = dirname(__DIR__, 2) . '/private/uploads/products/' . $imagem['arquivo'];

            if (is_file($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }

            $sqlDeleteImagem = "DELETE FROM produto_imagens WHERE id = :id";
            $stmtDeleteImagem = $pdo->prepare($sqlDeleteImagem);
            $stmtDeleteImagem->execute([':id' => $imagemId]);
        }
    }

    header("Location: product-edit.php?id=" . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_imagem'])) {
    if (!empty($_FILES['nova_imagem']['name'])) {
        $arquivo = $_FILES['nova_imagem'];

        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            if ($arquivo['size'] > 5 * 1024 * 1024) {
                $erro = 'A imagem deve ter no máximo 5MB.';
            } else {
                $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
                $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($extensao, $extensoesPermitidas, true)) {
                    $nomeArquivo = uniqid('produto_', true) . '.' . $extensao;
                    $diretorioUpload = dirname(__DIR__ , 2) . '/private/uploads/products/';
                    $caminhoDestino = $diretorioUpload . $nomeArquivo;

                    if (!is_dir($diretorioUpload)) {
                        mkdir($diretorioUpload, 0777, true);
                    }

                    if (move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
                        $sqlOrdem = "SELECT COALESCE(MAX(ordem), 0) + 1 AS proxima_ordem
                                     FROM produto_imagens
                                     WHERE produto_id = :produto_id";
                        $stmtOrdem = $pdo->prepare($sqlOrdem);
                        $stmtOrdem->execute([':produto_id' => $id]);
                        $proximaOrdem = (int) $stmtOrdem->fetchColumn();

                        $sqlInsertImagem = "INSERT INTO produto_imagens (produto_id, arquivo, ordem)
                                            VALUES (:produto_id, :arquivo, :ordem)";
                        $stmtInsertImagem = $pdo->prepare($sqlInsertImagem);
                        $stmtInsertImagem->execute([
                            ':produto_id' => $id,
                            ':arquivo' => $nomeArquivo,
                            ':ordem' => $proximaOrdem
                        ]);

                        header("Location: product-edit.php?id=" . $id);
                        exit;
                    } else {
                        $erro = 'Não foi possível mover a imagem enviada.';
                    }
                } else {
                    $erro = 'Formato de imagem inválido. Use JPG, JPEG, PNG ou WEBP.';
                }
            }
        } else {
            $erro = 'Erro ao enviar a imagem.';
        }
    } else {
        $erro = 'Selecione uma imagem para enviar.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_produto'])) {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(',', '.', $_POST['preco'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '') {
        $erro = 'O nome do produto é obrigatório.';
    } elseif (!is_numeric($preco)) {
        $erro = 'Informe um preço válido.';
    } else {
        $sqlUpdate = "UPDATE produtos
                      SET nome = :nome, descricao = :descricao, preco = :preco, ativo = :ativo
                      WHERE id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':preco' => $preco,
            ':ativo' => $ativo,
            ':id' => $id
        ]);

        header('Location: admin-dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Linea Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">

        <aside class="admin-sidebar">
            <h2 class="logo mb-4">Linea Labs</h2>

            <nav class="nav flex-column gap-2">
                <a class="nav-link" href="admin-dashboard.php">Dashboard</a>
                <a class="nav-link active" href="#">Editar produto</a>
                <a class="nav-link text-danger" href="logout.php">Sair</a>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Editar produto</h1>
                        <p class="text-muted mb-0">Atualize as informações do produto</p>
                    </div>

                    <a href="admin-dashboard.php" class="btn btn-outline-dark btn-sm">Voltar</a>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input
                            type="text"
                            class="form-control"
                            id="nome"
                            name="nome"
                            value="<?= htmlspecialchars($_POST['nome'] ?? $produto['nome']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea
                            class="form-control"
                            id="descricao"
                            name="descricao"
                            rows="5"
                        ><?= htmlspecialchars($_POST['descricao'] ?? $produto['descricao']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="preco" class="form-label">Preço</label>
                        <input
                            type="text"
                            class="form-control"
                            id="preco"
                            name="preco"
                            value="<?= htmlspecialchars($_POST['preco'] ?? number_format((float)$produto['preco'], 2, ',', '.')) ?>"
                            required
                        >
                    </div>

                    <div class="form-check mb-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="ativo"
                            name="ativo"
                            <?= (isset($_POST['ativo']) || (!$_POST && (int)$produto['ativo'] === 1)) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="ativo">
                            Produto ativo
                        </label>
                    </div>

                    <button type="submit" name="salvar_produto" class="btn btn-dark">
                        Salvar alterações
                    </button>
                </form>

                <hr class="my-5">

                <h2 class="h4 mb-3">Imagens do produto</h2>

                <div class="row g-3 mb-4">
                    <?php if (!empty($imagens)): ?>
                        <?php foreach ($imagens as $imagem): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card h-100 shadow-sm">
                                    <img
                                        src="/media/image.php?file=<?= urlencode($imagem['arquivo']) ?>"
                                        class="card-img-top"
                                        alt="Imagem do produto"
                                        style="height: 220px; object-fit: cover;"
                                    >

                                    <div class="card-body p-2">
                                        <form method="POST" onsubmit="return confirm('Deseja realmente excluir esta imagem?');">
                                            <input type="hidden" name="imagem_id" value="<?= $imagem['id'] ?>">
                                            <button
                                                type="submit"
                                                name="excluir_imagem"
                                                class="btn btn-danger btn-sm w-100"
                                            >
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-secondary mb-0">
                                Este produto ainda não possui imagens.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 class="h5 mb-3">Adicionar nova imagem</h3>

                <form method="POST" enctype="multipart/form-data" class="border rounded p-3 bg-light">
                    <div class="mb-3">
                        <label for="nova_imagem" class="form-label">Selecionar imagem</label>
                        <input
                            type="file"
                            class="form-control"
                            id="nova_imagem"
                            name="nova_imagem"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >
                    </div>

                    <button type="submit" name="adicionar_imagem" class="btn btn-dark">
                        Enviar imagem
                    </button>
                </form>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>