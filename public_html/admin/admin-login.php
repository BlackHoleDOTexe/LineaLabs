
<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once __DIR__ . '/auth.php';

redirecionarSeLogado();

$erro = '';

if (isset($_GET['timeout']) && $_GET['timeout'] === '1') {
    $erro = 'Sua sessão expirou por inatividade. Faça login novamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $sql = "SELECT id, nome, email, senha FROM admins WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        $admin = $stmt->fetch();
        $hashVerificar = $admin['senha'] ?? '$2y$12$invaliddummyhashfortimingxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $credenciaisValidas = password_verify($senha, $hashVerificar);

        if ($admin && $credenciaisValidas) {
            session_regenerate_id(true);

            $_SESSION['admin_id']      = $admin['id'];
            $_SESSION['admin_nome']    = $admin['nome'];
            $_SESSION['admin_email']   = $admin['email'];
            $_SESSION['ultimo_acesso'] = time();

            header('Location: admin-dashboard.php');
            exit;
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Linea Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_login.css?v=<?= APP_version ?>">
    </head>
<body class="admin-login-body">
  <main class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
    <div class="login-card">
      <div class="text-center mb-4">
        <h1 class="logo mb-2">Linea Labs</h1>
        <h2 class="h5">Acesso administrativo</h2>
        <p class="text-muted small mb-0">Área restrita para gerenciamento</p>
      </div>

        <?php if ($erro !== ''): ?>
          <div class="alert alert-danger">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" class="form-control" placeholder="admin@linealabs.com" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input type="password" class="form-control" placeholder="••••••••" name="senha" required>
        </div>

        <button type="submit" class="btn btn-dark w-100">Entrar</button>
      </form>

      <div class="text-center mt-3">
        <a href="index.php" class="small text-decoration-none">Voltar ao site</a>
      </div>
    </div>
  </main>
</body>