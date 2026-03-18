<?php
require_once dirname(__DIR__) . '/config.php';

$sqlProdutos = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY id DESC";
$produtos = $pdo->query($sqlProdutos)->fetchAll();

$sqlImagens = "SELECT * FROM produto_imagens ORDER BY produto_id, ordem, id";
$imagens = $pdo->query($sqlImagens)->fetchAll();

$imagensPorProduto = [];

foreach ($imagens as $imagem) {
    $produtoId = $imagem['produto_id'];
    $imagensPorProduto[$produtoId][] = $imagem;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Linea Labs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/index_style.css?v=6">
</head>
<body>

<section class="hero container-fluid">
  <div class="hero-bg-svg">
    <?php include 'img/linea-labs-logo.svg'; ?>
  </div>

  <div class="hero-content">
    <h1 class="display-2 logo tracking-in-expand">Linea Labs</h1>
    <p class="lead mb-4 tracking-in-expand">O detalhe que faltava.</p>
    <a href="#catalogo" class="btn btn-dark btn-lg tracking-in-expand">Ver Catálogo</a>
  </div>
</section>

<section id="catalogo" class="catalogo container-fluid bg-2 px-3 px-md-0">
  <h2 class="mb-4">Destaques:</h2>

  <div class="row g-3 container">
    <?php foreach ($produtos as $produto): ?>
      <?php
        $imagensDoProduto = $imagensPorProduto[$produto['id']] ?? [];
        $primeiraImagem = $imagensDoProduto[0]['arquivo'] ?? 'default.png';
      ?>

      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100">
          <img
            src="/uploads/products/<?= htmlspecialchars($primeiraImagem) ?>"
            class="card-img-top produto-img"
            alt="<?= htmlspecialchars($produto['nome']) ?>"
          >

          <div class="card-body d-flex flex-column">
            <h5 class="card-title">
              <?= htmlspecialchars($produto['nome']) ?>
            </h5>

            <p class="card-text">
              <?= htmlspecialchars(mb_strimwidth($produto['descricao'] ?? '', 0, 80, '...')) ?>
            </p>

            <p class="preco">
              R$ <?= number_format((float)($produto['preco'] ?? 0), 2, ',', '.') ?>
            </p>

            <button
              type="button"
              class="btn btn-dark w-100 mt-auto"
              data-bs-toggle="modal"
              data-bs-target="#modalProduto<?= $produto['id'] ?>"
            >
              Ver detalhes
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<footer class="footer bg-dark text-light">
  <div class="container py-4">
    <div class="row g-4">

      <div class="col-12 col-md-4">
        <h4 class="logo">Linea Labs</h4>
        <p class="small">
          Peças personalizadas em MDF e corte a laser.
        </p>
      </div>

      <div class="col-12 col-md-4">
        <h6>Contato</h6>

        <p class="mb-1">
          WhatsApp: (44) 99755-4052
        </p>

        <p class="mb-1">
          Instagram: @linealabs.br
        </p>
      </div>

      <div class="col-12 col-md-4">
        <h6>Links</h6>

        <a href="#" class="footer-link">
          Catálogo
        </a>
        <br>

        <a href="#" class="footer-link">
          Sobre
        </a>
      </div>

    </div>
  </div>

  <div class="text-center py-2">
    <small>
      © <?= date('Y') ?> Linea Labs — Todos os direitos reservados
    </small>
  </div>
</footer>

<?php foreach ($produtos as $produto): ?>
  <?php $imagensDoProduto = $imagensPorProduto[$produto['id']] ?? []; ?>

  <div class="modal fade" id="modalProduto<?= $produto['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title"><?= htmlspecialchars($produto['nome']) ?></h5>
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
                            src="/uploads/products/<?= htmlspecialchars($imagem['arquivo']) ?>"
                            class="d-block w-100 img-fluid rounded"
                            alt="<?= htmlspecialchars($produto['nome']) ?>"
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
                    src="/uploads/products/default.png"
                    class="img-fluid rounded"
                    alt="Imagem padrão"
                  >
                <?php endif; ?>
              </div>

              <div class="col-12 col-md-6">
                <h2><?= htmlspecialchars($produto['nome']) ?></h2>

                <p class="preco fs-3">
                  R$ <?= number_format((float)($produto['preco'] ?? 0), 2, ',', '.') ?>
                </p>

                <p>
                  <?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?>
                </p>

                <?php
                $mensagem = "Olá, gostaria de solicitar um orçamento do produto " . $produto['nome'];

                $linkWhats =
                  "https://wa.me/5544997554052?text=" .
                  urlencode($mensagem);
                ?>

                <a
                  href="<?= $linkWhats ?>"
                  target="_blank"
                  class="btn btn-success w-100 w-md-auto"
                >
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>