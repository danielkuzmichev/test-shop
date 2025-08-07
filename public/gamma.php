<?php

require __DIR__.'/../vendor/autoload.php';

use App\DatabaseManager;

try {
    $dbManager = new DatabaseManager();
    $stats = $dbManager->getCategoryStats();
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>