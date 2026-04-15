<?php
/**
 * ProductRepository
 *
 * Centraliza todo o acesso ao banco para a tabela `produtos`.
 * Nenhum SQL de produto deve existir fora desta classe.
 */
class ProductRepository
{
    public function __construct(private PDO $pdo) {}

    // ----------------------------------------------------------------
    // Catálogo público (sempre ativo = 1)
    // ----------------------------------------------------------------

    public function findPublic(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildPublicWhere($filters);

        $sql  = "SELECT * FROM produtos {$where} ORDER BY id DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countPublic(array $filters): int
    {
        [$where, $params] = $this->buildPublicWhere($filters);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM produtos {$where}");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getCategories(): array
    {
        return $this->pdo->query(
            "SELECT DISTINCT categoria FROM produtos
             WHERE ativo = 1 AND categoria IS NOT NULL AND categoria <> ''
             ORDER BY categoria"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    // ----------------------------------------------------------------
    // CRUD (painel admin)
    // ----------------------------------------------------------------

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO produtos (nome, descricao, dimensoes, preco, categoria, ativo)
             VALUES (:nome, :descricao, :dimensoes, :preco, :categoria, :ativo)"
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE produtos
             SET nome = :nome, descricao = :descricao, dimensoes = :dimensoes,
                 preco = :preco, categoria = :categoria, ativo = :ativo
             WHERE id = :id"
        );
        $stmt->execute([...$data, ':id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare("DELETE FROM produtos WHERE id = :id")
                  ->execute([':id' => $id]);
    }

    public function toggleStatus(int $id): void
    {
        $this->pdo->prepare(
            "UPDATE produtos
             SET ativo = CASE WHEN ativo = 1 THEN 0 ELSE 1 END
             WHERE id = :id"
        )->execute([':id' => $id]);
    }

    public function getAllCategories(): array
    {
        return $this->pdo->query(
            "SELECT DISTINCT categoria FROM produtos
             WHERE categoria IS NOT NULL AND categoria <> ''
             ORDER BY categoria"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    // ----------------------------------------------------------------
    // Construção do WHERE — catálogo público
    // ----------------------------------------------------------------

    private function buildPublicWhere(array $filters): array
    {
        $conditions = ['ativo = 1'];
        $params     = [];

        if (!empty($filters['busca'])) {
            $busca    = mb_substr(trim($filters['busca']), 0, 150);
            $palavras = preg_split('/\s+/', $busca, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($palavras as $i => $palavra) {
                $escaped        = addcslashes($palavra, '%_\\');
                $conditions[]   = "(nome LIKE :bN{$i} OR COALESCE(descricao,'') LIKE :bD{$i})";
                $params[":bN{$i}"] = '%' . $escaped . '%';
                $params[":bD{$i}"] = '%' . $escaped . '%';
            }
        }

        if (!empty($filters['categoria'])) {
            $conditions[]      = 'categoria = :categoria';
            $params[':categoria'] = $filters['categoria'];
        }

        if (isset($filters['preco_min'])
            && is_numeric($filters['preco_min'])
            && (float) $filters['preco_min'] >= 0
        ) {
            $conditions[]        = 'preco >= :preco_min';
            $params[':preco_min'] = (float) $filters['preco_min'];
        }

        if (isset($filters['preco_max'])
            && is_numeric($filters['preco_max'])
            && (float) $filters['preco_max'] >= 0
        ) {
            $conditions[]        = 'preco <= :preco_max';
            $params[':preco_max'] = (float) $filters['preco_max'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }
}
