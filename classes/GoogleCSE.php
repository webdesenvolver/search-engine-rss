<?php
/**
 * Classe GoogleCSE - Integração com Google Custom Search Engine
 */

class GoogleCSE {
    private $apiKey;
    private $searchEngineId;
    private $baseUrl = 'https://www.googleapis.com/customsearch/v1';
    
    public function __construct($apiKey, $searchEngineId) {
        $this->apiKey = $apiKey;
        $this->searchEngineId = $searchEngineId;
    }
    
    /**
     * Realizar busca
     */
    public function search($query, $page = 1) {
        if (empty($this->apiKey) || empty($this->searchEngineId)) {
            return [
                'success' => false,
                'error' => 'Google CSE não configurado. Configure GOOGLE_CSE_API_KEY e GOOGLE_CSE_ID em config.php'
            ];
        }
        
        if (strlen($query) > MAX_SEARCH_LENGTH) {
            return [
                'success' => false,
                'error' => 'Busca muito longa (máximo ' . MAX_SEARCH_LENGTH . ' caracteres)'
            ];
        }
        
        $startIndex = (($page - 1) * GOOGLE_CSE_RESULTS_PER_PAGE) + 1;
        
        $params = [
            'q' => $query,
            'cx' => $this->searchEngineId,
            'key' => $this->apiKey,
            'start' => $startIndex,
            'num' => GOOGLE_CSE_RESULTS_PER_PAGE
        ];
        
        $url = $this->baseUrl . '?' . http_build_query($params);
        
        try {
            $response = @file_get_contents($url);
            
            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'Erro ao conectar com Google CSE'
                ];
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['error'])) {
                return [
                    'success' => false,
                    'error' => 'Erro Google CSE: ' . $data['error']['message']
                ];
            }
            
            $results = [];
            if (isset($data['items'])) {
                foreach ($data['items'] as $item) {
                    $results[] = [
                        'title' => $item['title'] ?? '',
                        'link' => $item['link'] ?? '',
                        'snippet' => $item['snippet'] ?? '',
                        'source' => 'Google CSE',
                        'image' => $item['pagemap']['cse_image'][0]['src'] ?? null,
                        'date' => date('Y-m-d H:i:s')
                    ];
                }
            }
            
            return [
                'success' => true,
                'results' => $results,
                'totalResults' => $data['queries']['request'][0]['totalResults'] ?? 0,
                'page' => $page
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Exceção: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validar configuração
     */
    public function isConfigured() {
        return !empty($this->apiKey) && !empty($this->searchEngineId);
    }
}
