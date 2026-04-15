<?php
/**
 * Alterar status do produto (ativo/inativo)
 * Sistema em duas etapas: confirmação -> execução
 */

require_once dirname(__DIR__, 3) . '/private/config.php';
require_once dirname(__DIR__, 2) . '/app/Service/Auth.php';
require_once dirname(__DIR__, 2) . '/app/Repository/ProductRepository.php';

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
    $produto = $productRepo->findById($id);

    if (!$produto) {
        header('Location: ../index.php?aba=produtos');
        exit;
    }

    $acao = (int) $produto['ativo'] === 1 ? 'desativar' : 'ativar';
    $icone = (int) $produto['ativo'] === 1 ? 'bi-pause-circle' : 'bi-play-circle';
    $cor = (int) $produto['ativo'] === 1 ? 'warning' : 'success';
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alterar Status — Linea Labs</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="../../css/admin_dashboard.css?v=<?= APP_version ?>">
        <link rel="stylesheet" href="status.css?v=<?= APP_version ?>">
        <style>:root { --status-color: var(--bs-<?= $cor ?>); }</style>
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
                    <i class="bi <?= $icone ?> me-2"></i>Status
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
                    <h1 class="h3 mb-1"><i class="bi <?= $icone ?> me-2 text-<?= $cor ?>"></i>Alterar Status</h1>
                    <p class="text-muted mb-0">Confirme a alteração do status do produto</p>
                </div>
                <a href="../index.php?aba=produtos" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Voltar
                </a>
            </div>

            <div class="admin-card status-card">
                <div class="alert alert-<?= $cor ?>">
                    <i class="bi <?= $icone ?> me-2"></i>
                    <strong>Alterar Status do Produto</strong>
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">Detalhes do Produto:</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">INFORMAÇÕES</h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                                            <i class="bi bi-tag text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">ID</small>
                                            <strong>#<?= (int) $produto['id'] ?></strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                                            <i class="bi bi-box text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">NOME</small>
                                            <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                                            <i class="bi bi-currency-dollar text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">PREÇO</small>
                                            <strong>R$ <?= number_format((float) $produto['preco'], 2, ',', '.') ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">STATUS</h6>
                                    <div class="text-center py-4">
                                        <div class="display-1 mb-3">
                                            <?php if ((int) $produto['ativo'] === 1): ?>
                                                <i class="bi bi-toggle-on text-success"></i>
                                            <?php else: ?>
                                                <i class="bi bi-toggle-off text-secondary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-<?= (int) $produto['ativo'] === 1 ? 'success' : 'secondary' ?> px-3 py-2 fs-6">
                                                <?= (int) $produto['ativo'] === 1 ? 'ATIVO' : 'INATIVO' ?>
                                            </span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-arrow-right"></i>
                                            <span class="mx-2">será alterado para</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-<?= (int) $produto['ativo'] === 1 ? 'secondary' : 'success' ?> px-3 py-2 fs-6">
                                                <?= (int) $produto['ativo'] === 1 ? 'INATIVO' : 'ATIVO' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <form method="POST" action="status.php">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id" value="<?= (int) $id ?>">
                        <button type="submit" class="btn btn-<?= $cor ?> btn-status">
                            <i class="bi <?= $icone ?> me-1"></i>Confirmar Alteração
                        </button>
                    </form>

                    <a href="../index.php?aba=produtos" class="btn btn-outline-secondary btn-status">
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
        header('Location: ../index.php?aba=produtos&erro=status_invalido');
        exit;
    }

    try {
        $productRepo = new ProductRepository($pdo);
        $produto = $productRepo->findById($id);

        if (!$produto) {
            header('Location: ../index.php?aba=produtos&erro=produto_nao_encontrado');
            exit;
        }

        $productRepo->toggleStatus($id);

        // Log da ação
        $novoStatus = (int) $produto['ativo'] === 1 ? 'inativo' : 'ativo';
        error_log("[status] Produto {$id} alterado para {$novoStatus}");

        header('Location: ../index.php?aba=produtos&msg=status_alterado');
        exit;

    } catch (Throwable $e) {
        error_log('[status] Erro: ' . $e->getMessage());
        header('Location: ../index.php?aba=produtos&erro=status_falhou');
        exit;
    }
}

// Se não for GET nem POST, redirecionar
header('Location: ../index.php?aba=produtos');
exit;