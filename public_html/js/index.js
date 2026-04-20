window.carregarPagina = async function (
  pagina   = 1,
  busca    = '',
  categoria = '',
  precoMin = '',
  precoMax = ''
) {
  try {
    const params = new URLSearchParams();
    params.set('pagina', pagina);

    if (busca.trim()     !== '') params.set('busca',     busca.trim());
    if (categoria.trim() !== '') params.set('categoria', categoria.trim());
    if (precoMin.trim()  !== '') params.set('preco_min', precoMin.trim());
    if (precoMax.trim()  !== '') params.set('preco_max', precoMax.trim());

    const resposta = await fetch(`catalog/index.php?${params.toString()}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    if (!resposta.ok) {
      throw new Error(`Erro HTTP: ${resposta.status}`);
    }

    const html = await resposta.text();
    document.getElementById('catalogo-container').innerHTML = html;

    history.pushState({}, '', `?${params.toString()}#catalogo`);

    conectarFormularioBusca();
    iniciarAnimacoesCatalogo();

  } catch (erro) {
    console.error('Erro ao carregar catálogo:', erro);
  }
};

function buscarProdutos(event) {
  event.preventDefault();

  const busca     = document.getElementById('campo-busca')?.value     ?? '';
  const categoria = document.getElementById('campo-categoria')?.value  ?? '';
  const precoMin  = document.getElementById('campo-preco-min')?.value  ?? '';
  const precoMax  = document.getElementById('campo-preco-max')?.value  ?? '';

  window.carregarPagina(1, busca, categoria, precoMin, precoMax);
}

function conectarFormularioBusca() {
  const formBusca = document.getElementById('form-busca-catalogo');
  if (formBusca && !formBusca.dataset.listenerAttached) {
    formBusca.addEventListener('submit', buscarProdutos);
    formBusca.dataset.listenerAttached = 'true';
  }

  const btnLimpar = document.getElementById('btn-limpar-filtros');
  if (btnLimpar && !btnLimpar.dataset.listenerAttached) {
    btnLimpar.addEventListener('click', () => {
      window.carregarPagina(1, '', '', '', '');
    });
    btnLimpar.dataset.listenerAttached = 'true';
  }
}

function iniciarAnimacoesCatalogo() {
  const elementos = document.querySelectorAll('.animar-quando-aparecer');

  const observer = new IntersectionObserver((entries, observerRef) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('text-focus-in');
        observerRef.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  elementos.forEach(el => {
    if (!el.classList.contains('text-focus-in')) {
      observer.observe(el);
    }
  });
}

document.addEventListener('click', (e) => {
  const link = e.target.closest('.page-link[data-pagina]');
  if (!link) return;

  e.preventDefault();

  const item = link.closest('.page-item');
  if (item && item.classList.contains('disabled')) return;

  const pagina    = parseInt(link.dataset.pagina, 10) || 1;
  const busca     = link.dataset.busca     ?? '';
  const categoria = link.dataset.categoria ?? '';
  const precoMin  = link.dataset.precoMin  ?? '';
  const precoMax  = link.dataset.precoMax  ?? '';

  window.carregarPagina(pagina, busca, categoria, precoMin, precoMax);
});

window.addEventListener('popstate', () => {
  const params = new URLSearchParams(window.location.search);

  const pagina    = parseInt(params.get('pagina'))    || 1;
  const busca     = params.get('busca')     ?? '';
  const categoria = params.get('categoria') ?? '';
  const precoMin  = params.get('preco_min') ?? '';
  const precoMax  = params.get('preco_max') ?? '';

  window.carregarPagina(pagina, busca, categoria, precoMin, precoMax);
});

// Detectar se é dispositivo móvel
function isMobileDevice() {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
         window.innerWidth <= 768;
}

// Otimizar experiência de hover para dispositivos móveis
function otimizarHoverParaMobile() {
  if (isMobileDevice()) {
    const cardImgContainers = document.querySelectorAll('.card-img-container');

    cardImgContainers.forEach(container => {
      // Remover hover effects em mobile para melhor performance
      container.style.cursor = 'pointer';

      // Adicionar evento de toque para expandir imagem
      container.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const img = this.querySelector('.produto-card-img');
        if (img) {
          img.classList.toggle('mobile-expanded');

          // Fechar outras imagens expandidas
          cardImgContainers.forEach(otherContainer => {
            if (otherContainer !== container) {
              const otherImg = otherContainer.querySelector('.produto-card-img');
              if (otherImg) {
                otherImg.classList.remove('mobile-expanded');
              }
            }
          });
        }
      });
    });

    // Fechar imagem expandida ao tocar fora
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.card-img-container')) {
        const expandedImgs = document.querySelectorAll('.produto-card-img.mobile-expanded');
        expandedImgs.forEach(img => {
          img.classList.remove('mobile-expanded');
        });
      }
    });
  }
}

// Otimizar carregamento de imagens para mobile
function otimizarCarregamentoImagens() {
  if (isMobileDevice()) {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');

    // Priorizar imagens visíveis
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          observer.unobserve(img);
        }
      });
    }, { rootMargin: '50px' });

    lazyImages.forEach(img => observer.observe(img));
  }
}

// Ajustar formulário para mobile
function ajustarFormularioMobile() {
  if (isMobileDevice()) {
    const formBusca = document.getElementById('form-busca-catalogo');
    if (formBusca) {
      // Adicionar atributo para melhor UX em mobile
      formBusca.setAttribute('autocomplete', 'off');

      // Focar no campo de busca quando o formulário for tocado
      const campoBusca = document.getElementById('campo-busca');
      if (campoBusca) {
        campoBusca.addEventListener('focus', function() {
          setTimeout(() => {
            window.scrollTo({
              top: formBusca.getBoundingClientRect().top + window.pageYOffset - 100,
              behavior: 'smooth'
            });
          }, 300);
        });
      }
    }
  }
}

// Prevenir zoom em inputs numéricos no iOS
function prevenirZoomIOS() {
  if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.style.fontSize = '16px'; // Prevenir zoom no iOS
      });

      input.addEventListener('blur', function() {
        this.style.fontSize = '';
      });
    });
  }
}

// Inicializar todas as otimizações
function inicializarOtimizacoesResponsivas() {
  otimizarHoverParaMobile();
  otimizarCarregamentoImagens();
  ajustarFormularioMobile();
  prevenirZoomIOS();

  // Ajustar altura do viewport para mobile
  if (isMobileDevice()) {
    const setVH = () => {
      const vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty('--vh', `${vh}px`);
    };

    setVH();
    window.addEventListener('resize', setVH);
    window.addEventListener('orientationchange', setVH);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  conectarFormularioBusca();
  iniciarAnimacoesCatalogo();
  inicializarOtimizacoesResponsivas();
});

// Re-inicializar otimizações quando o catálogo for atualizado via AJAX
window.carregarPagina = async function (
  pagina   = 1,
  busca    = '',
  categoria = '',
  precoMin = '',
  precoMax = ''
) {
  try {
    const params = new URLSearchParams();
    params.set('pagina', pagina);

    if (busca.trim()     !== '') params.set('busca',     busca.trim());
    if (categoria.trim() !== '') params.set('categoria', categoria.trim());
    if (precoMin.trim()  !== '') params.set('preco_min', precoMin.trim());
    if (precoMax.trim()  !== '') params.set('preco_max', precoMax.trim());

    const resposta = await fetch(`catalog/index.php?${params.toString()}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    if (!resposta.ok) {
      throw new Error(`Erro HTTP: ${resposta.status}`);
    }

    const html = await resposta.text();
    document.getElementById('catalogo-container').innerHTML = html;

    history.pushState({}, '', `?${params.toString()}#catalogo`);

    conectarFormularioBusca();
    iniciarAnimacoesCatalogo();
    inicializarOtimizacoesResponsivas(); // Re-inicializar otimizações

  } catch (erro) {
    console.error('Erro ao carregar catálogo:', erro);
  }
};
