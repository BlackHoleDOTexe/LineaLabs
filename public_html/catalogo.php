<?php
require_once dirname(__DIR__) . '/private/config.php';

$paginaAtual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if (!$paginaAtual || $paginaAtual < 1) {
    $paginaAtual = 1;
}

$busca = trim($_GET['busca'] ?? '');

$produtosPorPagina = 8;
$offset = ($paginaAtual - 1) * $produtosPorPagina;

$where = "WHERE ativo = 1";
$params = [];

if ($busca !== '') {
    $palavras = preg_split('/\s+/', $busca);
    $condicoesBusca = [];

    foreach ($palavras as $index => $palavra) {
        $palavra = trim($palavra);

        if ($palavra === '') {
            continue;
        }

        $paramNome = ":busca_nome{$index}";
        $paramDesc = ":busca_desc{$index}";

        $condicoesBusca[] = "(
            nome LIKE {$paramNome}
            OR COALESCE(descricao, '') LIKE {$paramDesc}
        )";

        $params[$paramNome] = '%' . $palavra . '%';
        $params[$paramDesc] = '%' . $palavra . '%';
    }

    if (!empty($condicoesBusca)) {
        $where .= " AND " . implode(' AND ', $condicoesBusca);
    }
}

$sqlTotalProdutos = "SELECT COUNT(*) FROM produtos {$where}";
$stmtTotal = $pdo->prepare($sqlTotalProdutos);

foreach ($params as $chave => $valor) {
    $stmtTotal->bindValue($chave, $valor, PDO::PARAM_STR);
}

$stmtTotal->execute();
$totalProdutos = (int) $stmtTotal->fetchColumn();

$totalPaginas = (int) ceil($totalProdutos / $produtosPorPagina);

if ($totalPaginas > 0 && $paginaAtual > $totalPaginas) {
    $paginaAtual = $totalPaginas;
    $offset = ($paginaAtual - 1) * $produtosPorPagina;
}

$sqlProdutos = "SELECT *
                FROM produtos
                {$where}
                ORDER BY id DESC
                LIMIT :limit OFFSET :offset";

$stmtProdutos = $pdo->prepare($sqlProdutos);

foreach ($params as $chave => $valor) {
    $stmtProdutos->bindValue($chave, $valor, PDO::PARAM_STR);
}

$stmtProdutos->bindValue(':limit', $produtosPorPagina, PDO::PARAM_INT);
$stmtProdutos->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtProdutos->execute();
$produtos = $stmtProdutos->fetchAll();

$sqlImagens = "SELECT *
               FROM produto_imagens
               ORDER BY produto_id, ordem, id";
$imagens = $pdo->query($sqlImagens)->fetchAll();

$imagensPorProduto = [];
foreach ($imagens as $imagem) {
    $produtoId = $imagem['produto_id'];
    $imagensPorProduto[$produtoId][] = $imagem;
}

function renderizarPaginacao(int $paginaAtual, int $totalPaginas, string $busca): void
{
    if ($totalPaginas <= 1) {
        return;
    }

    $buscaSegura = htmlspecialchars($busca, ENT_QUOTES, 'UTF-8');
    ?>
    <nav aria-label="Paginação do catálogo">
      <ul class="pagination justify-content-center mb-0">

        <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
          <a
            class="page-link"
            href="#"
            <?php if ($paginaAtual > 1): ?>
              data-pagina="<?= $paginaAtual - 1 ?>"
              data-busca="<?= $buscaSegura ?>"
            <?php endif; ?>
          >
            Anterior
          </a>
        </li>

        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
          <li class="page-item <?= $i === $paginaAtual ? 'active' : '' ?>">
            <a
              class="page-link"
              href="#"
              data-pagina="<?= $i ?>"
              data-busca="<?= $buscaSegura ?>"
            >
              <?= $i ?>
            </a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
          <a
            class="page-link"
            href="#"
            <?php if ($paginaAtual < $totalPaginas): ?>
              data-pagina="<?= $paginaAtual + 1 ?>"
              data-busca="<?= $buscaSegura ?>"
            <?php endif; ?>
          >
            Próxima
          </a>
        </li>

      </ul>
    </nav>
    <?php
}
?>

<form class="mb-4" id="form-busca-catalogo">
  <div class="input-group">
    <input
      type="text"
      id="campo-busca"
      name="busca"
      class="form-control"
      placeholder="Buscar produtos..."
      value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
    >

    <button type="submit" class="btn btn-dark">
      Buscar
    </button>

    <?php if ($busca !== ''): ?>
      <button
        type="button"
        class="btn btn-outline-secondary"
        onclick="carregarPagina(1, ''); document.getElementById('campo-busca').value = '';"
      >
        Voltar
      </button>
    <?php endif; ?>
  </div>
</form>

<div class="mb-4">
  <?php renderizarPaginacao($paginaAtual, $totalPaginas, $busca); ?>
</div>

<div class="row g-3">
  <?php if (!empty($produtos)): ?>
    <?php foreach ($produtos as $produto): ?>
      <?php
        $imagensDoProduto = $imagensPorProduto[$produto['id']] ?? [];
        $primeiraImagem = $imagensDoProduto[0]['arquivo'] ?? 'default.png';
      ?>

      <div class="col-6 col-md-4 col-lg-3 animar-quando-aparecer">
        <div class="card h-100">
          <img
            src="/media/image.php?file=<?= urlencode($primeiraImagem) ?>"
            class="card-img-top produto-card-img"
            alt="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
          >

          <div class="card-body d-flex flex-column">
            <h5 class="product-title">
              <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>
            </h5>

            <p class="card-text product-description">
              <?= htmlspecialchars(mb_strimwidth($produto['descricao'] ?? '', 0, 80, '...'), ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="mt-auto">
              <p class="preco text-end mb-2">
                R$ <?= number_format((float)($produto['preco'] ?? 0), 2, ',', '.') ?>
              </p>

              <button
                type="button"
                class="btn btn-dark w-100"
                data-bs-toggle="modal"
                data-bs-target="#modalProduto<?= $produto['id'] ?>"
              >
                Ver detalhes
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="alert alert-secondary mb-0">
        Nenhum produto encontrado para essa busca.
      </div>
    </div>
  <?php endif; ?>
</div>

<div class="mt-4">
  <?php renderizarPaginacao($paginaAtual, $totalPaginas, $busca); ?>
</div>

<?php foreach ($produtos as $produto): ?>
  <?php
    $imagensDoProduto = $imagensPorProduto[$produto['id']] ?? [];
    $mensagem = "Olá, gostaria de solicitar um orçamento do produto " . $produto['nome'];
    $linkWhats = "https://wa.me/5544997554052?text=" . urlencode($mensagem);
  ?>

  <div class="modal fade" id="modalProduto<?= $produto['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title"><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <div class="container py-4">
            <div class="row g-4 align-items-start">

              <div class="col-12 col-md-6">
                <?php if (!empty($imagensDoProduto)): ?>
                  <div id="carouselProduto<?= $produto['id'] ?>" class="carousel slide">
                    <div class="carousel-inner rounded">

                      <?php foreach ($imagensDoProduto as $index => $imagem): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                          <img
                            src="/media/image.php?file=<?= urlencode($imagem['arquivo']) ?>"
                            class="d-block w-100 produto-modal-img rounded"
                            alt="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                          >
                        </div>
                      <?php endforeach; ?>

                    </div>

                    <?php if (count($imagensDoProduto) > 1): ?>
                      <button
                        class="carousel-control-prev"
                        type="button"
                        data-bs-target="#carouselProduto<?= $produto['id'] ?>"
                        data-bs-slide="prev"
                      >
                        <span class="carousel-control-prev-icon"></span>
                      </button>

                      <button
                        class="carousel-control-next"
                        type="button"
                        data-bs-target="#carouselProduto<?= $produto['id'] ?>"
                        data-bs-slide="next"
                      >
                        <span class="carousel-control-next-icon"></span>
                      </button>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <img
                    src="/media/image.php?file=default.png"
                    class="img-fluid rounded produto-modal-img"
                    alt="Imagem padrão"
                  >
                <?php endif; ?>
              </div>

              <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h2 class="mb-0"><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></h2>

                  <p class="preco fs-4 mb-0 text-end">
                    R$ <?= number_format((float)($produto['preco'] ?? 0), 2, ',', '.') ?>
                  </p>
                </div>

                <p class="product-description mb-4">
                  <?= nl2br(htmlspecialchars($produto['descricao'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                </p>

                <a
                  href="<?= $linkWhats ?>"
                  target="_blank"
                  class="btn btn-success w-100 w-md-auto d-flex align-items-center justify-content-center gap-2"
                >
                  <i class="bi bi-whatsapp"></i>
                  Pedir pelo WhatsApp
                </a>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
<?php endforeach; ?>