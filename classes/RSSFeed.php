<?php
/**
 * Classe RSSFeed - Parser de RSS Feeds
 */

class RSSFeed {
    private $feeds;
    private $cache;
    
    public function __construct($feedsFile, $cacheFile) {
        $this->feeds = new Database($feedsFile);
        $this->cache = new Database($cacheFile);
    }
    
    /**
     * Adicionar novo feed
     */
    public function addFeed($url, $name = null, $category = 'geral') {
        if (!$this->isValidUrl($url)) {
            return ['success' => false, 'error' => 'URL inválida'];
        }
        
        $id = md5($url);
        $feedData = [
            'id' => $id,
            'url' => $url,
            'name' => $name ?? parse_url($url, PHP_URL_HOST),
            'category' => $category,
            'added_at' => date('Y-m-d H:i:s'),
            'last_fetch' => null,
            'active' => true
        ];
        
        $this->feeds->add($id, $feedData);
        return ['success' => true, 'id' => $id];
    }
    
    /**
     * Listar todos os feeds
     */
    public function getAllFeeds() {
        return $this->feeds->all();
    }
    
    /**
     * Obter feed ativo
     */
    public function getActiveFeeds() {
        $all = $this->feeds->all();
        return array_filter($all, function($feed) {
            return $feed['active'] === true;
        });
    }
    
    /**
     * Deletar feed
     */
    public function deleteFeed($id) {
        return $this->feeds->delete($id);
    }
    
    /**
     * Atualizar feed
     */
    public function updateFeed($id, $data) {
        $feed = $this->feeds->get($id, []);
        $updated = array_merge($feed, $data);
        return $this->feeds->update($id, $updated);
    }
    
    /**
     * Buscar e cachear itens de um feed
     */
    public function fetchFeed($url) {
        $cacheKey = md5($url);
        $cached = $this->cache->get($cacheKey);
        
        // Se há cache e ainda é válido, retorna cache
        if ($cached && isset($cached['timestamp'])) {
            if (time() - $cached['timestamp'] < RSS_CACHE_TIME) {
                return $cached['items'];
            }
        }
        
        // Buscar novo feed
        $items = $this->parseFeed($url);
        
        if (!empty($items)) {
            // Cachear resultado
            $this->cache->add($cacheKey, [
                'timestamp' => time(),
                'items' => $items
            ]);
        }
        
        return $items;
    }
    
    /**
     * Parser do feed RSS/Atom
     */
    private function parseFeed($url) {
        $items = [];
        
        try {
            $context = stream_context_create(['http' => ['timeout' => 10]]);
            $xml = @file_get_contents($url, false, $context);
            
            if ($xml === false) {
                return [];
            }
            
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->load('data://text/xml;base64,' . base64_encode($xml));
            libxml_clear_errors();
            
            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
            $xpath->registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
            
            // Processar itens RSS
            $rssItems = $xpath->query('//item');
            if ($rssItems->length > 0) {
                foreach ($rssItems as $item) {
                    $title = $xpath->query('title', $item)->item(0)?->nodeValue ?? '';
                    $link = $xpath->query('link', $item)->item(0)?->nodeValue ?? '';
                    $description = $xpath->query('description', $item)->item(0)?->nodeValue ?? '';
                    $pubDate = $xpath->query('pubDate', $item)->item(0)?->nodeValue ?? date('Y-m-d H:i:s');
                    $image = null;
                    
                    // Tentar extrair imagem
                    $enclosure = $xpath->query('enclosure', $item)->item(0);
                    if ($enclosure) {
                        $image = $enclosure->getAttribute('url');
                    }
                    
                    if (!empty($title) && !empty($link)) {
                        $items[] = [
                            'title' => trim($title),
                            'link' => trim($link),
                            'description' => trim(strip_tags($description)),
                            'snippet' => substr(trim(strip_tags($description)), 0, 200),
                            'pubDate' => $this->parseDate($pubDate),
                            'source' => 'RSS Feed',
                            'image' => $image,
                            'date' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
            
            // Processar itens Atom
            $atomItems = $xpath->query('//atom:entry');
            if ($atomItems->length > 0) {
                foreach ($atomItems as $item) {
                    $title = $xpath->query('atom:title', $item)->item(0)?->nodeValue ?? '';
                    $link = $xpath->query('atom:link', $item)->item(0)?->getAttribute('href') ?? '';
                    $summary = $xpath->query('atom:summary', $item)->item(0)?->nodeValue ?? '';
                    $published = $xpath->query('atom:published', $item)->item(0)?->nodeValue ?? date('Y-m-d H:i:s');
                    
                    if (!empty($title) && !empty($link)) {
                        $items[] = [
                            'title' => trim($title),
                            'link' => trim($link),
                            'description' => trim(strip_tags($summary)),
                            'snippet' => substr(trim(strip_tags($summary)), 0, 200),
                            'pubDate' => $this->parseDate($published),
                            'source' => 'RSS Feed',
                            'image' => null,
                            'date' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
            
            // Limitar resultados
            return array_slice($items, 0, RSS_MAX_RESULTS);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Parse de data para formato padrão
     */
    private function parseDate($dateString) {
        try {
            $date = new DateTime($dateString);
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Validar URL
     */
    private function isValidUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Obter todos os itens de feeds ativos
     */
    public function getAllItems() {
        $allItems = [];
        $feeds = $this->getActiveFeeds();
        
        foreach ($feeds as $feed) {
            $items = $this->fetchFeed($feed['url']);
            foreach ($items as $item) {
                $item['feed'] = $feed['name'];
                $item['feedId'] = $feed['id'];
                $allItems[] = $item;
            }
        }
        
        // Ordenar por data (mais recente primeiro)
        usort($allItems, function($a, $b) {
            return strtotime($b['pubDate']) - strtotime($a['pubDate']);
        });
        
        return array_slice($allItems, 0, RSS_MAX_RESULTS);
    }
}
