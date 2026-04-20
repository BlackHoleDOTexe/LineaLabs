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
        <div class="user-info mb-4 p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05);">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #b8922e 0%, #d4b24a 100%);">
                    <i class="bi bi-person-fill text-white"></i>
                </div>
                <div>
                    <div class="small text-white-50">Administrador</div>
                    <div class="fw-medium"><?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        </div>
        <nav class="nav flex-column gap-1">
            <a class="nav-link" href="../index.php?aba=produtos">
                <i class="bi bi-box-seam"></i>Produtos
            </a>
            <a class="nav-link active" href="#">
                <i class="bi bi-plus-circle"></i>Novo Produto
            </a>
            <a class="nav-link" href="../index.php?aba=orcamentos">
                <i class="bi bi-calculator"></i>Orçamentos
            </a>
            <a class="nav-link" href="../index.php?aba=configuracoes">
                <i class="bi bi-gear"></i>Configurações
            </a>
        </nav>
        <div class="mt-auto pt-4">
            <a class="nav-link text-danger" href="../logout.php">
                <i class="bi bi-box-arrow-right"></i>Sair
            </a>
        </div>
    </aside>

    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1"><i class="bi bi-plus-circle me-2 text-primary"></i>Novo Produto</h1>
                <p class="text-muted mb-0">Preencha os dados e envie as imagens para o catálogo</p>
            </div>
            <a href="../index.php?aba=produtos" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i>Voltar aos Produtos
            </a>
        </div>

        <?php if ($erro !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="row g-4">
                    <div class="col-12">
                        <label for="nome" class="form-label fw-medium">Nome do Produto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-tag text-muted"></i>
                            </span>
                            <input type="text" id="nome" name="nome" class="form-control border-start-0"
                                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                   placeholder="Ex: Cruz Decorativa em MDF" required>
                        </div>
                        <div class="form-text small">Nome que aparecerá no catálogo</div>
                    </div>

                    <div class="col-12">
                        <label for="descricao" class="form-label fw-medium">Descrição Detalhada</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent align-items-start pt-3 border-end-0">
                                <i class="bi bi-text-paragraph text-muted"></i>
                            </span>
                            <textarea id="descricao" name="descricao" class="form-control border-start-0" rows="5"
                                      placeholder="Descreva o produto em detalhes, materiais, acabamentos, usos..."><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                        </div>
                        <div class="form-text small">Esta descrição aparecerá na página de detalhes do produto</div>
                    </div>

                    <div class="col-md-6">
                        <label for="dimensoes" class="form-label fw-medium">Dimensões</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-rulers text-muted"></i>
                            </span>
                            <input type="text" id="dimensoes" name="dimensoes" class="form-control border-start-0"
                                   value="<?= htmlspecialchars($_POST['dimensoes'] ?? '') ?>"
                                   placeholder="Ex: 30cm × 21cm × 9mm">
                        </div>
                        <div class="form-text small">Formato livre. Ex: 30cm × 21cm × 9mm</div>
                    </div>

                    <div class="col-md-3">
                        <label for="preco" class="form-label fw-medium">Preço (R$) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-currency-dollar text-muted"></i>
                            </span>
                            <input type="text" id="preco" name="preco" class="form-control border-start-0"
                                   value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>"
                                   placeholder="Ex: 29,90"
                                   inputmode="decimal" required>
                        </div>
                        <div class="form-text small">Use ponto ou vírgula para decimais</div>
                    </div>

                    <div class="col-md-3">
                        <label for="categoria" class="form-label fw-medium">Categoria</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-grid text-muted"></i>
                            </span>
                            <input type="text" id="categoria" name="categoria" class="form-control border-start-0"
                                   value="<?= htmlspecialchars($_POST['categoria'] ?? '') ?>"
                                   placeholder="Ex: Decorativo"
                                   list="lista-categorias">
                        </div>
                        <datalist id="lista-categorias">
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <div class="form-text small">Categoria para organização no catálogo</div>
                    </div>

                    <div class="col-12">
                        <label for="imagens" class="form-label fw-medium">
                            Imagens do Produto <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-images text-muted"></i>
                            </span>
                            <input type="file" id="imagens" name="imagens[]" class="form-control border-start-0"
                                   accept=".jpg,.jpeg,.png,.webp,.gif" multiple required>
                        </div>
                        <div class="form-text small">
                            <i class="bi bi-info-circle me-1"></i>Formatos: JPG, PNG, WebP ou GIF — até 10 MB cada, serão convertidas automaticamente para .webp
                        </div>
                        <div id="preview-imagens" class="d-flex flex-wrap gap-3 mt-3"></div>
                    </div>

                    <div class="col-12">
                        <div class="form-check p-3 rounded-3" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                            <input type="checkbox" id="ativo" name="ativo" class="form-check-input"
                                   <?= isset($_POST['ativo']) || !isset($_POST['nome']) ? 'checked' : '' ?>>
                            <label for="ativo" class="form-check-label fw-medium">
                                <i class="bi bi-eye me-1"></i>Produto ativo (visível no catálogo)
                            </label>
                            <div class="form-text small mt-1">Desmarque para ocultar o produto temporariamente</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-dark px-4">
                        <i class="bi bi-save me-2"></i>Salvar Produto
                    </button>
                    <a href="../index.php?aba=produtos" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
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

    if (this.files.length === 0) {
        container.innerHTML = '<div class="text-muted small p-3"><i class="bi bi-info-circle me-1"></i>Nenhuma imagem selecionada</div>';
        return;
    }

    Array.from(this.files).forEach((file, index) => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = e => {
            const card = document.createElement('div');
            card.className = 'preview-card';
            card.style.cssText = 'width: 120px; position: relative;';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'rounded-3 border';
            img.style.cssText = 'width:100%;height:120px;object-fit:cover;';
            img.title = file.name;

            const badge = document.createElement('div');
            badge.className = 'badge bg-dark position-absolute top-0 end-0 m-1';
            badge.style.cssText = 'font-size: 0.7rem;';
            badge.textContent = `#${index + 1}`;

            const name = document.createElement('div');
            name.className = 'small text-truncate mt-1 text-center';
            name.style.cssText = 'max-width: 120px;';
            name.title = file.name;
            name.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;

            card.appendChild(img);
            card.appendChild(badge);
            card.appendChild(name);
            container.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
});

// Validação do formulário
document.querySelector('form.needs-validation').addEventListener('submit', function (event) {
    if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }
    this.classList.add('was-validated');
}, false);
</script>

<style>
.preview-card {
    transition: transform 0.2s ease;
}
.preview-card:hover {
    transform: translateY(-5px);
}
</style>
</body>
</html>
