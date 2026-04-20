<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once dirname(__DIR__)    . '/app/Repository/ProductRepository.php';
require_once dirname(__DIR__)    . '/app/Repository/ImageRepository.php';

$productRepo = new ProductRepository($pdo);
$imageRepo   = new ImageRepository($pdo);

// ── Parâmetros de entrada ─────────────────────────────────────────
$paginaAtual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if (!$paginaAtual || $paginaAtual < 1) {
    $paginaAtual = 1;
}

$busca     = trim($_GET['busca']     ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$precoMin  = trim($_GET['preco_min'] ?? '');
$precoMax  = trim($_GET['preco_max'] ?? '');

$filters = [
    'busca'     => $busca,
    'categoria' => $categoria,
    'preco_min' => $precoMin,
    'preco_max' => $precoMax,
];

$hasFilters = ($busca !== '' || $categoria !== '' || $precoMin !== '' || $precoMax !== '');

// ── Paginação ────────────────────────────────────────────────────
$produtosPorPagina = 8;
$totalProdutos     = $productRepo->countPublic($filters);
$totalPaginas      = (int) ceil($totalProdutos / $produtosPorPagina);

if ($totalPaginas > 0 && $paginaAtual > $totalPaginas) {
    $paginaAtual = $totalPaginas;
}

$offset   = ($paginaAtual - 1) * $produtosPorPagina;
$produtos = $productRepo->findPublic($filters, $produtosPorPagina, $offset);

// ── Dados complementares ──────────────────────────────────────────
$categorias        = $productRepo->getCategories();
$imagensPorProduto = $imageRepo->groupByProduct();

// ── Helper de paginação ───────────────────────────────────────────
function renderizarPaginacao(
    int $paginaAtual,
    int $totalPaginas,
    string $busca,
    string $categoria,
    string $precoMin,
    string $precoMax
): void {
    if ($totalPaginas <= 1) {
        return;
    }

    $buscaS     = htmlspecialchars($busca,     ENT_QUOTES, 'UTF-8');
    $categoriaS = htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8');
    $precoMinS  = htmlspecialchars($precoMin,  ENT_QUOTES, 'UTF-8');
    $precoMaxS  = htmlspecialchars($precoMax,  ENT_QUOTES, 'UTF-8');
    ?>
    <nav aria-label="Paginação do catálogo">
      <ul class="pagination justify-content-center mb-0">

        <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="#"
            <?php if ($paginaAtual > 1): ?>
              data-pagina="<?= $paginaAtual - 1 ?>"
              data-busca="<?= $buscaS ?>"
              data-categoria="<?= $categoriaS ?>"
              data-preco-min="<?= $precoMinS ?>"
              data-preco-max="<?= $precoMaxS ?>"
            <?php endif; ?>
          >Anterior</a>
        </li>

        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
          <li class="page-item <?= $i === $paginaAtual ? 'active' : '' ?>">
            <a class="page-link" href="#"
              data-pagina="<?= $i ?>"
              data-busca="<?= $buscaS ?>"
              data-categoria="<?= $categoriaS ?>"
              data-preco-min="<?= $precoMinS ?>"
              data-preco-max="<?= $precoMaxS ?>"
            ><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
          <a class="page-link" href="#"
            <?php if ($paginaAtual < $totalPaginas): ?>
              data-pagina="<?= $paginaAtual + 1 ?>"
              data-busca="<?= $buscaS ?>"
              data-categoria="<?= $categoriaS ?>"
              data-preco-min="<?= $precoMinS ?>"
              data-preco-max="<?= $precoMaxS ?>"
            <?php endif; ?>
          >Próxima</a>
        </li>

      </ul>
    </nav>
    <?php
}
?>

<form class="mb-4" id="form-busca-catalogo">
  <div class="row g-2">

    <div class="col-12">
      <div class="input-group">
        <input
          type="text"
          id="campo-busca"
          name="busca"
          class="form-control"
          placeholder="Buscar produtos..."
          value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
        >
        <button type="submit" class="btn btn-gold">
          <i class="bi bi-search me-1"></i>Buscar
        </button>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <select id="campo-categoria" name="categoria" class="form-select">
        <option value="">Todas as categorias</option>
        <?php foreach ($categorias as $cat): ?>
          <option
            value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"
            <?= $categoria === $cat ? 'selected' : '' ?>
          >
            <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-6 col-md-4">
      <div class="input-group">
        <span class="input-group-text">R$</span>
        <input
          type="number"
          id="campo-preco-min"
          name="preco_min"
          class="form-control"
          placeholder="Preço mín."
          min="0"
          step="0.01"
          value="<?= htmlspecialchars($precoMin, ENT_QUOTES, 'UTF-8') ?>"
        >
      </div>
    </div>

    <div class="col-6 col-md-4">
      <div class="input-group">
        <span class="input-group-text">R$</span>
        <input
          type="number"
          id="campo-preco-max"
          name="preco_max"
          class="form-control"
          placeholder="Preço máx."
          min="0"
          step="0.01"
          value="<?= htmlspecialchars($precoMax, ENT_QUOTES, 'UTF-8') ?>"
        >
      </div>
    </div>

    <?php if ($hasFilters): ?>
      <div class="col-12">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-limpar-filtros">
          <i class="bi bi-x-circle me-1"></i>Limpar filtros
        </button>
      </div>
    <?php endif; ?>

  </div>
</form>

<div class="mb-4">
  <?php renderizarPaginacao($paginaAtual, $totalPaginas, $busca, $categoria, $precoMin, $precoMax); ?>
</div>

<div class="row g-3">
  <?php if (!empty($produtos)): ?>
    <?php foreach ($produtos as $produto): ?>
      <?php
        $imagensDoProduto = $imagensPorProduto[$produto['id']] ?? [];
        $primeiraImagem   = $imagensDoProduto[0]['arquivo'] ?? 'default.png';
      ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100">
          <div class="card-img-container">
            <span class="card-category">
              <?= htmlspecialchars($produto['categoria'] ?? 'Produto', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <img
              src="/media/image.php?file=<?= urlencode($primeiraImagem) ?>"
              class="produto-card-img"
              alt="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
              loading="lazy"
            >
          </div>
          <div class="card-body">
            <h5 class="product-title">
              <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>
            </h5>
            <p class="product-description">
              <?= htmlspecialchars(mb_strimwidth($produto['descricao'] ?? '', 0, 100, '...'), ENT_QUOTES, 'UTF-8') ?>
            </p>
            <div class="price-container">
              <p class="preco">
                <?= number_format((float)($produto['preco'] ?? 0), 2, ',', '.') ?>
              </p>
              <button
                type="button"
                class="btn btn-gold"
                data-bs-toggle="modal"
                data-bs-target="#modalProduto<?= $produto['id'] ?>"
              >
                <i class="bi bi-eye me-1"></i>Ver detalhes
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="alert alert-secondary mb-0">
        Nenhum produto encontrado<?= $hasFilters ? ' para os filtros selecionados' : '' ?>.
      </div>
    </div>
  <?php endif; ?>
</div>

<div class="mt-4">
  <?php renderizarPaginacao($paginaAtual, $totalPaginas, $busca, $categoria, $precoMin, $precoMax); ?>
</div>

<?php foreach ($produtos as $produto): ?>
  <?php
    $imagensDoProduto = $imagensPorProduto[$produto['id']] ?? [];
    $mensagem  = "Olá, gostaria de solicitar um orçamento do produto " . $produto['nome'];
    $linkWhats = "https://wa.me/" . EMP_WHATSAPP . "?text=" . urlencode($mensagem);
  ?>
  <div class="modal fade modal-produto" id="modalProduto<?= $produto['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">

        <div class="modal-header px-4 py-2">
          <span class="small text-muted">
            <i class="bi bi-box-seam me-1"></i>Catálogo Linea Labs
          </span>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body p-0">
          <div class="row g-0">

            <div class="col-12 col-md-7 modal-img-col">
              <?php if (!empty($imagensDoProduto)): ?>
                <div id="carouselProduto<?= $produto['id'] ?>" class="carousel slide h-100">
                  <?php if (count($imagensDoProduto) > 1): ?>
                    <div class="carousel-indicators">
                      <?php foreach ($imagensDoProduto as $index => $imagem): ?>
                        <button
                          type="button"
                          data-bs-target="#carouselProduto<?= $produto['id'] ?>"
                          data-bs-slide-to="<?= $index ?>"
                          <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?>
                          aria-label="Slide <?= $index + 1 ?>"
                        ></button>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <div class="carousel-inner h-100">
                    <?php foreach ($imagensDoProduto as $index => $imagem): ?>
                      <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                        <img
                          src="/media/image.php?file=<?= urlencode($imagem['arquivo']) ?>"
                          class="d-block w-100 produto-modal-img"
                          alt="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <?php if (count($imagensDoProduto) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduto<?= $produto['id'] ?>" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselProduto<?= $produto['id'] ?>" data-bs-slide="next">
                      <span class="carousel-control-next-icon"></span>
                    </button>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <img src="/media/image.php?file=default.png" class="d-block w-100 produto-modal-img" alt="Imagem padrão">
              <?php endif; ?>
            </div>

            <div class="col-12 col-md-5 modal-info-col d-flex flex-column">
              <div class="p-4 p-md-5 d-flex flex-column h-100">

                <h2 class="modal-product-title mb-3">
                  <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                  <span class="modal-preco">
                    R$ <?= number_format((float)($produto['preco'] ?? 0), 2, ',', '.') ?>
                  </span>
                  <a
                    href="<?= $linkWhats ?>"
                    target="_blank"
                    class="btn btn-success d-flex align-items-center gap-2"
                  >
                    <i class="bi bi-whatsapp"></i>
                    Pedir pelo WhatsApp
                  </a>
                </div>

                <hr class="mt-0 mb-4">

                <div class="flex-grow-1">
                  <p class="small fw-semibold text-uppercase text-muted section-eyebrow mb-2">Descrição</p>
                  <p class="modal-description text-secondary body-relaxed">
                    <?= nl2br(htmlspecialchars($produto['descricao'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                  </p>
                </div>

              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
<?php endforeach; ?>
