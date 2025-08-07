<?php

require __DIR__.'/../vendor/autoload.php';


use App\DatabaseManager;
use App\RedisManager;

$redis = new RedisManager();
$dbManager = new DatabaseManager();


$lockKey = "order_process";

try {
    if (!$redis->acquireLock($lockKey, 5)) {
        die('Process locked');
    }
    
    // Имитация обработки
    sleep(5);
    
    $productId = rand(1, 2); // Случайный продукт
    $orderId = $dbManager->addOrder($productId);
    $redis->releaseLock('category_stats_cache');
    
    echo "Added new order with product $productId";
    
} finally {
    $redis->releaseLock($lockKey);
}