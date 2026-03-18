<!DOCTYPE html>
<html lang="pt-BR">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Linea Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_login.css">
    </head>
<body class="admin-login-body">
  <main class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
    <div class="login-card">
      <div class="text-center mb-4">
        <h1 class="logo mb-2">Linea Labs</h1>
        <h2 class="h5">Acesso administrativo</h2>
        <p class="text-muted small mb-0">Área restrita para gerenciamento</p>
      </div>

      <form>
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" class="form-control" placeholder="admin@linealabs.com">
        </div>

        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input type="password" class="form-control" placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-dark w-100">Entrar</button>
      </form>

      <div class="text-center mt-3">
        <a href="index.php" class="small text-decoration-none">Voltar ao site</a>
      </div>
    </div>
  </main>
</body>