<?php
/**
 * Search Engine RSS - Configuração Principal
 */

// Configurações gerais
define('APP_NAME', 'Search Engine RSS');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true);

// Caminhos
define('BASE_PATH', dirname(__FILE__));
define('DATA_PATH', BASE_PATH . '/data');
define('CACHE_PATH', DATA_PATH . '/cache');
define('LOGS_PATH', BASE_PATH . '/logs');

// URLs
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));
define('API_URL', BASE_URL . '/api');

// Google Custom Search Engine
define('GOOGLE_CSE_API_KEY', 'YOUR_GOOGLE_API_KEY_HERE');
define('GOOGLE_CSE_ID', 'YOUR_CSE_ID_HERE');
define('GOOGLE_CSE_RESULTS_PER_PAGE', 10);

// RSS Feeds
define('RSS_CHECK_INTERVAL', 3600); // 1 hora
define('RSS_CACHE_TIME', 1800); // 30 minutos
define('RSS_MAX_RESULTS', 50);

// Database (JSON)
define('DB_FEEDS_FILE', DATA_PATH . '/feeds.json');
define('DB_RESULTS_FILE', DATA_PATH . '/results.json');
define('DB_SEARCHES_FILE', DATA_PATH . '/searches.json');
define('DB_CACHE_FEEDS', CACHE_PATH . '/feeds_cache.json');

// Sitemap
define('SITEMAP_FILE', BASE_PATH . '/sitemap.xml');
define('SITEMAP_MAX_URLS', 50000);

// Paginação
define('RESULTS_PER_PAGE', 10);

// Segurança
define('MAX_SEARCH_LENGTH', 200);
define('ALLOWED_ORIGINS', ['*']); // Ajuste conforme necessário

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Criar diretórios se não existirem
if (!is_dir(DATA_PATH)) mkdir(DATA_PATH, 0755, true);
if (!is_dir(CACHE_PATH)) mkdir(CACHE_PATH, 0755, true);
if (!is_dir(LOGS_PATH)) mkdir(LOGS_PATH, 0755, true);

// Headers padrão
header('Content-Type: text/html; charset=utf-8');
header('X-UA-Compatible: ie=edge');

// Error handling
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
