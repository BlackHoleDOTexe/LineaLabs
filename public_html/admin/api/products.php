<?php
/**
 * API endpoint: listagem paginada de produtos com filtros.
 * Retorna HTML parcial para ser injetado via AJAX no dashboard.
 */

require_once dirname(__DIR__, 3) . '/private/config.php';
require_once dirname(__DIR__, 2) . '/app/Service/Auth.php';

// Em chamadas AJAX, não redirecionar — retorna HTML de erro legível
$timeout   = defined('ADMIN_SESSION_TIMEOUT') ? ADMIN_SESSION_TIMEOUT : 1800;
$loggedIn  = adminEstaLogado();
$timedOut  = $loggedIn && isset($_SESSION['ultimo_acesso'])
             && (time() - $_SESSION['ultimo_acesso']) > $timeout;

if (!$loggedIn || $timedOut) {
    if ($timedOut) {
        session_destroy();
    }
    http_response_code(401);
    echo '<div class="alert alert-warning mb-0">'
       . '<i class="bi bi-lock me-2"></i>Sessão expirada. '
       . '<a href="/admin/login.php">Fazer login novamente</a></div>';
    exit;
}

$_SESSION['ultimo_acesso'] = time();

header('Content-Type: text/html; charset=utf-8');

$busca     = mb_substr(trim($_GET['busca']     ?? ''), 0, 150);
$categoria = trim($_GET['categoria'] ?? '');
$status    = $_GET['status']         ?? '';
$precoMin  = $_GET['preco_min']      ?? '';
$precoMax  = $_GET['preco_max']      ?? '';
$pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 20;

// --- Montagem dinâmica do WHERE ---
$where  = ['1=1'];
$params = [];

if ($busca !== '') {
    $palavras = preg_split('/\s+/', $busca, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($palavras as $idx => $palavra) {
        $palavraSafe = '%' . addcslashes($palavra, '%_\\') . '%';
        $pNome = ":bN{$idx}";
        $pDesc = ":bD{$idx}";
        $where[]        = "(nome LIKE {$pNome} OR COALESCE(descricao,'') LIKE {$pDesc})";
        $params[$pNome] = $palavraSafe;
        $params[$pDesc] = $palavraSafe;
    }
}

if ($categoria !== '') {
    $where[]              = 'categoria = :categoria';
    $params[':categoria'] = $categoria;
}

if ($status !== '' && in_array($status, ['1', '0'], true)) {
    $where[]         = 'ativo = :status';
    $params[':status'] = (int) $status;
}

if (is_numeric($precoMin) && $precoMin !== '') {
    $where[]              = 'preco >= :preco_min';
    $params[':preco_min'] = (float) $precoMin;
}

if (is_numeric($precoMax) && $precoMax !== '') {
    $where[]              = 'preco <= :preco_max';
    $params[':preco_max'] = (float) $precoMax;
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// --- Contagem total ---
$sqlCount  = "SELECT COUNT(*) FROM produtos {$whereSQL}";
$stmtCount = $pdo->prepare($sqlCount);
foreach ($params as $k => $v) {
    $stmtCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtCount->execute();
$total        = (int) $stmtCount->fetchColumn();
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina       = min($pagina, $totalPaginas);
$offset       = ($pagina - 1) * $porPagina;

// --- Busca dos produtos ---
$sqlProdutos = "SELECT id, nome, preco, categoria, ativo, dimensoes
                FROM produtos
                {$whereSQL}
                ORDER BY id DESC
                LIMIT :limite OFFSET :offset";

$stmtProdutos = $pdo->prepare($sqlProdutos);
foreach ($params as $k => $v) {
    $stmtProdutos->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtProdutos->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmtProdutos->bindValue(':offset', $offset,    PDO::PARAM_INT);
$stmtProdutos->execute();
$produtos = $stmtProdutos->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <small class="text-muted">
        <?= $total ?> produto(s) encontrado(s)
    </small>
    <?php if ($total > 0): ?>
        <small class="text-muted">Página <?= $pagina ?> de <?= $totalPaginas ?></small>
    <?php endif; ?>
</div>

<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:50px">#</th>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Dimensões</th>
                <th>Preço</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($produtos)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Nenhum produto encontrado com os filtros aplicados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td class="text-muted small"><?= (int) $p['id'] ?></td>
                        <td class="fw-medium"><?= htmlspecialchars($p['nome']) ?></td>
                        <td>
                            <?php if (!empty($p['categoria'])): ?>
                                <span class="badge text-bg-light border text-dark">
                                    <?= htmlspecialchars($p['categoria']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= !empty($p['dimensoes']) ? htmlspecialchars($p['dimensoes']) : '—' ?>
                        </td>
                        <td>R$&nbsp;<?= number_format((float) $p['preco'], 2, ',', '.') ?></td>
                        <td>
                            <?php if ((int) $p['ativo'] === 1): ?>
                                <span class="badge text-bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="products/edit.php?id=<?= (int) $p['id'] ?>"
                                   class="btn btn-outline-dark btn-sm d-flex align-items-center gap-1">
                                    <i class="bi bi-pencil"></i>Editar
                                </a>

                                <a href="products/status.php?id=<?= (int) $p['id'] ?>"
                                   class="btn btn-outline-<?= (int) $p['ativo'] === 1 ? 'warning' : 'success' ?> btn-sm d-flex align-items-center gap-1">
                                    <i class="bi <?= (int) $p['ativo'] === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                    <?= (int) $p['ativo'] === 1 ? 'Desativar' : 'Ativar' ?>
                                </a>

                                <a href="products/delete.php?id=<?= (int) $p['id'] ?>"
                                   class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                                    <i class="bi bi-trash3"></i>Excluir
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
    <nav class="mt-3" aria-label="Paginação de produtos">
        <ul class="pagination pagination-sm justify-content-center mb-0">

            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                <button class="page-link" data-pagina="<?= $pagina - 1 ?>" <?= $pagina <= 1 ? 'tabindex="-1"' : '' ?>>
                    ‹ Anterior
                </button>
            </li>

            <?php
            $inicio = max(1, $pagina - 2);
            $fim    = min($totalPaginas, $pagina + 2);
            if ($inicio > 1): ?>
                <li class="page-item">
                    <button class="page-link" data-pagina="1">1</button>
                </li>
                <?php if ($inicio > 2): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                    <button class="page-link" data-pagina="<?= $i ?>"><?= $i ?></button>
                </li>
            <?php endfor; ?>

            <?php if ($fim < $totalPaginas): ?>
                <?php if ($fim < $totalPaginas - 1): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <button class="page-link" data-pagina="<?= $totalPaginas ?>"><?= $totalPaginas ?></button>
                </li>
            <?php endif; ?>

            <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                <button class="page-link" data-pagina="<?= $pagina + 1 ?>" <?= $pagina >= $totalPaginas ? 'tabindex="-1"' : '' ?>>
                    Próximo ›
                </button>
            </li>

        </ul>
    </nav>
<?php endif; ?>
