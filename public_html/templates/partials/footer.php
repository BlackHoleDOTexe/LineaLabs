<?php
// Garante que as constantes da empresa estão disponíveis
// (config.php já deve ter sido carregado pelo controlador)
?>
<footer class="footer bg-dark text-light border-top border-secondary">
  <div class="container py-4">
    <div class="row g-4">

      <div class="col-12 col-md-4">
        <h4 class="logo"><?= EMP_NOME_FANTASIA ?></h4>
        <p class="small text-white-50 mb-2">
          Peças personalizadas em MDF e corte a laser.
        </p>
        <p class="mb-0">
            <a href="sobre.php" class="small text-success text-decoration-none fw-bold">
            Nossa História <i class="bi bi-arrow-right"></i>
            </a>
        </p>
        <p class="small text-white-50 mb-0">
          <i class="bi bi-geo-alt-fill me-1 text-light"></i> <?= EMP_ENDERECO ?>
        </p>
      </div>

      <div class="col-12 col-md-4">
        <h6 class="text-uppercase mb-3">Contato</h6>
        <p class="mb-2 small">
          <i class="bi bi-whatsapp me-1 text-light"></i>
          <a href="https://wa.me/<?= EMP_WHATSAPP ?>" class="text-white-50 text-decoration-none" target="_blank">
            <?= EMP_TELEFONE ?>
          </a>
        </p>
        <p class="mb-0 small">
          <i class="bi bi-instagram me-1 text-light"></i>
          <a href="https://www.instagram.com/linealabs.com.br/" class="text-white-50 text-decoration-none" target="_blank">
            @linealabs.com.br
          </a>
        </p>
      </div>

      <div class="col-12 col-md-4">
        <h6 class="text-uppercase mb-3">Transparência Fiscal</h6>
        <p class="small text-white-50 mb-1">
          <strong class="text-light">Razão Social:</strong> <?= EMP_RAZAO_SOCIAL ?>
        </p>
        <p class="small text-white-50 mb-1">
          <strong class="text-light">CNPJ:</strong> <?= EMP_CNPJ ?>
        </p>
        <p class="small text-white-50 mb-0">
          <strong class="text-light">IE:</strong> <?= EMP_IE ?>
        </p>
      </div>

    </div>
  </div>

  <div class="text-center py-3">
    <small class="text-white-50">
      © <?= date('Y') ?> <?= EMP_NOME_FANTASIA ?> — Todos os direitos reservados
    </small>
  </div>
</footer>
