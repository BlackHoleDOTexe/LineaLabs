<?php
/**
 * ConfigRepository
 *
 * Centraliza todo o acesso ao banco para a tabela `configuracoes`.
 */
class ConfigRepository
{
    private array $defaults = [
        'razao_social'         => '',
        'nome_fantasia'        => 'Linea Labs',
        'inscricao_estadual'   => '',
        'custo_material_cm2'   => '0.0500',
        'custo_minuto_maquina' => '0.5000',
        'markup_padrao'        => '3.00',
    ];

    public function __construct(private PDO $pdo) {}

    /** Retorna todas as configurações como array chave → valor. */
    public function findAll(): array
    {
        $rows = $this->pdo->query("SELECT chave, valor FROM configuracoes")->fetchAll();

        $config = [];
        foreach ($rows as $row) {
            $config[$row['chave']] = $row['valor'];
        }

        return $config + $this->defaults;
    }

    /** Persiste uma chave (INSERT ou UPDATE). */
    public function save(string $key, string $value): void
    {
        $this->pdo->prepare(
            "INSERT INTO configuracoes (chave, valor)
             VALUES (:chave, :valor)
             ON DUPLICATE KEY UPDATE valor = :valor2"
        )->execute([':chave' => $key, ':valor' => $value, ':valor2' => $value]);
    }

    /** Persiste múltiplas chaves de uma vez. */
    public function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->save($key, $value);
        }
    }
}
