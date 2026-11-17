-- ============================================================
-- sistema_chamados_2 — schema novo (04/11/2026)
-- Diferenças em relação ao antigo:
--   1. chamados.localizacao_id INT (FK) no lugar de localizacao VARCHAR
--   2. idx_responsavel_status em chamados
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- localizacoes (domínio de áreas físicas: CPD, Arquivo, Recepção, ...)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `localizacoes`;
CREATE TABLE `localizacoes` (
    `id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`  VARCHAR(150) NOT NULL,
    `ativo` TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_localizacoes_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- usuarios
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`           VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL,
    `senha_hash`     VARCHAR(255) NOT NULL,
    `perfil`         ENUM('solicitante','tecnico','admin','painel') NOT NULL DEFAULT 'solicitante',
    `trocar_senha`   TINYINT(1)   NOT NULL DEFAULT 0,
    `pediu_senha_em` DATETIME     DEFAULT NULL,
    `ativo`          TINYINT(1)   NOT NULL DEFAULT 1,
    `criado_em`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_usuarios_email` (`email`),
    KEY `idx_usuarios_perfil_ativo` (`perfil`, `ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- sla_prazos (domínio de prazos por prioridade)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `sla_prazos`;
CREATE TABLE `sla_prazos` (
    `prioridade`  ENUM('baixa','media','alta','urgente') NOT NULL,
    `prazo_horas` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`prioridade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sla_prazos` (`prioridade`, `prazo_horas`) VALUES
    ('baixa',   24),
    ('media',   12),
    ('alta',     8),
    ('urgente',  4);

-- ------------------------------------------------------------
-- chamados
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `chamados`;
CREATE TABLE `chamados` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titulo`          VARCHAR(150) NOT NULL,
    `descricao`       MEDIUMTEXT   NOT NULL,
    `prioridade`      ENUM('baixa','media','alta','urgente') NOT NULL DEFAULT 'media',
    `status`          ENUM('aberto','em_andamento','resolvido','fechado') NOT NULL DEFAULT 'aberto',
    `solicitante_id`  INT UNSIGNED NOT NULL,
    `responsavel_id`  INT UNSIGNED DEFAULT NULL,
    `localizacao_id`  INT UNSIGNED DEFAULT NULL,
    `criado_em`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `fechado_em`      DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_chamados_status_criado`         (`status`, `criado_em`),
    KEY `idx_chamados_solicitante_criado`    (`solicitante_id`, `criado_em`),
    KEY `idx_chamados_responsavel_status`    (`responsavel_id`, `status`),
    KEY `idx_chamados_localizacao`           (`localizacao_id`),
    CONSTRAINT `fk_chamados_solicitante`
        FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_chamados_responsavel`
        FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_chamados_localizacao`
        FOREIGN KEY (`localizacao_id`) REFERENCES `localizacoes` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- comentarios
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `comentarios`;
CREATE TABLE `comentarios` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `chamado_id`  INT UNSIGNED NOT NULL,
    `usuario_id`  INT UNSIGNED NOT NULL,
    `texto`       TEXT NOT NULL,
    `criado_em`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_comentarios_chamado_criado` (`chamado_id`, `criado_em`),
    CONSTRAINT `fk_comentarios_chamado`
        FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_comentarios_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- historico (log de mudanças, texto livre)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `historico`;
CREATE TABLE `historico` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `chamado_id`  INT UNSIGNED NOT NULL,
    `usuario_id`  INT UNSIGNED DEFAULT NULL,
    `texto`       TEXT NOT NULL,
    `criado_em`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_historico_chamado_criado` (`chamado_id`, `criado_em`),
    CONSTRAINT `fk_historico_chamado`
        FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_historico_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- anexos (MEDIUMBLOB)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `anexos`;
CREATE TABLE `anexos` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `chamado_id`    INT UNSIGNED NOT NULL,
    `usuario_id`    INT UNSIGNED NOT NULL,
    `nome_original` VARCHAR(255) NOT NULL,
    `mime`          VARCHAR(100) NOT NULL,
    `tamanho`       INT UNSIGNED NOT NULL,
    `conteudo`      MEDIUMBLOB NOT NULL,
    `criado_em`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_anexos_chamado` (`chamado_id`),
    CONSTRAINT `fk_anexos_chamado`
        FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_anexos_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- relatorios (encerramento; 1 por chamado)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `relatorios`;
CREATE TABLE `relatorios` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `chamado_id`   INT UNSIGNED NOT NULL,
    `usuario_id`   INT UNSIGNED NOT NULL,
    `localizacao_id` INT UNSIGNED DEFAULT NULL,
    `tipo`         VARCHAR(100) NOT NULL,
    `equipamento`  VARCHAR(100) DEFAULT NULL,
    `tempo_min`    INT UNSIGNED NOT NULL DEFAULT 0,
    `solucao`      TEXT NOT NULL,
    `criado_em`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_relatorios_chamado` (`chamado_id`),
    CONSTRAINT `fk_relatorios_chamado`
        FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_relatorios_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_relatorios_localizacao`
        FOREIGN KEY (`localizacao_id`) REFERENCES `localizacoes` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- sessoes_lembradas (cookie selector+validator)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `sessoes_lembradas`;
CREATE TABLE `sessoes_lembradas` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`     INT UNSIGNED NOT NULL,
    `seletor`        CHAR(32) NOT NULL,
    `validador_hash` CHAR(64) NOT NULL,
    `expira_em`      DATETIME NOT NULL,
    `criado_em`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_sessoes_lembradas_seletor` (`seletor`),
    KEY `idx_sessoes_lembradas_usuario` (`usuario_id`),
    CONSTRAINT `fk_sessoes_lembradas_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- tentativas_login (rate limiting; marcadores especiais)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `tentativas_login`;
CREATE TABLE `tentativas_login` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`       VARCHAR(150) NOT NULL,
    `ip`          VARCHAR(45) DEFAULT NULL,
    `sucesso`     TINYINT(1) NOT NULL DEFAULT 0,
    `criado_em`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tentativas_email_criado` (`email`, `criado_em`),
    KEY `idx_tentativas_ip_criado`    (`ip`, `criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;