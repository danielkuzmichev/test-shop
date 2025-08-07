<?php
require __DIR__.'/../vendor/autoload.php';

use App\DatabaseManager;
use App\RedisManager;

try {
    $dbManager = new DatabaseManager();
    $redis = new RedisManager();
    
    $cacheKey = 'category_stats_cache';
    
    // Пытаемся получить данные из кэша
    if ($redis->isLocked($cacheKey)) {
        $cachedStats = $redis->getClient()->get($cacheKey);
        if ($cachedStats !== null) {
            $stats = json_decode($cachedStats, true);
            echo json_encode([
                'success' => true,
                'data' => $stats,
                'cached' => true
            ]);
            return;
        }
    }
    
    $stats = $dbManager->getCategoryStats();
    
    // Сохраняем в кэш на 1 час
    $redis->getClient()->set($cacheKey, json_encode($stats), 'EX', 3600);
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'cached' => false
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>