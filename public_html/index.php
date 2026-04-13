<?php
require_once dirname(__DIR__) . '/private/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Linea Labs</title>

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
    <a href="#catalogo" class="btn btn-dark btn-lg text-focus-in">Ver Catálogo</a>
  </div>
</section>

<section id="catalogo" class="catalogo container-fluid bg-2 px-3 px-md-0">
  <div class="container py-4">
    <h2 class="mb-4 justify-content-center text-center logo">NOSSOS PRODUTOS PERSONALIZADOS</h2>

    <div id="catalogo-container">
      <?php include __DIR__ . '/catalogo.php'; ?>
    </div>
  </div>
</section>

<section id="orçamento-personalizado" class="container-fluid bg-3 py-4">
  <div class="container py-4 justify-content-center text-center">
    <h2 class="mb-4 logo ">Pedido Personalizado</h2>
    <h4 class="mb-4 ">
      Não encontrou exatamente o que desejava em nosso catálogo? Entre em contato para solicitar um orçamento personalizado. Desenvolvemos peças e projetos sob medida, com atenção aos detalhes, qualidade no acabamento e foco na sua necessidade.
    </h4>
    <a href="https://wa.me/5544997554052" class="btn btn-dark btn-lg bi bi-whatsapp " target="_blank">
      Solicitar Orçamento
    </a>
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

        <a href="https://www.instagram.com/linealabs.br/" class="mb-1 footer-link" target="_blank">
          Instagram: @linealabs.br
        </a>
      </div>

      <div class="col-12 col-md-4">
        <h6>Links</h6>

        <a href="#catalogo" class="footer-link">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/index.js?v=7"></script>

</body>
</html>