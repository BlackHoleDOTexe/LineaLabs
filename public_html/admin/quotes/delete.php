<?php
/**
 * Excluir orçamento permanentemente
 * Sistema em duas etapas: confirmação -> execução
 */

require_once dirname(__DIR__, 3) . '/private/config.php';
require_once dirname(__DIR__, 2) . '/app/Service/Auth.php';
require_once dirname(__DIR__, 2) . '/app/Repository/QuoteRepository.php';

exigirLogin();

// ============================================================
// ETAPA 1: Página de confirmação (GET)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        header('Location: ../index.php?aba=orcamentos');
        exit;
    }

    $quoteRepo = new QuoteRepository($pdo);

    $orcamento = $quoteRepo->findById($id);

    if (!$orcamento) {
        header('Location: ../index.php?aba=orcamentos&erro=orcamento_nao_encontrado');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Excluir Orçamento — Linea Labs</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="../../css/admin_dashboard.css?v=<?= APP_version ?>">
        <style>
            .delete-card {
                border-left: 4px solid var(--bs-danger);
            }
            .btn-delete {
                padding: 0.5rem 1.5rem;
                font-weight: 500;
            }
            .stat-card {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 10px;
                padding: 1.5rem;
            }
            .detail-card {
                background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
                border-radius: 10px;
                padding: 1.5rem;
            }
        </style>
    </head>
    <body class="admin-body">
    <div class="admin-wrapper">

        <aside class="admin-sidebar">
            <h2 class="logo mb-4">Linea Labs</h2>
            <nav class="nav flex-column gap-1">
                <a class="nav-link" href="../index.php?aba=produtos">
                    <i class="bi bi-box-seam me-2"></i>Produtos
                </a>
                <a class="nav-link" href="../index.php?aba=orcamentos">
                    <i class="bi bi-calculator me-2"></i>Orçamentos
                </a>
                <a class="nav-link active" href="#">
                    <i class="bi bi-trash3 me-2"></i>Excluir
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
                    <h1 class="h3 mb-1"><i class="bi bi-trash3 me-2 text-danger"></i>Excluir Orçamento</h1>
                    <p class="text-muted mb-0">Confirme a exclusão permanente</p>
                </div>
                <a href="../index.php?aba=orcamentos" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Voltar
                </a>
            </div>

            <div class="admin-card delete-card">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção! Esta ação é irreversível.</strong> Todos os dados do orçamento serão permanentemente removidos.
                </div>

                <div class="mb-4">
                    <h5 class="mb-3"><i class="bi bi-calculator me-2"></i>Orçamento a ser excluído</h5>

                    <div class="stat-card mb-4">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <div class="text-center">
                                    <div class="display-6 text-danger mb-2">
                                        <i class="bi bi-hash"></i>
                                    </div>
                                    <div class="h4">#<?= (int) $orcamento['id'] ?></div>
                                    <small class="text-muted">ID DO ORÇAMENTO</small>
                                </div>
                            </div>
                            <div class="col-md-4 border-end">
                                <div class="text-center">
                                    <div class="display-6 text-danger mb-2">
                                        <i class="bi bi-rulers"></i>
                                    </div>
                                    <div class="h4"><?= number_format((float)$orcamento['area_cm2'], 1, ',', '.') ?> cm²</div>
                                    <small class="text-muted">ÁREA TOTAL</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="display-6 text-danger mb-2">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <div class="h4">R$ <?= number_format((float)$orcamento['preco_calculado'], 2, ',', '.') ?></div>
                                    <small class="text-muted">VALOR TOTAL</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <h6 class="text-muted mb-3">DETALHES DO ORÇAMENTO</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted d-block">PEÇA</small>
                                    <strong class="fs-5"><?= !empty($orcamento['descricao_peca']) ? htmlspecialchars($orcamento['descricao_peca']) : '—' ?></strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">CLIENTE</small>
                                    <strong><?= !empty($orcamento['nome_cliente']) ? htmlspecialchars($orcamento['nome_cliente']) : '—' ?></strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">EMAIL</small>
                                    <strong><?= !empty($orcamento['email_cliente']) ? htmlspecialchars($orcamento['email_cliente']) : '—' ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted d-block">DIMENSÕES</small>
                                    <strong><?= (float)$orcamento['largura_cm'] ?>cm × <?= (float)$orcamento['altura_cm'] ?>cm</strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">CRIADO EM</small>
                                    <strong><?= date('d/m/Y H:i', strtotime($orcamento['criado_em'])) ?></strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">STATUS</small>
                                    <span class="badge bg-<?= (int) $orcamento['status'] === 1 ? 'success' : 'secondary' ?>">
                                        <?= (int) $orcamento['status'] === 1 ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    Após a exclusão, não será possível recuperar este orçamento.
                </div>

                <div class="d-flex gap-2">
                    <form method="POST" action="delete.php">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id" value="<?= (int) $id ?>">
                        <button type="submit" class="btn btn-danger btn-delete">
                            <i class="bi bi-trash3 me-1"></i>Sim, excluir permanentemente
                        </button>
                    </form>

                    <a href="../index.php?aba=orcamentos" class="btn btn-outline-secondary btn-delete">
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
        header('Location: ../index.php?aba=orcamentos&erro=delete_invalido');
        exit;
    }

    try {
        $quoteRepo = new QuoteRepository($pdo);

        $orcamento = $quoteRepo->findById($id);

        if (!$orcamento) {
            header('Location: ../index.php?aba=orcamentos&erro=orcamento_nao_encontrado');
            exit;
        }

        $quoteRepo->delete($id);

        error_log("[quotes/delete] Orçamento {$id} excluído");

        header('Location: ../index.php?aba=orcamentos&msg=orc_excluido');
        exit;

    } catch (Throwable $e) {
        error_log('[quotes/delete] Erro: ' . $e->getMessage());
        header('Location: ../index.php?aba=orcamentos&erro=delete_falhou');
        exit;
    }
}

// Se não for GET nem POST, redirecionar
header('Location: ../index.php?aba=orcamentos');
exit;
