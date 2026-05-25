<?php
/**
 * Classe Database - Gerenciador de arquivos JSON
 */

class Database {
    private $path;
    
    public function __construct($filepath) {
        $this->path = $filepath;
        $this->ensureFile();
    }
    
    /**
     * Garante que o arquivo existe
     */
    private function ensureFile() {
        if (!file_exists($this->path)) {
            file_put_contents($this->path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    
    /**
     * Ler todos os dados
     */
    public function all() {
        $content = file_get_contents($this->path);
        return json_decode($content, true) ?? [];
    }
    
    /**
     * Obter item por chave
     */
    public function get($key, $default = null) {
        $data = $this->all();
        return $data[$key] ?? $default;
    }
    
    /**
     * Salvar dados
     */
    public function save($data) {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($this->path, $json) !== false;
    }
    
    /**
     * Adicionar item
     */
    public function add($key, $value) {
        $data = $this->all();
        $data[$key] = $value;
        return $this->save($data);
    }
    
    /**
     * Atualizar item
     */
    public function update($key, $value) {
        return $this->add($key, $value);
    }
    
    /**
     * Deletar item
     */
    public function delete($key) {
        $data = $this->all();
        unset($data[$key]);
        return $this->save($data);
    }
    
    /**
     * Limpar tudo
     */
    public function clear() {
        return $this->save([]);
    }
    
    /**
     * Contar items
     */
    public function count() {
        return count($this->all());
    }
    
    /**
     * Verificar se existe chave
     */
    public function exists($key) {
        $data = $this->all();
        return isset($data[$key]);
    }
    
    /**
     * Buscar por valor (case-insensitive)
     */
    public function search($searchTerm, $field = null) {
        $data = $this->all();
        $results = [];
        $searchTerm = strtolower($searchTerm);
        
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                if ($field && isset($item[$field])) {
                    if (strpos(strtolower($item[$field]), $searchTerm) !== false) {
                        $results[$key] = $item;
                    }
                } else {
                    foreach ($item as $value) {
                        if (is_string($value) && strpos(strtolower($value), $searchTerm) !== false) {
                            $results[$key] = $item;
                            break;
                        }
                    }
                }
            } else {
                if (strpos(strtolower($item), $searchTerm) !== false) {
                    $results[$key] = $item;
                }
            }
        }
        
        return $results;
    }
}
