<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';

header('Content-Type: application/xml; charset=utf-8');

$db = new Database(DB_SEARCHES_FILE);
$searches = $db->all();

$baseUrl = BASE_URL;
$today = date('Y-m-d');

// Começar XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// URLs principais
$urls = [
    '' => '1.0',
    'admin' => '0.8',
];

foreach ($urls as $url => $priority) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($baseUrl . ($url ? "/$url" : '')) . "</loc>\n";
    $xml .= "    <lastmod>$today</lastmod>\n";
    $xml .= "    <changefreq>daily</changefreq>\n";
    $xml .= "    <priority>$priority</priority>\n";
    $xml .= "  </url>\n";
}

// URLs de buscas recentes
$count = 0;
foreach (array_reverse($searches) as $id => $search) {
    if ($count >= SITEMAP_MAX_URLS) break;
    
    $url = $baseUrl . '?q=' . urlencode($search['query']);
    $date = isset($search['timestamp']) ? date('Y-m-d', strtotime($search['timestamp'])) : $today;
    
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
    $xml .= "    <lastmod>$date</lastmod>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.6</priority>\n";
    $xml .= "  </url>\n";
    
    $count++;
}

$xml .= "</urlset>";

echo $xml;

// Salvar sitemap
file_put_contents(SITEMAP_FILE, $xml);
