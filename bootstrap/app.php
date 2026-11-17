<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);

// Carrega o autoload do Composer
require_once $basePath . '/vendor/autoload.php';

// Carrega as variáveis do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable($basePath);
$dotenv->safeLoad();

// Define o fuso horário
date_default_timezone_set(
    $_ENV['APP_TIMEZONE'] ?? 'America/Fortaleza'
);

// Retorna o caminho principal do projeto
return $basePath;