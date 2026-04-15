<?php
/**
 * ImageRepository
 *
 * Centraliza todo o acesso ao banco para a tabela `produto_imagens`.
 */
class ImageRepository
{
    public function __construct(private PDO $pdo) {}

    /** Retorna todas as imagens agrupadas por produto_id. */
    public function groupByProduct(): array
    {
        $rows   = $this->pdo->query(
            "SELECT * FROM produto_imagens ORDER BY produto_id, ordem, id"
        )->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['produto_id']][] = $row;
        }
        return $grouped;
    }

    /** Retorna as imagens de um produto específico, ordenadas. */
    public function findByProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM produto_imagens
             WHERE produto_id = :pid
             ORDER BY ordem, id"
        );
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll();
    }

    /** Busca uma imagem por ID e produto_id (garante ownership). */
    public function findByIdAndProduct(int $id, int $productId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM produto_imagens
             WHERE id = :id AND produto_id = :pid
             LIMIT 1"
        );
        $stmt->execute([':id' => $id, ':pid' => $productId]);
        return $stmt->fetch() ?: null;
    }

    /** Retorna todos os nomes de arquivo de imagens de um produto. */
    public function getFilesByProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT arquivo FROM produto_imagens WHERE produto_id = :pid"
        );
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Próxima ordem disponível para um produto. */
    public function nextOrder(int $productId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(MAX(ordem), 0) + 1
             FROM produto_imagens
             WHERE produto_id = :pid"
        );
        $stmt->execute([':pid' => $productId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(int $productId, string $arquivo, int $ordem): void
    {
        $this->pdo->prepare(
            "INSERT INTO produto_imagens (produto_id, arquivo, ordem)
             VALUES (:pid, :arquivo, :ordem)"
        )->execute([':pid' => $productId, ':arquivo' => $arquivo, ':ordem' => $ordem]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare("DELETE FROM produto_imagens WHERE id = :id")
                  ->execute([':id' => $id]);
    }

    public function deleteByProduct(int $productId): void
    {
        $this->pdo->prepare("DELETE FROM produto_imagens WHERE produto_id = :pid")
                  ->execute([':pid' => $productId]);
    }
}
