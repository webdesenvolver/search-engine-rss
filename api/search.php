<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/GoogleCSE.php';
require_once __DIR__ . '/../classes/RSSFeed.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Instanciar classes
$db = new Database(DB_SEARCHES_FILE);
$googleCSE = new GoogleCSE(GOOGLE_CSE_API_KEY, GOOGLE_CSE_ID);

try {
    if ($action === 'search' && $method === 'GET') {
        $query = trim($_GET['q'] ?? '');
        $page = (int)($_GET['page'] ?? 1);
        $source = $_GET['source'] ?? 'all'; // all, google, rss
        
        if (empty($query)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Termo de busca vazio']);
            exit;
        }
        
        if (strlen($query) > MAX_SEARCH_LENGTH) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Busca muito longa']);
            exit;
        }
        
        // Salvar busca no histórico
        $searchId = md5($query . time());
        $db->add($searchId, [
            'query' => $query,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        $results = [];
        $totalResults = 0;
        
        // Buscar no Google CSE
        if (($source === 'all' || $source === 'google') && $googleCSE->isConfigured()) {
            $googleResults = $googleCSE->search($query, $page);
            if ($googleResults['success']) {
                $results = array_merge($results, $googleResults['results']);
                $totalResults += $googleResults['totalResults'];
            }
        }
        
        // Buscar em RSS Feeds
        if ($source === 'all' || $source === 'rss') {
            $rssFeed = new RSSFeed(DB_FEEDS_FILE, DB_CACHE_FEEDS);
            $feedItems = $rssFeed->getAllItems();
            
            foreach ($feedItems as $item) {
                $queryLower = strtolower($query);
                if (
                    strpos(strtolower($item['title']), $queryLower) !== false ||
                    strpos(strtolower($item['description']), $queryLower) !== false
                ) {
                    $results[] = $item;
                }
            }
            
            $totalResults += count($results);
        }
        
        // Remover duplicatas
        $uniqueResults = [];
        $links = [];
        foreach ($results as $result) {
            if (!isset($links[$result['link']])) {
                $uniqueResults[] = $result;
                $links[$result['link']] = true;
            }
        }
        
        // Paginar
        $start = ($page - 1) * RESULTS_PER_PAGE;
        $paginatedResults = array_slice($uniqueResults, $start, RESULTS_PER_PAGE);
        
        echo json_encode([
            'success' => true,
            'query' => $query,
            'results' => $paginatedResults,
            'totalResults' => count($uniqueResults),
            'page' => $page,
            'totalPages' => ceil(count($uniqueResults) / RESULTS_PER_PAGE)
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ação inválida']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
