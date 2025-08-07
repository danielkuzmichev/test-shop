<?php

require __DIR__.'/../vendor/autoload.php';


use App\DatabaseManager;
use App\RedisManager;

$locker = new RedisManager();
$dbManager = new DatabaseManager();


$lockKey = "order_process";

try {
    if (!$locker->acquireLock($lockKey, 5)) {
        die('Process locked');
    }
    
    // Имитация обработки
    sleep(1);
    
    $productId = rand(1, 2); // Случайный продукт
    $orderId = $dbManager->addOrder($productId);
    
    echo "Added new order with product $productId";
    
} finally {
    $locker->releaseLock($lockKey);
}