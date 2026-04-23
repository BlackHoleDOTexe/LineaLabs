<?php
/**
 * QuoteRepository
 *
 * Centraliza todo o acesso ao banco para a tabela `orcamentos`.
 */
class QuoteRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orcamentos WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    public function findRecent(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM orcamentos ORDER BY criado_em DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findAll(): array
    {
        return $this->pdo->query("SELECT * FROM orcamentos ORDER BY criado_em DESC")->fetchAll();
    }

    public function create(array $data): void
    {
        $this->pdo->prepare(
            "INSERT INTO orcamentos
                (nome_cliente, descricao_peca, largura_cm, altura_cm, area_cm2,
                 tempo_maquina_min, custo_material_cm2, custo_minuto_maquina,
                 markup, preco_calculado)
             VALUES
                (:nome_cliente, :descricao_peca, :largura, :altura, :area,
                 :tempo, :custo_mat, :custo_maq, :markup, :preco)"
        )->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $this->pdo->prepare(
            "UPDATE orcamentos SET
                nome_cliente         = :nome_cliente,
                descricao_peca       = :descricao_peca,
                largura_cm           = :largura,
                altura_cm            = :altura,
                area_cm2             = :area,
                tempo_maquina_min    = :tempo,
                custo_material_cm2   = :custo_mat,
                custo_minuto_maquina = :custo_maq,
                markup               = :markup,
                preco_calculado      = :preco
             WHERE id = :id"
        )->execute([...$data, ':id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare("DELETE FROM orcamentos WHERE id = :id")
                  ->execute([':id' => $id]);
    }
}
