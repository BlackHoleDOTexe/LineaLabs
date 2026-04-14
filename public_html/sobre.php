<?php
require_once dirname(__DIR__) . '/private/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre Nós | Linea Labs</title>
  <meta name="description" content="Conheça a história da Linea Labs — fabricação própria de peças em MDF com corte a laser de alta precisão em Toledo-PR.">

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/index_style.css?v=<?= APP_version ?>">
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand logo fs-3" href="index.php">Linea Labs</a>
    <div class="d-flex">
      <a href="index.php#catalogo" class="btn btn-gold btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar ao Catálogo
      </a>
    </div>
  </div>
</nav>

<!-- Cabeçalho da página -->
<section class="bg-1 section-gold-border py-5 text-center">
  <div class="container py-2">
    <p class="section-eyebrow text-gold text-uppercase small fw-bold mb-2">Nossa Operação · Toledo-PR</p>
    <h1 class="display-4 fw-bold logo">Sobre a Linea Labs</h1>
  </div>
</section>

<!-- Conteúdo principal -->
<section class="container-fluid bg-dark py-5">
  <div class="container py-3">
    <div class="row align-items-start g-5">

      <div class="col-lg-7">
        <h2 class="display-5 fw-bold text-white mb-4">Engenharia de Precisão.<br>Design sem Concessões.</h2>

        <div class="text-white-50 lead body-relaxed mb-4">
          <p>
            A <strong class="text-white">Linea Labs</strong> surgiu da convergência entre o estudo tecnológico e a busca por soluções práticas de design e personalização.
          </p>
        </div>

        <div class="text-white-50 body-relaxed">
          <p>
            Sediada em Toledo-PR, nossa produção foca na integração entre processos digitais e um rigoroso controle de qualidade. Fundada por Eduardo Godoy, a empresa foi idealizada a partir da análise do potencial técnico e da eficiência das máquinas CNC a laser.
          </p>
          <p>
            Com visão estratégica e foco em viabilidade, Eduardo identificou uma lacuna no mercado: o alto custo de itens personalizados em madeira. O que começou como um projeto de fabricação própria para uso pessoal evoluiu para um modelo de negócio focado em otimizar a produção para oferecer design de alto padrão com custo competitivo.
          </p>
          <p>
            Hoje, operamos com fabricação 100% própria, investindo em aprimoramento constante das técnicas de corte e gravação. O conceito <strong class="text-white">"Premium Acessível"</strong> define nossa entrega: o acabamento de alto nível aliado à eficiência operacional que garante o melhor custo-benefício para o cliente.
          </p>
        </div>

        <blockquote class="mt-5 spec-panel">
          <p class="fw-bold text-white mb-0">
            "Nosso compromisso é com a exatidão técnica e com a entrega de um produto que supere as expectativas de durabilidade e design."
          </p>
          <footer class="blockquote-footer mt-2 text-white-50">Eduardo Godoy, Fundador</footer>
        </blockquote>
      </div>

      <div class="col-lg-5">
        <div class="spec-panel">
          <h4 class="mb-4 logo text-white">Linea Labs</h4>

          <div class="mb-4">
            <h6 class="text-gold text-uppercase small fw-bold mb-1">
              <i class="bi bi-bullseye me-2"></i>Nossa Missão
            </h6>
            <p class="small text-white-50 mb-0">Unir fabricação digital e cuidado manual para criar peças que contam histórias com alta precisão.</p>
          </div>

          <hr class="border-secondary">

          <div class="mb-4">
            <h6 class="text-gold text-uppercase small fw-bold mb-1">
              <i class="bi bi-geo-alt me-2"></i>Localização
            </h6>
            <p class="small text-white-50 mb-0">Toledo, Paraná — Jardim La Salle.</p>
          </div>

          <hr class="border-secondary">

          <div class="mb-4">
            <h6 class="text-gold text-uppercase small fw-bold mb-1">
              <i class="bi bi-scissors me-2"></i>O que fazemos
            </h6>
            <p class="small text-white-50 mb-0">Corte e gravação a laser em MDF, brindes corporativos e itens decorativos sob medida.</p>
          </div>

          <hr class="border-secondary">

          <div class="d-grid gap-2 mt-4">
            <a href="https://wa.me/<?= EMP_WHATSAPP ?>" class="btn btn-gold py-2" target="_blank">
              <i class="bi bi-whatsapp me-2"></i>Fale Conosco
            </a>
            <a href="https://www.instagram.com/linealabs.com.br/" class="btn btn-outline-light py-2" target="_blank">
              <i class="bi bi-instagram me-2"></i>Siga no Instagram
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
