<!DOCTYPE html>
<html lang="pt-BR">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Linea Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    </head>

<body class="admin-body">
  <div class="admin-wrapper">
    
    <aside class="admin-sidebar">
      <h2 class="logo mb-4">Linea Labs</h2>

      <nav class="nav flex-column gap-2">
        <a class="nav-link active" href="#">Dashboard</a>
        <a class="nav-link" href="#">Produtos</a>
        <a class="nav-link" href="#">Pedidos</a>
        <a class="nav-link" href="#">Categorias</a>
        <a class="nav-link" href="#">Configurações</a>
        <a class="nav-link text-danger" href="#">Sair</a>
      </nav>
    </aside>

    <main class="admin-content">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 mb-1">Dashboard</h1>
          <p class="text-muted mb-0">Visão geral do painel administrativo</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
          <div class="admin-card">
            <p class="text-muted mb-1">Produtos</p>
            <h3 class="mb-0">48</h3>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="admin-card">
            <p class="text-muted mb-1">Pedidos</p>
            <h3 class="mb-0">12</h3>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="admin-card">
            <p class="text-muted mb-1">Em destaque</p>
            <h3 class="mb-0">8</h3>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="admin-card">
            <p class="text-muted mb-1">Faturamento</p>
            <h3 class="mb-0">R$ 2.450</h3>
          </div>
        </div>
      </div>

      <div class="admin-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h5 mb-0">Produtos recentes</h2>
          <button class="btn btn-dark btn-sm">Novo produto</button>
        </div>

        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Produto Exemplo</td>
                <td>R$ 49,90</td>
                <td><span class="badge text-bg-success">Ativo</span></td>
                <td class="text-end">
                  <button class="btn btn-outline-dark btn-sm">Editar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

  </div>
</body>