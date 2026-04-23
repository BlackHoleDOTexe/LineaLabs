<?php
/**
 * Seeder de orçamentos (dados de teste).
 *
 * Uso:
 *   php db/seed_orcamentos.php [quantidade]
 *
 * Acrescenta N orçamentos à tabela `orcamentos` (padrão: 30).
 * Não apaga registros existentes.
 */

require __DIR__ . '/../private/config.php';
require __DIR__ . '/../public_html/app/Repository/QuoteRepository.php';

$qtd = isset($argv[1]) ? max(1, (int) $argv[1]) : 30;

$clientes = [
    'Marcos Silva', 'Ana Paula Ferreira', 'João Pedro Souza', 'Carla Mendes',
    'Ricardo Oliveira', 'Fernanda Lima', 'Bruno Carvalho', 'Juliana Alves',
    'Rafael Nunes', 'Patrícia Rocha', 'Eduardo Martins', 'Larissa Costa',
    'Gustavo Pereira', 'Camila Ribeiro', 'Thiago Barbosa', 'Mariana Castro',
    'Felipe Ramos', 'Beatriz Cardoso', 'Lucas Moreira', 'Isabela Gomes',
    'Pedro Henrique Dias', 'Aline Fernandes', 'Diego Araújo', 'Natália Teixeira',
    'Vinícius Correia', 'Sabrina Pinto', 'André Monteiro', 'Cláudia Batista',
    'Rodrigo Azevedo', 'Helena Freitas',
];

$pecas = [
    'Placa de MDF com gravação a laser',
    'Letreiro em acrílico recortado',
    'Chaveiro personalizado em madeira',
    'Troféu em acrílico transparente',
    'Luminária decorativa em MDF',
    'Porta-copos gravados (kit 6un)',
    'Quadro decorativo com corte vazado',
    'Placa de homenagem em acrílico',
    'Caixa de presente em MDF',
    'Convite de casamento em papel recortado',
    'Suporte para celular em acrílico',
    'Tag para loja em MDF',
    'Topo de bolo personalizado',
    'Organizador de mesa em MDF',
    'Expositor de joias em acrílico',
];

$repo = new QuoteRepository($pdo);

$inseridos = 0;
$pdo->beginTransaction();

try {
    for ($i = 0; $i < $qtd; $i++) {
        $largura = mt_rand(50, 1000) / 10;   // 5.0 .. 100.0 cm
        $altura  = mt_rand(50, 1000) / 10;
        $area    = round($largura * $altura, 2);

        // tempo proporcional à área, com ruído
        $tempo = round(($area / 50) + mt_rand(1, 30), 2);

        $custoMat = mt_rand(5, 80) / 1000;   // R$/cm²  (0.005 .. 0.080)
        $custoMaq = mt_rand(50, 200) / 100;  // R$/min  (0.50 .. 2.00)
        $markup   = mt_rand(150, 350) / 100; // 1.50 .. 3.50

        $preco = round((($area * $custoMat) + ($tempo * $custoMaq)) * $markup, 2);

        $repo->create([
            ':nome_cliente'   => $clientes[array_rand($clientes)],
            ':descricao_peca' => $pecas[array_rand($pecas)],
            ':largura'        => $largura,
            ':altura'         => $altura,
            ':area'           => $area,
            ':tempo'          => $tempo,
            ':custo_mat'      => $custoMat,
            ':custo_maq'      => $custoMaq,
            ':markup'         => $markup,
            ':preco'          => $preco,
        ]);

        $inseridos++;
    }

    $pdo->commit();
    echo "OK: {$inseridos} orçamentos inseridos.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "ERRO: {$e->getMessage()}\n");
    exit(1);
}
