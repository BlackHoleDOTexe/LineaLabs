<?php
require_once dirname(__DIR__, 2) . "/private/config.php";
require_once __DIR__ . "/auth.php";

exigirLogin();

$busca = trim($_GET["busca"] ?? "");

$where = "";
$params = [];

if ($busca !== "") {
    $palavras = preg_split("/\s+/", $busca);
    $condicoesBusca = [];

    foreach ($palavras as $index => $palavra) {
        $palavra = trim($palavra);

        if ($palavra === "") {
            continue;
        }

        $paramNome = ":busca_nome{$index}";
        $paramDesc = ":busca_desc{$index}";

        $condicoesBusca[] = "(
            nome LIKE {$paramNome}
            OR COALESCE(descricao, '') LIKE {$paramDesc}
        )";

        $params[$paramNome] = "%" . $palavra . "%";
        $params[$paramDesc] = "%" . $palavra . "%";
    }

    if (!empty($condicoesBusca)) {
        $where = "WHERE " . implode(" AND ", $condicoesBusca);
    }
}

$sql = "SELECT id, nome, preco, ativo
        FROM produtos
        {$where}
        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

foreach ($params as $chave => $valor) {
    $stmt->bindValue($chave, $valor, PDO::PARAM_STR);
}

$stmt->execute();
$produtos = $stmt->fetchAll();
?>

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
                <a class="nav-link text-danger" href="logout.php">Sair</a>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Dashboard</h1>
                    <p class="text-muted mb-0">Visão geral do painel administrativo</p>
                </div>
            </div>

            <form method="GET" class="row g-2 mb-3">
    <div class="col-12 col-md-6 admin-buscar">
        <input
            type="text"
            name="busca"
            class="form-control"
            placeholder="Buscar produto por nome ou descrição..."
            value="<?= htmlspecialchars($busca) ?>"
        >
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Buscar</button>
    </div>

    <?php if ($busca !== ""): ?>
        <div class="col-auto">
            <a href="admin-dashboard.php" class="btn btn-outline-secondary">Limpar</a>
        </div>
    <?php endif; ?>
</form>

            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Produtos recentes</h2>
                    <a href="product-create.php" class="btn btn-dark btn-sm">Novo produto</a>
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
                            <?php if (empty($produtos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nenhum produto cadastrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(
                                            $produto["nome"]
                                        ) ?></td>
                                        <td>R$ <?= number_format(
                                            (float) $produto["preco"],
                                            2,
                                            ",",
                                            "."
                                        ) ?></td>
                                        <td>
                                            <?php if (
                                                (int) $produto["ativo"] === 1
                                            ): ?>
                                                <span class="badge text-bg-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="product-edit.php?id=<?= $produto[
                                                "id"
                                            ] ?>" class="btn btn-outline-dark btn-sm">
                                                Editar
                                            </a>

                                            <a
                                                href="product-toggle.php?id=<?= $produto[
                                                    "id"
                                                ] ?>"
                                                class="btn btn-outline-warning btn-sm"
                                                onclick="return confirm('Deseja alterar o status deste produto?');"
                                            >
                                                <?= (int) $produto["ativo"] ===
                                                1
                                                    ? "Desativar"
                                                    : "Ativar" ?>
                                            </a>

                                            <a
                                                href="product-delete.php?id=<?= $produto[
                                                    "id"
                                                ] ?>"
                                                class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Deseja excluir este produto permanentemente?');"
                                            >
                                                Excluir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>