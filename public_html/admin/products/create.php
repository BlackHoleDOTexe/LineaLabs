<?php
require_once dirname(__DIR__, 3) . '/private/config.php';
require_once dirname(__DIR__, 2) . '/app/Service/Auth.php';
require_once dirname(__DIR__, 2) . '/app/Service/Image.php';
require_once dirname(__DIR__, 2) . '/app/Repository/ProductRepository.php';
require_once dirname(__DIR__, 2) . '/app/Repository/ImageRepository.php';

exigirLogin();

$productRepo = new ProductRepository($pdo);
$imageRepo   = new ImageRepository($pdo);

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $nome      = trim($_POST['nome']      ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $dimensoes = trim($_POST['dimensoes'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $ativo     = isset($_POST['ativo']) ? 1 : 0;

    $preco = str_replace(',', '.', trim($_POST['preco'] ?? ''));

    if ($nome === '') {
        $erro = 'O nome do produto é obrigatório.';
    } elseif (!is_numeric($preco) || (float) $preco < 0) {
        $erro = 'Informe um preço válido (ex: 29,90).';
    } elseif (
        empty($_FILES['imagens']['name'][0])
        || count(array_filter($_FILES['imagens']['name'])) === 0
    ) {
        $erro = 'Envie pelo menos uma imagem para o produto.';
    } else {
        try {
            $pdo->beginTransaction();

            $produtoId = $productRepo->create([
                'nome'      => $nome,
                'descricao' => $descricao !== '' ? $descricao : null,
                'dimensoes' => $dimensoes !== '' ? $dimensoes : null,
                'preco'     => (float) $preco,
                'categoria' => $categoria !== '' ? $categoria : null,
                'ativo'     => $ativo,
            ]);

            $uploadDir = dirname(__DIR__, 3) . '/private/uploads/products/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $arquivos         = $_FILES['imagens'];
            $enviouAoMenosUma = false;
            $errosImagem      = [];

            for ($i = 0, $qtd = count($arquivos['name']); $i < $qtd; $i++) {
                if ($arquivos['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $tmpFile = $arquivos['tmp_name'][$i];
                $tamanho = $arquivos['size'][$i];

                if ($tamanho > 10 * 1024 * 1024) {
                    $errosImagem[] = "Imagem " . ($i + 1) . " excede 10 MB — ignorada.";
                    continue;
                }

                $prefixo     = 'produto_' . $produtoId . '_';
                $nomeArquivo = processarImagemWebP($tmpFile, $uploadDir, $prefixo);

                if ($nomeArquivo === false) {
                    $errosImagem[] = "Imagem " . ($i + 1) . " inválida ou não pôde ser convertida — ignorada.";
                    continue;
                }

                $imageRepo->create($produtoId, $nomeArquivo, $i);
                $enviouAoMenosUma = true;
            }

            if (!$enviouAoMenosUma) {
                throw new RuntimeException(
                    'Nenhuma imagem válida foi processada. ' . implode(' ', $errosImagem)
                );
            }

            $pdo->commit();

            header('Location: ../index.php?aba=produtos&msg=produto_criado');
            exit;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[products/create] ' . $e->getMessage());
            $erro = 'Erro interno ao salvar o produto. Tente novamente.';
        }
    }
}

$categorias = $productRepo->getAllCategories();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Produto — Linea Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/admin_dashboard.css?v=<?= APP_version ?>">
</head>
<body class="admin-body">
<div class="admin-wrapper">

    <aside class="admin-sidebar">
        <h2 class="logo mb-4">Linea Labs</h2>
        <nav class="nav flex-column gap-1">
            <a class="nav-link" href="../index.php?aba=produtos">
                <i class="bi bi-box-seam me-2"></i>Produtos
            </a>
            <a class="nav-link active" href="#">
                <i class="bi bi-plus-circle me-2"></i>Novo Produto
            </a>
            <a class="nav-link" href="../index.php?aba=orcamentos">
                <i class="bi bi-calculator me-2"></i>Orçamentos
            </a>
            <a class="nav-link" href="../index.php?aba=configuracoes">
                <i class="bi bi-gear me-2"></i>Configurações
            </a>
        </nav>
        <div class="mt-auto pt-4">
            <a class="nav-link text-danger" href="../logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Sair
            </a>
        </div>
    </aside>

    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Novo Produto</h1>
                <p class="text-muted mb-0">Preencha os dados e envie as imagens</p>
            </div>
            <a href="../index.php?aba=produtos" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <?php if ($erro !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" id="nome" name="nome" class="form-control"
                               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                               placeholder="Ex: Cruz Decorativa em MDF" required>
                    </div>

                    <div class="col-12">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" rows="5"
                                  placeholder="Descreva o produto em detalhes..."><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="dimensoes" class="form-label">Dimensões</label>
                        <input type="text" id="dimensoes" name="dimensoes" class="form-control"
                               value="<?= htmlspecialchars($_POST['dimensoes'] ?? '') ?>"
                               placeholder="Ex: 30cm × 21cm × 9mm">
                        <div class="form-text">Formato livre. Ex: 30cm × 21cm × 9mm</div>
                    </div>

                    <div class="col-md-3">
                        <label for="preco" class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                        <input type="text" id="preco" name="preco" class="form-control"
                               value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>"
                               placeholder="Ex: 29,90"
                               inputmode="decimal" required>
                    </div>

                    <div class="col-md-3">
                        <label for="categoria" class="form-label">Categoria</label>
                        <input type="text" id="categoria" name="categoria" class="form-control"
                               value="<?= htmlspecialchars($_POST['categoria'] ?? '') ?>"
                               placeholder="Ex: Decorativo"
                               list="lista-categorias">
                        <datalist id="lista-categorias">
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="col-12">
                        <label for="imagens" class="form-label">
                            Imagens <span class="text-danger">*</span>
                            <span class="text-muted small ms-1">
                                (JPG, PNG, WebP ou GIF — até 10 MB cada, convertidas para .webp)
                            </span>
                        </label>
                        <input type="file" id="imagens" name="imagens[]" class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,.gif" multiple required>
                        <div id="preview-imagens" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" id="ativo" name="ativo" class="form-check-input"
                                   <?= isset($_POST['ativo']) || !isset($_POST['nome']) ? 'checked' : '' ?>>
                            <label for="ativo" class="form-check-label">Produto ativo (visível no catálogo)</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save me-1"></i>Salvar Produto
                    </button>
                    <a href="../index.php?aba=produtos" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('imagens').addEventListener('change', function () {
    const container = document.getElementById('preview-imagens');
    container.innerHTML = '';

    Array.from(this.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'rounded border';
            img.style.cssText = 'width:80px;height:80px;object-fit:cover;';
            img.title = file.name;
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>
</body>
</html>
