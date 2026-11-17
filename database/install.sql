-- SCRIPT DE INSTALAÇÃO - SISTEMA DE CHAMADOS SEAD
CREATE DATABASE IF NOT EXISTS sistema_chamados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_chamados;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS localizacoes;
CREATE TABLE localizacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(150) NOT NULL UNIQUE, ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    nome VARCHAR(150) NOT NULL, 
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL, 
    perfil ENUM('solicitante','tecnico','admin','painel') NOT NULL DEFAULT 'solicitante',
    trocar_senha TINYINT(1) NOT NULL DEFAULT 0, 
    pediu_senha_em DATETIME DEFAULT NULL, 
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    foto VARCHAR(255) DEFAULT NULL, 
    bio VARCHAR(500) DEFAULT NULL,
    KEY idx_usuarios_perfil_ativo (perfil, ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS sla_prazos;
CREATE TABLE sla_prazos (
    prioridade ENUM('baixa','media','alta','urgente') NOT NULL PRIMARY KEY, prazo_horas INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS chamados;
CREATE TABLE chamados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    titulo VARCHAR(150) NOT NULL, 
    descricao MEDIUMTEXT NOT NULL,
    prioridade ENUM('baixa','media','alta','urgente') NOT NULL DEFAULT 'media',
    status ENUM('aberto','em_andamento','resolvido','fechado') NOT NULL DEFAULT 'aberto',
    solicitante_id INT UNSIGNED NOT NULL, 
    responsavel_id INT UNSIGNED DEFAULT NULL, 
    localizacao_id INT UNSIGNED DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    assumido_em DATETIME DEFAULT NULL, 
    fechado_em DATETIME DEFAULT NULL,
    KEY idx_status_criado (status, criado_em), 
    KEY idx_solicitante (solicitante_id), 
    KEY idx_responsavel (responsavel_id),
    CONSTRAINT fk_chamados_solicitante FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_chamados_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_chamados_local FOREIGN KEY (localizacao_id) REFERENCES localizacoes(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS comentarios;
CREATE TABLE comentarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, chamado_id INT UNSIGNED NOT NULL, usuario_id INT UNSIGNED NOT NULL,
    texto TEXT NOT NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentarios_chamado FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS historico;
CREATE TABLE historico (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, chamado_id INT UNSIGNED NOT NULL, usuario_id INT UNSIGNED DEFAULT NULL,
    texto TEXT NOT NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historico_chamado FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    CONSTRAINT fk_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS anexos;
CREATE TABLE anexos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, chamado_id INT UNSIGNED NOT NULL, usuario_id INT UNSIGNED NOT NULL,
    nome_original VARCHAR(255) NOT NULL, mime VARCHAR(100) NOT NULL, tamanho INT UNSIGNED NOT NULL, conteudo MEDIUMBLOB NOT NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_anexos_chamado FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    CONSTRAINT fk_anexos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS relatorios;
CREATE TABLE relatorios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, chamado_id INT UNSIGNED NOT NULL UNIQUE, usuario_id INT UNSIGNED NOT NULL, localizacao_id INT UNSIGNED DEFAULT NULL,
    tipo VARCHAR(100) NOT NULL, equipamento VARCHAR(100) DEFAULT NULL, tempo_min INT UNSIGNED NOT NULL DEFAULT 0, solucao TEXT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_relatorios_chamado FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    CONSTRAINT fk_relatorios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_relatorios_local FOREIGN KEY (localizacao_id) REFERENCES localizacoes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS equipamentos;
CREATE TABLE equipamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL UNIQUE, ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS sessoes_lembradas;
CREATE TABLE sessoes_lembradas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT UNSIGNED NOT NULL,
    seletor CHAR(32) NOT NULL UNIQUE, validador_hash CHAR(64) NOT NULL, expira_em DATETIME NOT NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sessoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS tentativas_login;
CREATE TABLE tentativas_login (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(150) NOT NULL, ip VARCHAR(45) DEFAULT NULL, sucesso TINYINT(1) NOT NULL DEFAULT 0, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_tentativas_email_criado (email, criado_em), KEY idx_tentativas_ip_criado (ip, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO sla_prazos (prioridade, prazo_horas) VALUES ('baixa', 24), ('media', 12), ('alta', 8), ('urgente', 4);
INSERT INTO localizacoes (nome) VALUES ('CPD'), ('Recepção'), ('Arquivo'), ('Protocolo');
INSERT INTO equipamentos (nome) VALUES ('Nenhum'), ('Computador'), ('Notebook'), ('Impressora'), ('Monitor'), ('Roteador/Switch'), ('Telefone'), ('Outro');

-- IMPORTANTE: Substitua o hash abaixo por um hash real gerado via password_hash() no PHP!
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES 
('Administrador', 'admin@sead.gov.br', 'SUBSTITUA_PELO_HASH_GERADO', 'admin', 1);
