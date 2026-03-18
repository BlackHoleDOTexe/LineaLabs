<?php
$produtos = [
    [
        'id' => 1,
        'nome' => 'Nome do Produto 1',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 1 para aparecer no modal.',
        'preco' => '49,90',
        'imagem' => 'img/template_1.jpg'
    ],
    [
        'id' => 2,
        'nome' => 'Nome do Produto 2',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 2 para aparecer no modal.',
        'preco' => '59,90',
        'imagem' => 'img/template_1.jpg'
    ]
    ,
    [
        'id' => 3,
        'nome' => 'Nome do Produto 3',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 3 para aparecer no modal.',
        'preco' => '69,90',
        'imagem' => 'img/template_1.jpg'
    ]
    ,
    [
        'id' => 4,
        'nome' => 'Nome do Produto 4',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 4 para aparecer no modal.',
        'preco' => '79,90',
        'imagem' => 'img/template_1.jpg'
    ],
        [
        'id' => 5,
        'nome' => 'Nome do Produto 5',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 5 para aparecer no modal.',
        'preco' => '49,90',
        'imagem' => 'img/template_1.jpg'
    ],
    [
        'id' => 6,
        'nome' => 'Nome do Produto 6',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 6 para aparecer no modal.',
        'preco' => '59,90',
        'imagem' => 'img/template_1.jpg'
    ]
    ,
    [
        'id' => 7,
        'nome' => 'Nome do Produto 7',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 7 para aparecer no modal.',
        'preco' => '69,90',
        'imagem' => 'img/template_1.jpg'
    ]
    ,
    [
        'id' => 8,
        'nome' => 'Nome do Produto 8',
        'descricao' => 'Descrição do produto aqui.',
        'descricao_completa' => 'Descrição completa do produto 8 para aparecer no modal.',
        'preco' => '79,90',
        'imagem' => 'img/template_1.jpg'
    ]
];
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
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100">
            <img
              src="<?= htmlspecialchars($produto['imagem']) ?>"
              class="card-img-top produto-img"
              alt="<?= htmlspecialchars($produto['nome']) ?>"
            >

            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($produto['nome']) ?></h5>

              <p class="card-text">
                <?= htmlspecialchars($produto['descricao']) ?>
              </p>

              <p class="preco">R$ <?= htmlspecialchars($produto['preco']) ?></p>

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

      <!-- Marca -->
      <div class="col-12 col-md-4">
        <h4 class="logo">Linea Labs</h4>
        <p class="small">
          Peças personalizadas em MDF e corte a laser.
        </p>
      </div>

      <!-- Contato -->
      <div class="col-12 col-md-4">
        <h6>Contato</h6>

        <p class="mb-1">
          WhatsApp: (44) 99755-4052
        </p>

        <p class="mb-1">
          Instagram: @linealabs.br
        </p>

      </div>

      <!-- Links -->
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
                  <img
                    src="<?= htmlspecialchars($produto['imagem']) ?>"
                    class="img-fluid rounded"
                    alt="<?= htmlspecialchars($produto['nome']) ?>"
                  >
                </div>

                <div class="col-12 col-md-6">
                  <h2><?= htmlspecialchars($produto['nome']) ?></h2>
                  <p class="preco fs-3">R$ <?= htmlspecialchars($produto['preco']) ?></p>
                  <p><?= htmlspecialchars($produto['descricao_completa']) ?></p>

                  <a href="#" class="btn btn-dark w-100 w-md-auto">
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