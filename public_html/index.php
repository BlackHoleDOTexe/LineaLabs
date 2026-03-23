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
  <link rel="stylesheet" href="css/index_style.css?v=6">
</head>
<body>

<section class="hero container-fluid">
  <div class="hero-bg-svg">
    <?php include __DIR__ . '/img/linea-labs-logo.svg'; ?>
  </div>

  <div class="hero-content">
    <h1 class="display-2 logo tracking-in-expand">Linea Labs</h1>
    <p class="lead mb-4 tracking-in-expand">O detalhe que faltava.</p>
    <a href="#catalogo" class="btn btn-dark btn-lg tracking-in-expand">Ver Catálogo</a>
  </div>
</section>

<section id="catalogo" class="catalogo container-fluid bg-2 px-3 px-md-0">
  <div class="container py-4">
    <h2 class="mb-4">Destaques:</h2>

    <div id="catalogo-container">
      <?php include __DIR__ . '/catalogo.php'; ?>
    </div>
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

<script>
  async function carregarPagina(pagina = 1, busca = '') {
    try {
      const params = new URLSearchParams();
      params.set('pagina', pagina);

      if (busca.trim() !== '') {
        params.set('busca', busca);
      }

      const url = `catalogo.php?${params.toString()}`;

      const resposta = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!resposta.ok) {
        throw new Error(`Erro HTTP: ${resposta.status}`);
      }

      const html = await resposta.text();
      document.getElementById('catalogo-container').innerHTML = html;

      history.pushState({}, '', `?${params.toString()}#catalogo`);

      conectarFormularioBusca();
    } catch (erro) {
      console.error('Erro ao carregar catálogo:', erro);
    }
  }

  function buscarProdutos(event) {
    event.preventDefault();

    const campoBusca = document.getElementById('campo-busca');
    const busca = campoBusca ? campoBusca.value.trim() : '';

    carregarPagina(1, busca);
  }

  function conectarFormularioBusca() {
    const formBusca = document.getElementById('form-busca-catalogo');

    if (formBusca && !formBusca.dataset.listenerAttached) {
      formBusca.addEventListener('submit', buscarProdutos);
      formBusca.dataset.listenerAttached = 'true';
    }
  }

  window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search);
    const pagina = parseInt(params.get('pagina')) || 1;
    const busca = params.get('busca') || '';

    carregarPagina(pagina, busca);
  });

  document.addEventListener('DOMContentLoaded', () => {
    conectarFormularioBusca();
  });
</script>
</body>
</html>