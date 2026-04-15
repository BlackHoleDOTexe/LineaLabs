<?php
/**
 * Excluir produto permanentemente
 * Sistema em duas etapas: confirmação -> execução
 */

require_once dirname(__DIR__, 3) . '/private/config.php';
require_once dirname(__DIR__, 2) . '/app/Service/Auth.php';
require_once dirname(__DIR__, 2) . '/app/Repository/ProductRepository.php';
require_once dirname(__DIR__, 2) . '/app/Repository/ImageRepository.php';

exigirLogin();

// ============================================================
// ETAPA 1: Página de confirmação (GET)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        header('Location: ../index.php?aba=produtos');
        exit;
    }

    $productRepo = new ProductRepository($pdo);
    $imageRepo = new ImageRepository($pdo);

    $produto = $productRepo->findById($id);
    $imagens = $imageRepo->findByProduct($id);

    if (!$produto) {
        header('Location: ../index.php?aba=produtos');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Excluir Produto — Linea Labs</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="../../css/admin_dashboard.css?v=<?= APP_version ?>">
        <link rel="stylesheet" href="delete.css?v=<?= APP_version ?>">
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
                    <i class="bi bi-trash3 me-2"></i>Excluir
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
                    <h1 class="h3 mb-1"><i class="bi bi-trash3 me-2 text-danger"></i>Excluir Produto</h1>
                    <p class="text-muted mb-0">Confirme a exclusão permanente</p>
                </div>
                <a href="../index.php?aba=produtos" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Voltar
                </a>
            </div>

            <div class="admin-card delete-card">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção! Esta ação é irreversível.</strong> Todos os dados do produto serão permanentemente removidos.
                </div>

                <div class="mb-4">
                    <h5 class="mb-3"><i class="bi bi-box-seam me-2"></i>Produto a ser excluído</h5>

                    <div class="stat-card mb-4">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <div class="text-center">
                                    <div class="display-6 text-danger mb-2">
                                        <i class="bi bi-box"></i>
                                    </div>
                                    <div class="h4">#<?= (int) $produto['id'] ?></div>
                                    <small class="text-muted">ID DO PRODUTO</small>
                                </div>
                            </div>
                            <div class="col-md-4 border-end">
                                <div class="text-center">
                                    <div class="display-6 text-danger mb-2">
                                        <i class="bi bi-images"></i>
                                    </div>
                                    <div class="h4"><?= count($imagens) ?></div>
                                    <small class="text-muted">IMAGENS</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="display-6 text-danger mb-2">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <div class="h4">R$ <?= number_format((float) $produto['preco'], 2, ',', '.') ?></div>
                                    <small class="text-muted">VALOR</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">DETALHES</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">NOME</small>
                                        <strong class="fs-5"><?= htmlspecialchars($produto['nome']) ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">CATEGORIA</small>
                                        <strong><?= !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : '—' ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">STATUS</small>
                                        <span class="badge bg-<?= (int) $produto['ativo'] === 1 ? 'success' : 'secondary' ?>">
                                            <?= (int) $produto['ativo'] === 1 ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">DIMENSÕES</small>
                                        <strong><?= !empty($produto['dimensoes']) ? htmlspecialchars($produto['dimensoes']) : '—' ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($imagens)): ?>
                <div class="mb-4">
                    <h6 class="mb-3"><i class="bi bi-images me-2"></i>Imagens que serão excluídas (<?= count($imagens) ?>)</h6>
                    <div class="row g-2">
                        <?php foreach ($imagens as $imagem): ?>
                            <div class="col-4 col-md-3 col-lg-2">
                                <div class="position-relative">
                                    <img src="/media/image.php?file=<?= urlencode($imagem['arquivo']) ?>"
                                         class="image-preview w-100 border"
                                         alt="Imagem do produto">
                                    <div class="position-absolute top-0 end-0 m-1">
                                        <span class="badge bg-danger rounded-circle p-1">
                                            <i class="bi bi-x delete-badge-icon"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    Após a exclusão, não será possível recuperar este produto ou suas imagens.
                </div>

                <div class="d-flex gap-2">
                    <form method="POST" action="delete.php">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id" value="<?= (int) $id ?>">
                        <button type="submit" class="btn btn-danger btn-delete">
                            <i class="bi bi-trash3 me-1"></i>Sim, excluir permanentemente
                        </button>
                    </form>

                    <a href="../index.php?aba=produtos" class="btn btn-outline-secondary btn-delete">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// ETAPA 2: Execução (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        header('Location: ../index.php?aba=produtos&erro=delete_invalido');
        exit;
    }

    try {
        $productRepo = new ProductRepository($pdo);
        $imageRepo = new ImageRepository($pdo);

        // Verificar se produto existe
        $produto = $productRepo->findById($id);
        if (!$produto) {
            header('Location: ../index.php?aba=produtos&erro=produto_nao_encontrado');
            exit;
        }

        $pdo->beginTransaction();

        // Deletar arquivos físicos
        $uploadDir = dirname(__DIR__, 3) . '/private/uploads/products/';
        $arquivos = $imageRepo->getFilesByProduct($id);

        if (is_dir($uploadDir)) {
            foreach ($arquivos as $arquivo) {
                $caminho = $uploadDir . basename($arquivo);
                if (is_file($caminho)) {
                    unlink($caminho);
                }
            }
        }

        // Deletar do banco
        $imageRepo->deleteByProduct($id);
        $productRepo->delete($id);

        $pdo->commit();

        // Log da ação
        error_log("[delete] Produto {$id} excluído com " . count($arquivos) . " imagens");

        header('Location: ../index.php?aba=produtos&msg=produto_excluido');
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[delete] Erro: ' . $e->getMessage());
        header('Location: ../index.php?aba=produtos&erro=delete_falhou');
        exit;
    }
}

// Se não for GET nem POST, redirecionar
header('Location: ../index.php?aba=produtos');
exit;