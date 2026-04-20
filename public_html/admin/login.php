<?php
require_once dirname(__DIR__, 2) . '/private/config.php';
require_once dirname(__DIR__)    . '/app/Service/Auth.php';

redirecionarSeLogado();

$erro = '';

// Configurações de rate limiting
define('LOGIN_MAX_ATTEMPTS', 5); // Máximo de tentativas
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos em segundos
define('LOGIN_ATTEMPT_WINDOW', 300); // 5 minutos em segundos para contagem de tentativas

// Inicializar sessão de rate limiting se não existir
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [
        'count' => 0,
        'first_attempt' => null,
        'locked_until' => null
    ];
}

// Verificar se está bloqueado por rate limiting
if ($_SESSION['login_attempts']['locked_until'] && time() < $_SESSION['login_attempts']['locked_until']) {
    $remaining = ceil(($_SESSION['login_attempts']['locked_until'] - time()) / 60);
    $erro = "Muitas tentativas de login. Tente novamente em {$remaining} minutos.";
} elseif ($_SESSION['login_attempts']['locked_until'] && time() >= $_SESSION['login_attempts']['locked_until']) {
    // Resetar bloqueio se o tempo expirou
    $_SESSION['login_attempts'] = [
        'count' => 0,
        'first_attempt' => null,
        'locked_until' => null
    ];
}

if (isset($_GET['timeout']) && $_GET['timeout'] === '1') {
    $erro = 'Sua sessão expirou por inatividade. Faça login novamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    // Verificar rate limiting antes de processar
    if ($_SESSION['login_attempts']['locked_until'] && time() < $_SESSION['login_attempts']['locked_until']) {
        $remaining = ceil(($_SESSION['login_attempts']['locked_until'] - time()) / 60);
        $erro = "Muitas tentativas de login. Tente novamente em {$remaining} minutos.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if ($email === '' || $senha === '') {
            $erro = 'Preencha e-mail e senha.';
        } else {
            // Gerenciar contagem de tentativas
            $now = time();

            // Resetar contagem se a janela de tempo expirou
            if ($_SESSION['login_attempts']['first_attempt'] &&
                ($now - $_SESSION['login_attempts']['first_attempt']) > LOGIN_ATTEMPT_WINDOW) {
                $_SESSION['login_attempts']['count'] = 0;
                $_SESSION['login_attempts']['first_attempt'] = $now;
            }

            // Registrar primeira tentativa se for a primeira
            if ($_SESSION['login_attempts']['count'] === 0) {
                $_SESSION['login_attempts']['first_attempt'] = $now;
            }

            // Incrementar contador de tentativas
            $_SESSION['login_attempts']['count']++;

            $sql  = "SELECT id, nome, email, senha FROM admins WHERE email = :email LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);

            $admin      = $stmt->fetch();
            $hash       = $admin['senha'] ?? '$2y$12$invaliddummyhashfortimingxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
            $valido     = password_verify($senha, $hash);

            if ($admin && $valido) {
                // Login bem-sucedido - resetar contador
                $_SESSION['login_attempts'] = [
                    'count' => 0,
                    'first_attempt' => null,
                    'locked_until' => null
                ];

                session_regenerate_id(true);

                $_SESSION['admin_id']      = $admin['id'];
                $_SESSION['admin_nome']    = $admin['nome'];
                $_SESSION['admin_email']   = $admin['email'];
                $_SESSION['ultimo_acesso'] = time();

                header('Location: /admin/index.php');
                exit;
            } else {
                // Login falhou - verificar se excedeu limite
                if ($_SESSION['login_attempts']['count'] >= LOGIN_MAX_ATTEMPTS) {
                    $_SESSION['login_attempts']['locked_until'] = $now + LOGIN_LOCKOUT_TIME;
                    $remaining = ceil(LOGIN_LOCKOUT_TIME / 60);
                    $erro = "Muitas tentativas de login falhas. Conta bloqueada por {$remaining} minutos.";
                } else {
                    $remaining = LOGIN_MAX_ATTEMPTS - $_SESSION['login_attempts']['count'];
                    $erro = "E-mail ou senha inválidos. Você tem {$remaining} tentativa(s) restante(s).";
                }
            }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/admin_login.css?v=<?= APP_version ?>">
</head>
<body class="admin-login-body">
  <main class="container-fluid d-flex align-items-center justify-content-center p-3">
    <div class="login-card">
      <div class="text-center mb-4">
        <h1 class="logo mb-2">Linea Labs</h1>
        <h2 class="h5 mb-1">Acesso Administrativo</h2>
        <p class="text-muted small">Área restrita para gerenciamento</p>
      </div>

      <?php if ($erro !== ''): ?>
        <div class="alert alert-danger mb-4">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0">
              <i class="bi bi-envelope text-muted"></i>
            </span>
            <input type="email" class="form-control border-start-0" name="email"
                   placeholder="admin@linealabs.com"
                   value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Senha</label>
          <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0">
              <i class="bi bi-lock text-muted"></i>
            </span>
            <input type="password" class="form-control border-start-0" name="senha"
                   placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn btn-dark w-100 py-2">
          <i class="bi bi-box-arrow-in-right me-2"></i>Entrar no Painel
        </button>
      </form>

      <div class="text-center pt-3 border-top">
        <a href="/index.php" class="small text-decoration-none">
          <i class="bi bi-arrow-left me-1"></i>Voltar ao site principal
        </a>
      </div>
    </div>
  </main>
</body>
</html>
