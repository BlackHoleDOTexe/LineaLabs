-- =============================================================
-- Migração: Refatoração do Painel Administrativo - Linea Labs
-- Data: 2026-04-12
-- =============================================================

-- 1. Adicionar coluna 'dimensoes' à tabela produtos
ALTER TABLE `produtos`
  ADD COLUMN `dimensoes` VARCHAR(255) NULL
  COMMENT 'Dimensões da peça (ex: 30cm x 21cm x 9mm)'
  AFTER `descricao`;

-- 2. Criar tabela de configurações globais
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id`            INT            NOT NULL AUTO_INCREMENT,
  `chave`         VARCHAR(100)   NOT NULL                   COMMENT 'Chave única da configuração',
  `valor`         TEXT                                       COMMENT 'Valor da configuração',
  `descricao`     VARCHAR(255)   NULL                        COMMENT 'Descrição legível da configuração',
  `atualizado_em` TIMESTAMP      NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Configurações globais do sistema';

-- 3. Inserir configurações padrão (sem sobrescrever se já existirem)
INSERT IGNORE INTO `configuracoes` (`chave`, `valor`, `descricao`) VALUES
  ('razao_social',         '66.043.362 EDUARDO FELIPE SCHMIDT DE GODOY', 'Razão Social da empresa'),
  ('nome_fantasia',        'Linea Labs',                                  'Nome Fantasia da empresa'),
  ('inscricao_estadual',   '91221792-20',                                 'Inscrição Estadual'),
  ('custo_material_cm2',   '0.0500',                                      'Custo do MDF por cm² (R$)'),
  ('custo_minuto_maquina', '0.5000',                                      'Custo por minuto de máquina laser (R$)'),
  ('markup_padrao',        '3.00',                                        'Markup padrão para precificação');

-- 4. Criar tabela de orçamentos salvos
CREATE TABLE IF NOT EXISTS `orcamentos` (
  `id`                   INT            NOT NULL AUTO_INCREMENT,
  `nome_cliente`         VARCHAR(150)   NULL                       COMMENT 'Nome do cliente (opcional)',
  `descricao_peca`       VARCHAR(255)   NULL                       COMMENT 'Descrição da peça orçada',
  `largura_cm`           DECIMAL(10,2)  NOT NULL DEFAULT '0.00'    COMMENT 'Largura em centímetros',
  `altura_cm`            DECIMAL(10,2)  NOT NULL DEFAULT '0.00'    COMMENT 'Altura em centímetros',
  `area_cm2`             DECIMAL(10,2)  NOT NULL DEFAULT '0.00'    COMMENT 'Área calculada em cm²',
  `tempo_maquina_min`    DECIMAL(10,2)  NOT NULL DEFAULT '0.00'    COMMENT 'Tempo estimado de máquina (minutos)',
  `custo_material_cm2`   DECIMAL(10,4)  NOT NULL DEFAULT '0.0000'  COMMENT 'Custo/cm² usado no cálculo',
  `custo_minuto_maquina` DECIMAL(10,4)  NOT NULL DEFAULT '0.0000'  COMMENT 'Custo/minuto usado no cálculo',
  `markup`               DECIMAL(10,2)  NOT NULL DEFAULT '1.00'    COMMENT 'Markup aplicado',
  `preco_calculado`      DECIMAL(10,2)  NOT NULL DEFAULT '0.00'    COMMENT 'Preço final calculado',
  `criado_em`            TIMESTAMP      NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Orçamentos gerados pela calculadora';
