<?php
/**
 * API endpoint: listagem paginada de produtos com filtros.
 * Retorna HTML parcial para ser injetado via AJAX no dashboard.
 */

require_once dirname(__DIR__, 3) . '/private/config.php';
require_once dirname(__DIR__) . '/auth.php';

exigirLogin();

// --- Parâmetros de filtro ---
$busca     = trim($_GET['busca']     ?? '');
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
        $pNome = ":bN{$idx}";
        $pDesc = ":bD{$idx}";
        $where[]       = "(nome LIKE {$pNome} OR COALESCE(descricao,'') LIKE {$pDesc})";
        $params[$pNome] = '%' . $palavra . '%';
        $params[$pDesc] = '%' . $palavra . '%';
    }
}

if ($categoria !== '') {
    $where[]           = 'categoria = :categoria';
    $params[':categoria'] = $categoria;
}

if ($status !== '' && in_array($status, ['1', '0'], true)) {
    $where[]         = 'ativo = :status';
    $params[':status'] = (int) $status;
}

if (is_numeric($precoMin) && $precoMin !== '') {
    $where[]             = 'preco >= :preco_min';
    $params[':preco_min'] = (float) $precoMin;
}

if (is_numeric($precoMax) && $precoMax !== '') {
    $where[]             = 'preco <= :preco_max';
    $params[':preco_max'] = (float) $precoMax;
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// --- Contagem total ---
$sqlCount   = "SELECT COUNT(*) FROM produtos {$whereSQL}";
$stmtCount  = $pdo->prepare($sqlCount);
foreach ($params as $k => $v) {
    $stmtCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtCount->execute();
$total        = (int) $stmtCount->fetchColumn();
$totalPaginas = (int) ceil($total / $porPagina);
$totalPaginas = max(1, $totalPaginas);
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

// --- Monta query string para paginação (preserva filtros ativos) ---
$queryBase = array_filter([
    'busca'     => $busca,
    'categoria' => $categoria,
    'status'    => $status,
    'preco_min' => $precoMin,
    'preco_max' => $precoMax,
], fn($v) => $v !== '');
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
                            <a href="product-edit.php?id=<?= (int) $p['id'] ?>"
                               class="btn btn-outline-dark btn-sm">Editar</a>

                            <a href="product-toggle.php?id=<?= (int) $p['id'] ?>"
                               class="btn btn-outline-warning btn-sm"
                               onclick="return confirm('Alterar status deste produto?')">
                                <?= (int) $p['ativo'] === 1 ? 'Desativar' : 'Ativar' ?>
                            </a>

                            <a href="product-delete.php?id=<?= (int) $p['id'] ?>"
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('Excluir permanentemente este produto e suas imagens?')">
                                Excluir
                            </a>
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
