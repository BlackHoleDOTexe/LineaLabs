<?php
require_once dirname(__DIR__) . '/private/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Linea Labs | Peças personalizadas em MDF e corte a laser</title>
  <meta name="description" content="Transformamos MDF em detalhes únicos. Peças personalizadas, brindes corporativos e decoração premium com corte a laser de alta precisão em Toledo-PR.">

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/index_style.css?v=<?= APP_version ?>">
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body>

<section class="hero container-fluid">
  <div class="hero-bg-svg text-focus-in">
    <?php include __DIR__ . '/img/linea-labs-logo.svg'; ?>
  </div>

  <div class="hero-content">
    <h1 class="display-2 logo text-focus-in">Linea Labs</h1>
    <p class="lead mb-4 text-focus-in">O detalhe que faltava.</p>
    <a href="#catalogo" class="btn btn-gold btn-lg text-focus-in">Ver Catálogo</a>
  </div>
</section>

<section id="catalogo" class="catalogo container-fluid bg-2 px-3 px-md-0">
  <div class="container py-4">
    <h2 class="mb-4 text-center">CONHEÇA NOSSOS PRODUTOS</h2>

    <div id="catalogo-container">
      <?php include __DIR__ . '/catalogo.php'; ?>
    </div>
  </div>
</section>

<section id="orçamento-personalizado" class="container-fluid bg-dark section-gold-border py-5">
  <div class="container text-center">
    <h2 class="mb-3 logo text-white">Pedido Personalizado</h2>
    <p class="lead text-white-50 mb-4 mx-auto" style="max-width: 640px;">
      Não encontrou exatamente o que desejava em nosso catálogo? Entre em contato para solicitar um orçamento personalizado. Desenvolvemos peças e projetos sob medida, com atenção aos detalhes, qualidade no acabamento e foco na sua necessidade.
    </p>
    <a href="https://wa.me/5544997554052" class="btn btn-gold btn-lg" target="_blank">
      <i class="bi bi-whatsapp me-2"></i>Solicitar Orçamento
    </a>
  </div>
</section>

<section id="institucional" class="container-fluid bg-dark py-5">
  <div class="container py-3">
    <div class="row align-items-center g-5">

      <div class="col-lg-7">
        <p class="section-eyebrow text-success text-uppercase small fw-bold mb-2">Laboratório de Criação · Toledo-PR</p>
        <h2 class="display-5 fw-bold text-white mb-4">Engenharia de Precisão.<br>Design sem Concessões.</h2>
        <p class="text-white-50 lead body-relaxed mb-3">
          A <strong class="text-white">Linea Labs</strong> opera na interseção entre tecnologia de corte a laser e design de produto.
          Cada peça é resultado de um processo de fabricação 100% próprio, onde a exatidão técnica determina cada milímetro do acabamento final.
        </p>
        <p class="text-white-50 body-relaxed">
          Sediados em Toledo-PR, desenvolvemos soluções em MDF que combinam eficiência operacional com padrão premium —
          viabilizando projetos corporativos, brindes e decoração exclusiva com custo competitivo.
        </p>
        <a href="sobre.php" class="btn btn-gold btn-lg mt-4 px-4">
          Conheça nossa História <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>

      <div class="col-lg-5">
        <div class="spec-panel">
          <div class="mb-4">
            <h6 class="text-gold text-uppercase small fw-bold mb-1">
              <i class="bi bi-cpu me-2"></i>Processo
            </h6>
            <p class="small text-white-50 mb-0">Corte e gravação a laser com controle digital de alta precisão em MDF.</p>
          </div>
          <hr class="border-secondary">
          <div class="mb-4">
            <h6 class="text-gold text-uppercase small fw-bold mb-1">
              <i class="bi bi-grid-3x3-gap me-2"></i>Produto
            </h6>
            <p class="small text-white-50 mb-0">Peças decorativas, brindes corporativos e projetos sob medida.</p>
          </div>
          <hr class="border-secondary">
          <div>
            <h6 class="text-gold text-uppercase small fw-bold mb-1">
              <i class="bi bi-award me-2"></i>Fabricação
            </h6>
            <p class="small text-white-50 mb-0">100% própria — sem terceirização, com controle total do resultado.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/index.js?v=<?= APP_version ?>"></script>

</body>
</html>
