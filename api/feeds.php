<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/RSSFeed.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$rssFeed = new RSSFeed(DB_FEEDS_FILE, DB_CACHE_FEEDS);

try {
    if ($action === 'list' && $method === 'GET') {
        echo json_encode([
            'success' => true,
            'feeds' => $rssFeed->getAllFeeds()
        ]);
        
    } elseif ($action === 'add' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['url'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'URL do feed vazia']);
            exit;
        }
        
        $result = $rssFeed->addFeed($data['url'], $data['name'] ?? null, $data['category'] ?? 'geral');
        echo json_encode($result);
        
    } elseif ($action === 'delete' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do feed vazio']);
            exit;
        }
        
        $rssFeed->deleteFeed($data['id']);
        echo json_encode(['success' => true]);
        
    } elseif ($action === 'update' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do feed vazio']);
            exit;
        }
        
        $rssFeed->updateFeed($data['id'], $data);
        echo json_encode(['success' => true]);
        
    } elseif ($action === 'fetch' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['url'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'URL do feed vazia']);
            exit;
        }
        
        $items = $rssFeed->fetchFeed($data['url']);
        echo json_encode([
            'success' => true,
            'items' => array_slice($items, 0, 10)
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ação inválida']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
