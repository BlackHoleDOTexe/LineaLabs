<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/includes/image-helper.php';

exigirLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: admin-dashboard.php?aba=produtos');
    exit;
}

// Carrega produto
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch();

if (!$produto) {
    header('Location: admin-dashboard.php?aba=produtos');
    exit;
}

$erro    = '';
$sucesso = '';

// ----------------------------------------------------------------
// Ação: Excluir imagem individual
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_imagem'])) {
    verificarCsrf();

    $imagemId = filter_input(INPUT_POST, 'imagem_id', FILTER_VALIDATE_INT);

    if ($imagemId) {
        $stmtImg = $pdo->prepare(
            "SELECT * FROM produto_imagens WHERE id = :id AND produto_id = :pid LIMIT 1"
        );
        $stmtImg->execute([':id' => $imagemId, ':pid' => $id]);
        $imagem = $stmtImg->fetch();

        if ($imagem) {
            $caminho = dirname(__DIR__, 2) . '/private/uploads/products/' . basename($imagem['arquivo']);
            if (is_file($caminho)) {
                unlink($caminho);
            }

            $pdo->prepare("DELETE FROM produto_imagens WHERE id = :id")
                ->execute([':id' => $imagemId]);
        }
    }

    header('Location: product-edit.php?id=' . $id . '&ok=img_excluida');
    exit;
}

// ----------------------------------------------------------------
// Ação: Adicionar nova imagem (com conversão WebP)
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_imagem'])) {
    verificarCsrf();

    if (empty($_FILES['nova_imagem']['name'])) {
        $erro = 'Selecione uma imagem para enviar.';
    } elseif ($_FILES['nova_imagem']['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Erro ao receber o arquivo. Tente novamente.';
    } elseif ($_FILES['nova_imagem']['size'] > 10 * 1024 * 1024) {
        $erro = 'A imagem deve ter no máximo 10 MB.';
    } else {
        $uploadDir = dirname(__DIR__, 2) . '/private/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $prefixo     = 'produto_' . $id . '_';
        $nomeArquivo = processarImagemWebP($_FILES['nova_imagem']['tmp_name'], $uploadDir, $prefixo);

        if ($nomeArquivo === false) {
            $erro = 'Formato inválido. Use JPG, JPEG, PNG, WebP ou GIF.';
        } else {
            $stmtOrdem = $pdo->prepare(
                "SELECT COALESCE(MAX(ordem), 0) + 1 FROM produto_imagens WHERE produto_id = :pid"
            );
            $stmtOrdem->execute([':pid' => $id]);
            $proximaOrdem = (int) $stmtOrdem->fetchColumn();

            $pdo->prepare(
                "INSERT INTO produto_imagens (produto_id, arquivo, ordem)
                 VALUES (:pid, :arquivo, :ordem)"
            )->execute([':pid' => $id, ':arquivo' => $nomeArquivo, ':ordem' => $proximaOrdem]);

            header('Location: product-edit.php?id=' . $id . '&ok=img_adicionada');
            exit;
        }
    }
}

// ----------------------------------------------------------------
// Ação: Salvar dados do produto
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_produto'])) {
    verificarCsrf();

    $nome      = trim($_POST['nome']      ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $dimensoes = trim($_POST['dimensoes'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco     = str_replace(',', '.', trim($_POST['preco'] ?? ''));
    $ativo     = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '') {
        $erro = 'O nome do produto é obrigatório.';
    } elseif (!is_numeric($preco) || (float) $preco < 0) {
        $erro = 'Informe um preço válido (ex: 29,90).';
    } else {
        $pdo->prepare(
            "UPDATE produtos
             SET nome = :nome, descricao = :descricao, dimensoes = :dimensoes,
                 preco = :preco, categoria = :categoria, ativo = :ativo
             WHERE id = :id"
        )->execute([
            ':nome'      => $nome,
            ':descricao' => $descricao !== '' ? $descricao : null,
            ':dimensoes' => $dimensoes !== '' ? $dimensoes : null,
            ':preco'     => (float) $preco,
            ':categoria' => $categoria !== '' ? $categoria : null,
            ':ativo'     => $ativo,
            ':id'        => $id,
        ]);

        header('Location: product-edit.php?id=' . $id . '&ok=salvo');
        exit;
    }
}

// ----------------------------------------------------------------
// Recarrega produto e imagens (após possíveis alterações)
// ----------------------------------------------------------------
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch();

$stmtImagens = $pdo->prepare(
    "SELECT * FROM produto_imagens WHERE produto_id = :pid ORDER BY ordem, id"
);
$stmtImagens->execute([':pid' => $id]);
$imagens = $stmtImagens->fetchAll();

// Categorias para datalist
$sqlCats    = "SELECT DISTINCT categoria FROM produtos
               WHERE categoria IS NOT NULL AND categoria <> ''
               ORDER BY categoria";
$categorias = $pdo->query($sqlCats)->fetchAll(PDO::FETCH_COLUMN);

// Mensagem de sucesso via redirect
if (isset($_GET['ok'])) {
    $mensagensOk = [
        'salvo'         => 'Produto atualizado com sucesso!',
        'img_adicionada'=> 'Imagem adicionada com sucesso!',
        'img_excluida'  => 'Imagem excluída com sucesso!',
    ];
    $sucesso = $mensagensOk[$_GET['ok']] ?? '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto — Linea Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?= APP_version ?>">
</head>
<body class="admin-body">
<div class="admin-wrapper">

    <aside class="admin-sidebar">
        <h2 class="logo mb-4">Linea Labs</h2>
        <nav class="nav flex-column gap-1">
            <a class="nav-link" href="admin-dashboard.php?aba=produtos">
                <i class="bi bi-box-seam me-2"></i>Produtos
            </a>
            <a class="nav-link active" href="#">
                <i class="bi bi-pencil me-2"></i>Editar Produto
            </a>
            <a class="nav-link" href="admin-dashboard.php?aba=orcamentos">
                <i class="bi bi-calculator me-2"></i>Orçamentos
            </a>
            <a class="nav-link" href="admin-dashboard.php?aba=configuracoes">
                <i class="bi bi-gear me-2"></i>Configurações
            </a>
        </nav>
        <div class="mt-auto pt-4">
            <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Sair
            </a>
        </div>
    </aside>

    <main class="admin-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Editar Produto</h1>
                <p class="text-muted mb-0">
                    ID #<?= (int) $produto['id'] ?> &mdash;
                    <?= htmlspecialchars($produto['nome']) ?>
                </p>
            </div>
            <a href="admin-dashboard.php?aba=produtos" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <!-- Feedback -->
        <?php if ($sucesso !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($sucesso) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($erro !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ====== Formulário de dados ====== -->
        <div class="admin-card mb-4">
            <h2 class="h5 mb-4">Dados do Produto</h2>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="row g-3">

                    <div class="col-12">
                        <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" id="nome" name="nome" class="form-control"
                               value="<?= htmlspecialchars($_POST['nome'] ?? $produto['nome']) ?>"
                               required>
                    </div>

                    <div class="col-12">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control"
                                  rows="5"><?= htmlspecialchars($_POST['descricao'] ?? ($produto['descricao'] ?? '')) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="dimensoes" class="form-label">Dimensões</label>
                        <input type="text" id="dimensoes" name="dimensoes" class="form-control"
                               value="<?= htmlspecialchars($_POST['dimensoes'] ?? ($produto['dimensoes'] ?? '')) ?>"
                               placeholder="Ex: 30cm × 21cm × 9mm">
                    </div>

                    <div class="col-md-3">
                        <label for="preco" class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                        <input type="text" id="preco" name="preco" class="form-control"
                               value="<?= htmlspecialchars($_POST['preco'] ?? number_format((float) $produto['preco'], 2, ',', '.')) ?>"
                               inputmode="decimal" required>
                    </div>

                    <div class="col-md-3">
                        <label for="categoria" class="form-label">Categoria</label>
                        <input type="text" id="categoria" name="categoria" class="form-control"
                               value="<?= htmlspecialchars($_POST['categoria'] ?? ($produto['categoria'] ?? '')) ?>"
                               placeholder="Ex: Decorativo"
                               list="lista-categorias">
                        <datalist id="lista-categorias">
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" id="ativo" name="ativo" class="form-check-input"
                                   <?php
                                        $ativoVal = isset($_POST['salvar_produto'])
                                            ? isset($_POST['ativo'])
                                            : (int) $produto['ativo'] === 1;
                                        echo $ativoVal ? 'checked' : '';
                                   ?>>
                            <label for="ativo" class="form-check-label">
                                Produto ativo (visível no catálogo)
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <button type="submit" name="salvar_produto" class="btn btn-dark">
                    <i class="bi bi-save me-1"></i>Salvar Alterações
                </button>
            </form>
        </div>

        <!-- ====== Galeria de imagens ====== -->
        <div class="admin-card mb-4">
            <h2 class="h5 mb-4">
                <i class="bi bi-images me-2 text-muted"></i>Imagens
                <span class="badge text-bg-light border ms-1"><?= count($imagens) ?></span>
            </h2>

            <?php if (empty($imagens)): ?>
                <div class="alert alert-secondary">
                    Este produto ainda não possui imagens.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($imagens as $imagem): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm">
                                <img
                                    src="/media/image.php?file=<?= urlencode($imagem['arquivo']) ?>"
                                    class="card-img-top"
                                    alt="Imagem do produto"
                                    style="height:180px;object-fit:cover;"
                                    loading="lazy"
                                >
                                <div class="card-footer p-2 bg-white border-top">
                                    <small class="text-muted d-block mb-1" style="font-size:.7rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        <?= htmlspecialchars($imagem['arquivo']) ?>
                                    </small>
                                    <form method="POST"
                                          onsubmit="return confirm('Excluir esta imagem permanentemente?')">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="imagem_id" value="<?= (int) $imagem['id'] ?>">
                                        <button type="submit" name="excluir_imagem"
                                                class="btn btn-outline-danger btn-sm w-100">
                                            <i class="bi bi-trash me-1"></i>Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ====== Adicionar nova imagem ====== -->
        <div class="admin-card">
            <h2 class="h5 mb-3">
                <i class="bi bi-cloud-upload me-2 text-muted"></i>Adicionar Imagem
            </h2>
            <p class="text-muted small mb-3">
                Aceita JPG, JPEG, PNG, WebP ou GIF (até 10 MB). A imagem é automaticamente
                convertida para <strong>.webp</strong> e redimensionada para no máximo 1200 px.
            </p>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="nova_imagem" class="form-label">Selecionar arquivo</label>
                        <input type="file" id="nova_imagem" name="nova_imagem"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,.gif" required>
                        <!-- Preview da imagem selecionada -->
                        <div id="preview-nova" class="mt-2"></div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="adicionar_imagem" class="btn btn-dark w-100">
                            <i class="bi bi-upload me-1"></i>Enviar Imagem
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preview da nova imagem antes do envio
document.getElementById('nova_imagem').addEventListener('change', function () {
    const container = document.getElementById('preview-nova');
    container.innerHTML = '';

    const file = this.files[0];
    if (!file || !file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'rounded border';
        img.style.cssText = 'max-height:120px;max-width:200px;object-fit:cover;';
        container.appendChild(img);
    };
    reader.readAsDataURL(file);
});
</script>
</body>
</html>
