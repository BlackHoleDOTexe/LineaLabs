window.carregarPagina = async function (pagina = 1, busca = '') {
  try {
    const params = new URLSearchParams();
    params.set('pagina', pagina);

    if (busca.trim() !== '') {
      params.set('busca', busca);
    }

    const resposta = await fetch(`catalogo.php?${params.toString()}`, {
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
    iniciarAnimacoesCatalogo();

  } catch (erro) {
    console.error('Erro ao carregar catálogo:', erro);
  }
};

function buscarProdutos(event) {
  event.preventDefault();

  const campoBusca = document.getElementById('campo-busca');
  const busca = campoBusca ? campoBusca.value.trim() : '';

  window.carregarPagina(1, busca);
}

function conectarFormularioBusca() {
  const formBusca = document.getElementById('form-busca-catalogo');

  if (formBusca && !formBusca.dataset.listenerAttached) {
    formBusca.addEventListener('submit', buscarProdutos);
    formBusca.dataset.listenerAttached = 'true';
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
  }, {
    threshold: 0.2
  });

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

  const pagina = parseInt(link.dataset.pagina, 10) || 1;
  const busca = link.dataset.busca || '';

  window.carregarPagina(pagina, busca);
});

window.addEventListener('popstate', () => {
  const params = new URLSearchParams(window.location.search);
  const pagina = parseInt(params.get('pagina')) || 1;
  const busca = params.get('busca') || '';

  window.carregarPagina(pagina, busca);
});

document.addEventListener('DOMContentLoaded', () => {
  conectarFormularioBusca();
  iniciarAnimacoesCatalogo();
});