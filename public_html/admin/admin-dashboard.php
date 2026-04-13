<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once __DIR__ . '/auth.php';

exigirLogin();

// ============================================================
// Ação: Salvar configurações globais
// ============================================================
$mensagemConfig = '';
$erroConfig     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_configuracoes'])) {
    $chavesPermitidas = [
        'razao_social', 'nome_fantasia', 'inscricao_estadual',
        'custo_material_cm2', 'custo_minuto_maquina', 'markup_padrao',
    ];

    $erroConfigLocal = '';

    foreach ($chavesPermitidas as $chave) {
        $valor = trim($_POST[$chave] ?? '');

        if (in_array($chave, ['custo_material_cm2', 'custo_minuto_maquina', 'markup_padrao'], true)) {
            $valor = str_replace(',', '.', $valor);
            if (!is_numeric($valor) || (float) $valor < 0) {
                $erroConfigLocal = "Valor numérico inválido para " . $chave;
                break;
            }
        }

        $sql  = "INSERT INTO configuracoes (chave, valor)
                 VALUES (:chave, :valor)
                 ON DUPLICATE KEY UPDATE valor = :valor2";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':chave' => $chave, ':valor' => $valor, ':valor2' => $valor]);
    }

    if ($erroConfigLocal !== '') {
        $erroConfig = $erroConfigLocal;
    } else {
        $mensagemConfig = 'Configurações salvas com sucesso!';
    }
}

// ============================================================
// Ação: Salvar orçamento
// ============================================================
$mensagemOrc = '';
$erroOrc     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_orcamento'])) {
    $nomeCliente    = trim($_POST['nome_cliente']    ?? '');
    $descricaoPeca  = trim($_POST['descricao_peca']  ?? '');
    $largura        = str_replace(',', '.', $_POST['largura_cm']           ?? '0');
    $altura         = str_replace(',', '.', $_POST['altura_cm']            ?? '0');
    $tempo          = str_replace(',', '.', $_POST['tempo_maquina_min']    ?? '0');
    $custoMat       = str_replace(',', '.', $_POST['custo_material_cm2']   ?? '0');
    $custoMaq       = str_replace(',', '.', $_POST['custo_minuto_maquina'] ?? '0');
    $markup         = str_replace(',', '.', $_POST['markup']               ?? '1');
    $precoCalculado = str_replace(',', '.', $_POST['preco_calculado']      ?? '0');

    $camposNum = ['largura' => $largura, 'altura' => $altura, 'tempo' => $tempo,
                  'custoMat' => $custoMat, 'custoMaq' => $custoMaq, 'markup' => $markup];
    $erroOrcLocal = '';
    foreach ($camposNum as $campo => $val) {
        if (!is_numeric($val) || (float) $val < 0) {
            $erroOrcLocal = "Valor inválido no campo \"{$campo}\". Use apenas números.";
            break;
        }
    }

    if ($erroOrcLocal !== '') {
        $erroOrc = $erroOrcLocal;
    } else {
        $area = round((float) $largura * (float) $altura, 2);

        $sql  = "INSERT INTO orcamentos
                    (nome_cliente, descricao_peca, largura_cm, altura_cm, area_cm2,
                     tempo_maquina_min, custo_material_cm2, custo_minuto_maquina,
                     markup, preco_calculado)
                 VALUES
                    (:nome_cliente, :descricao_peca, :largura, :altura, :area,
                     :tempo, :custo_mat, :custo_maq, :markup, :preco)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome_cliente'  => $nomeCliente  ?: null,
            ':descricao_peca'=> $descricaoPeca ?: null,
            ':largura'       => (float) $largura,
            ':altura'        => (float) $altura,
            ':area'          => $area,
            ':tempo'         => (float) $tempo,
            ':custo_mat'     => (float) $custoMat,
            ':custo_maq'     => (float) $custoMaq,
            ':markup'        => (float) $markup,
            ':preco'         => (float) $precoCalculado,
        ]);

        $mensagemOrc = 'Orçamento salvo com sucesso!';
    }
}

// ============================================================
// Carregar configurações para exibição nos formulários
// ============================================================
$sqlConfig = "SELECT chave, valor FROM configuracoes";
$stmtCfg   = $pdo->query($sqlConfig);
$config    = [];
foreach ($stmtCfg->fetchAll() as $row) {
    $config[$row['chave']] = $row['valor'];
}

// Valores padrão caso a tabela ainda não tenha sido populada
$config += [
    'razao_social'         => '66.043.362 EDUARDO FELIPE SCHMIDT DE GODOY',
    'nome_fantasia'        => 'Linea Labs',
    'inscricao_estadual'   => '91221792-20',
    'custo_material_cm2'   => '0.0500',
    'custo_minuto_maquina' => '0.5000',
    'markup_padrao'        => '3.00',
];

// ============================================================
// Carregar categorias disponíveis para o filtro
// ============================================================
$sqlCats   = "SELECT DISTINCT categoria FROM produtos
              WHERE categoria IS NOT NULL AND categoria <> ''
              ORDER BY categoria";
$categorias = $pdo->query($sqlCats)->fetchAll(PDO::FETCH_COLUMN);

// ============================================================
// Carregar últimos 10 orçamentos
// ============================================================
$sqlOrcs  = "SELECT * FROM orcamentos ORDER BY criado_em DESC LIMIT 10";
$orcamentos = $pdo->query($sqlOrcs)->fetchAll();

// ============================================================
// Aba ativa (persiste via query string para feedback pós-POST)
// ============================================================
$abaAtiva = $_GET['aba'] ?? 'produtos';
if (!in_array($abaAtiva, ['produtos', 'orcamentos', 'configuracoes'], true)) {
    $abaAtiva = 'produtos';
}
// Após POST redireciona de volta com aba correta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['salvar_configuracoes']) && $erroConfig === '') {
        header('Location: admin-dashboard.php?aba=configuracoes&msg=config_ok');
        exit;
    }
    if (isset($_POST['salvar_orcamento']) && $erroOrc === '') {
        header('Location: admin-dashboard.php?aba=orcamentos&msg=orc_ok');
        exit;
    }
}

// Mensagens de sucesso via redirect
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'config_ok') $mensagemConfig = 'Configurações salvas com sucesso!';
    if ($_GET['msg'] === 'orc_ok')    $mensagemOrc    = 'Orçamento salvo com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin — Linea Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">

    <!-- ======= SIDEBAR ======= -->
    <aside class="admin-sidebar">
        <h2 class="logo mb-4">Linea Labs</h2>

        <nav class="nav flex-column gap-1">
            <a class="nav-link <?= $abaAtiva === 'produtos'       ? 'active' : '' ?>"
               href="?aba=produtos">
                <i class="bi bi-box-seam me-2"></i>Produtos
            </a>
            <a class="nav-link <?= $abaAtiva === 'orcamentos'     ? 'active' : '' ?>"
               href="?aba=orcamentos">
                <i class="bi bi-calculator me-2"></i>Orçamentos
            </a>
            <a class="nav-link <?= $abaAtiva === 'configuracoes'  ? 'active' : '' ?>"
               href="?aba=configuracoes">
                <i class="bi bi-gear me-2"></i>Configurações
            </a>
        </nav>

        <div class="mt-auto pt-4">
            <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Sair
            </a>
        </div>
    </aside>

    <!-- ======= CONTEÚDO PRINCIPAL ======= -->
    <main class="admin-content">

        <!-- ================================================== -->
        <!-- ABA: PRODUTOS                                       -->
        <!-- ================================================== -->
        <?php if ($abaAtiva === 'produtos'): ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Produtos</h1>
                <p class="text-muted mb-0">Gerencie o catálogo de produtos</p>
            </div>
            <a href="product-create.php" class="btn btn-dark">
                <i class="bi bi-plus-lg me-1"></i>Novo Produto
            </a>
        </div>

        <!-- Filtros -->
        <div class="admin-card mb-3">
            <form id="form-filtros" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">Nome / Descrição</label>
                    <input type="text" name="busca" class="form-control form-control-sm"
                           placeholder="Buscar por nome..." id="filtro-busca">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Categoria</label>
                    <select name="categoria" class="form-select form-select-sm" id="filtro-categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>">
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm" id="filtro-status">
                        <option value="">Todos</option>
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Preço mín. (R$)</label>
                    <input type="number" name="preco_min" class="form-control form-control-sm"
                           placeholder="0,00" step="0.01" min="0" id="filtro-preco-min">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Preço máx. (R$)</label>
                    <input type="number" name="preco_max" class="form-control form-control-sm"
                           placeholder="9999,99" step="0.01" min="0" id="filtro-preco-max">
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <button type="button" id="btn-limpar" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Limpar filtros
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela de produtos (carregada via AJAX) -->
        <div class="admin-card">
            <div id="produtos-container">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Carregando produtos...
                </div>
            </div>
        </div>

        <!-- ================================================== -->
        <!-- ABA: ORÇAMENTOS                                     -->
        <!-- ================================================== -->
        <?php elseif ($abaAtiva === 'orcamentos'): ?>

        <div class="mb-4">
            <h1 class="h3 mb-1">Calculadora de Orçamento</h1>
            <p class="text-muted mb-0">
                Fórmula: <code>(Custo Material + Custo Máquina) × Markup</code>
            </p>
        </div>

        <?php if ($mensagemOrc !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($mensagemOrc) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($erroOrc !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erroOrc) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Formulário da calculadora -->
            <div class="col-lg-7">
                <div class="admin-card">
                    <h2 class="h5 mb-4">Nova Simulação</h2>

                    <form method="POST" id="form-orcamento">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Cliente (opcional)</label>
                                <input type="text" name="nome_cliente" class="form-control"
                                       placeholder="Nome do cliente">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descrição da Peça</label>
                                <input type="text" name="descricao_peca" class="form-control"
                                       placeholder="Ex: Cruz MDF 3mm">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Largura (cm)</label>
                                <input type="number" name="largura_cm" id="orc-largura"
                                       class="form-control orc-calc" step="0.1" min="0"
                                       placeholder="0.0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Altura (cm)</label>
                                <input type="number" name="altura_cm" id="orc-altura"
                                       class="form-control orc-calc" step="0.1" min="0"
                                       placeholder="0.0" required>
                            </div>

                            <div class="col-12">
                                <div class="bg-light rounded p-2 text-center small">
                                    Área calculada: <strong id="orc-area">0,00</strong> cm²
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tempo de máquina (min)</label>
                                <input type="number" name="tempo_maquina_min" id="orc-tempo"
                                       class="form-control orc-calc" step="0.1" min="0"
                                       placeholder="0.0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Markup</label>
                                <input type="number" name="markup" id="orc-markup"
                                       class="form-control orc-calc" step="0.1" min="0.1"
                                       value="<?= htmlspecialchars($config['markup_padrao']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Custo MDF/cm²
                                    <span class="badge text-bg-light border text-muted ms-1">R$</span>
                                </label>
                                <input type="number" name="custo_material_cm2" id="orc-custo-mat"
                                       class="form-control orc-calc" step="0.0001" min="0"
                                       value="<?= htmlspecialchars($config['custo_material_cm2']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Custo máquina/min
                                    <span class="badge text-bg-light border text-muted ms-1">R$</span>
                                </label>
                                <input type="number" name="custo_minuto_maquina" id="orc-custo-maq"
                                       class="form-control orc-calc" step="0.0001" min="0"
                                       value="<?= htmlspecialchars($config['custo_minuto_maquina']) ?>" required>
                            </div>
                        </div>

                        <!-- Preview do preço calculado -->
                        <div class="mt-4 p-4 rounded-3 bg-dark text-white text-center">
                            <div class="small text-white-50 mb-1">Preço simulado</div>
                            <div class="display-6 fw-bold" id="orc-preview">R$ 0,00</div>
                            <div class="small text-white-50 mt-1" id="orc-formula-desc">
                                Preencha os campos para calcular
                            </div>
                        </div>

                        <!-- Campo oculto com o preço calculado para submissão -->
                        <input type="hidden" name="preco_calculado" id="orc-preco-hidden" value="0">

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" name="salvar_orcamento" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i>Salvar Orçamento
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Limpar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Últimos orçamentos salvos -->
            <div class="col-lg-5">
                <div class="admin-card">
                    <h2 class="h5 mb-3">Últimos Orçamentos</h2>

                    <?php if (empty($orcamentos)): ?>
                        <p class="text-muted small">Nenhum orçamento salvo ainda.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Peça / Cliente</th>
                                        <th>Área</th>
                                        <th class="text-end">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orcamentos as $orc): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-medium small">
                                                    <?= htmlspecialchars($orc['descricao_peca'] ?? '—') ?>
                                                </div>
                                                <?php if (!empty($orc['nome_cliente'])): ?>
                                                    <div class="text-muted" style="font-size:.75rem">
                                                        <?= htmlspecialchars($orc['nome_cliente']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="text-muted" style="font-size:.7rem">
                                                    <?= date('d/m/Y H:i', strtotime($orc['criado_em'])) ?>
                                                </div>
                                            </td>
                                            <td class="small text-muted">
                                                <?= number_format((float)$orc['area_cm2'], 1, ',', '.') ?> cm²
                                            </td>
                                            <td class="text-end fw-bold">
                                                R$&nbsp;<?= number_format((float)$orc['preco_calculado'], 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ================================================== -->
        <!-- ABA: CONFIGURAÇÕES                                  -->
        <!-- ================================================== -->
        <?php elseif ($abaAtiva === 'configuracoes'): ?>

        <div class="mb-4">
            <h1 class="h3 mb-1">Configurações Globais</h1>
            <p class="text-muted mb-0">Dados fiscais e variáveis de custo da calculadora</p>
        </div>

        <?php if ($mensagemConfig !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($mensagemConfig) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($erroConfig !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erroConfig) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-4">
                <!-- Dados Fiscais -->
                <div class="col-lg-6">
                    <div class="admin-card h-100">
                        <h2 class="h5 mb-4">
                            <i class="bi bi-building me-2 text-muted"></i>Dados Fiscais
                        </h2>

                        <div class="mb-3">
                            <label class="form-label">Razão Social</label>
                            <input type="text" name="razao_social" class="form-control"
                                   value="<?= htmlspecialchars($config['razao_social']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" class="form-control"
                                   value="<?= htmlspecialchars($config['nome_fantasia']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Inscrição Estadual</label>
                            <input type="text" name="inscricao_estadual" class="form-control"
                                   value="<?= htmlspecialchars($config['inscricao_estadual']) ?>">
                        </div>
                    </div>
                </div>

                <!-- Variáveis de Custo -->
                <div class="col-lg-6">
                    <div class="admin-card h-100">
                        <h2 class="h5 mb-4">
                            <i class="bi bi-calculator me-2 text-muted"></i>Variáveis de Custo
                        </h2>
                        <p class="text-muted small mb-3">
                            Esses valores são usados como padrão na calculadora de orçamentos.
                        </p>

                        <div class="mb-3">
                            <label class="form-label">
                                Custo do MDF por cm²
                                <span class="text-muted">(R$)</span>
                            </label>
                            <input type="number" name="custo_material_cm2" class="form-control"
                                   step="0.0001" min="0"
                                   value="<?= htmlspecialchars($config['custo_material_cm2']) ?>" required>
                            <div class="form-text">Custo da matéria-prima por centímetro quadrado de MDF.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Custo por minuto de máquina
                                <span class="text-muted">(R$)</span>
                            </label>
                            <input type="number" name="custo_minuto_maquina" class="form-control"
                                   step="0.0001" min="0"
                                   value="<?= htmlspecialchars($config['custo_minuto_maquina']) ?>" required>
                            <div class="form-text">Custo operacional da máquina laser por minuto.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Markup padrão</label>
                            <input type="number" name="markup_padrao" class="form-control"
                                   step="0.1" min="0.1"
                                   value="<?= htmlspecialchars($config['markup_padrao']) ?>" required>
                            <div class="form-text">
                                Multiplicador de precificação. Ex: <code>3</code> = 3× o custo total.
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded small text-muted">
                            <strong>Fórmula aplicada:</strong><br>
                            <code>Preço = (Custo_mat × Área_cm² + Custo_maq × Tempo_min) × Markup</code>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" name="salvar_configuracoes" class="btn btn-dark">
                        <i class="bi bi-save me-1"></i>Salvar Configurações
                    </button>
                </div>
            </div>
        </form>

        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
// Módulo: Listagem de Produtos com AJAX
// ============================================================
(function () {
    if (document.getElementById('form-filtros') === null) return;

    const container  = document.getElementById('produtos-container');
    const form       = document.getElementById('form-filtros');
    const btnLimpar  = document.getElementById('btn-limpar');

    const filtros = {
        busca:      '',
        categoria:  '',
        status:     '',
        preco_min:  '',
        preco_max:  '',
        pagina:     1,
    };

    function carregarProdutos(pagina) {
        filtros.pagina = pagina || 1;

        const params = new URLSearchParams();
        Object.entries(filtros).forEach(([k, v]) => {
            if (v !== '') params.set(k, v);
        });

        container.innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Carregando...
            </div>`;

        fetch('api/produtos-lista.php?' + params.toString())
            .then(r => {
                if (!r.ok) throw new Error('Erro ao carregar produtos');
                return r.text();
            })
            .then(html => {
                container.innerHTML = html;
                // Delegação: cliques na paginação
                container.querySelectorAll('[data-pagina]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        carregarProdutos(parseInt(btn.dataset.pagina, 10));
                    });
                });
            })
            .catch(err => {
                container.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
            });
    }

    form.addEventListener('submit', e => {
        e.preventDefault();
        const fd = new FormData(form);
        filtros.busca     = fd.get('busca')     || '';
        filtros.categoria = fd.get('categoria') || '';
        filtros.status    = fd.get('status')    || '';
        filtros.preco_min = fd.get('preco_min') || '';
        filtros.preco_max = fd.get('preco_max') || '';
        carregarProdutos(1);
    });

    btnLimpar.addEventListener('click', () => {
        form.reset();
        Object.assign(filtros, { busca:'', categoria:'', status:'', preco_min:'', preco_max:'' });
        carregarProdutos(1);
    });

    // Carga inicial
    carregarProdutos(1);
}());

// ============================================================
// Módulo: Calculadora de Orçamento
// ============================================================
(function () {
    const campos = ['orc-largura','orc-altura','orc-tempo','orc-markup',
                    'orc-custo-mat','orc-custo-maq'];
    const camposEl = campos.map(id => document.getElementById(id));

    if (camposEl.some(el => el === null)) return; // Aba não está ativa

    const areaEl    = document.getElementById('orc-area');
    const previewEl = document.getElementById('orc-preview');
    const descEl    = document.getElementById('orc-formula-desc');
    const hiddenEl  = document.getElementById('orc-preco-hidden');

    function calcular() {
        const larg    = parseFloat(document.getElementById('orc-largura').value)    || 0;
        const alt     = parseFloat(document.getElementById('orc-altura').value)     || 0;
        const tempo   = parseFloat(document.getElementById('orc-tempo').value)      || 0;
        const markup  = parseFloat(document.getElementById('orc-markup').value)     || 0;
        const cMat    = parseFloat(document.getElementById('orc-custo-mat').value)  || 0;
        const cMaq    = parseFloat(document.getElementById('orc-custo-maq').value)  || 0;

        const area         = larg * alt;
        const custoMat     = cMat * area;
        const custoMaqTot  = cMaq * tempo;
        const total        = (custoMat + custoMaqTot) * markup;

        const fmt = n => n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        areaEl.textContent    = fmt(area);
        previewEl.textContent = 'R$ ' + fmt(total);
        hiddenEl.value        = total.toFixed(2);

        descEl.textContent = total > 0
            ? `(R$ ${fmt(custoMat)} mat. + R$ ${fmt(custoMaqTot)} máq.) × ${markup} markup`
            : 'Preencha os campos para calcular';
    }

    document.querySelectorAll('.orc-calc').forEach(el => {
        el.addEventListener('input', calcular);
    });

    // Recalcula após reset do formulário
    document.getElementById('form-orcamento')?.addEventListener('reset', () => {
        setTimeout(calcular, 10);
    });

    calcular();
}());
</script>
</body>
</html>
