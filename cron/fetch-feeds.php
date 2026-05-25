<?php
/**
 * Cron Job - Atualizar RSS Feeds
 * Execute: php -f cron/fetch-feeds.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/RSSFeed.php';

// Verificar se é CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Acesso apenas via CLI');
}

echo "[" . date('Y-m-d H:i:s') . "] Iniciando atualização de RSS feeds...\n";

try {
    $rssFeed = new RSSFeed(DB_FEEDS_FILE, DB_CACHE_FEEDS);
    $feeds = $rssFeed->getActiveFeeds();
    
    if (empty($feeds)) {
        echo "[" . date('Y-m-d H:i:s') . "] Nenhum feed ativo para processar.\n";
        exit(0);
    }
    
    $count = 0;
    foreach ($feeds as $feed) {
        echo "Processando: {$feed['name']}...\n";
        
        try {
            $items = $rssFeed->fetchFeed($feed['url']);
            
            // Atualizar last_fetch
            $rssFeed->updateFeed($feed['id'], [
                'last_fetch' => date('Y-m-d H:i:s'),
                'item_count' => count($items)
            ]);
            
            echo "  ✓ {$feed['name']}: " . count($items) . " itens processados\n";
            $count++;
            
        } catch (Exception $e) {
            echo "  ✗ {$feed['name']}: Erro - " . $e->getMessage() . "\n";
        }
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Atualização concluída: $count feeds processados\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
