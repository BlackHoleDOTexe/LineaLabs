
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function adminEstaLogado(): bool
{
    return isset($_SESSION['admin_id']);
}

function exigirLogin(): void
{
    if (!adminEstaLogado()) {
        header('Location: admin-login.php');
        exit;
    }
}

function redirecionarSeLogado(): void
{
    if (adminEstaLogado()) {
        header('Location: admin-dashboard.php');
        exit;
    }
}