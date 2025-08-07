<?php

namespace App;

use mysqli;

class DatabaseManager {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->connection = new mysqli(
            'db',      // host
            'user',    // username
            'password', // password
            'test_db'  // database
        );
        
        if ($this->connection->connect_error) {
            throw new \Exception("Connection failed: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8mb4");
    }
    
    public function addOrder($productId) {
        $stmt = $this->connection->prepare(
            "INSERT INTO orders (product_id) VALUES (?)"
        );
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        
        return $this->connection->insert_id;
    }
    
    public function getCategoryStats($limit = 100) {
            $stmt = $this->connection->prepare("
            SELECT 
                c.name AS category,
                COUNT(o.id) AS orders_count,
                MIN(o.purchase_time) AS first_order_time,
                MAX(o.purchase_time) AS last_order_time,
                TIMESTAMPDIFF(SECOND, MIN(o.purchase_time), MAX(o.purchase_time)) AS time_diff_seconds
            FROM orders o
            JOIN products p ON o.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            GROUP BY c.name
            ORDER BY orders_count DESC
            LIMIT 100
        ");

        if (!$stmt) {
            throw new \Exception("Ошибка подготовки запроса: " . $this->connection->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $seconds = $row['time_diff_seconds'];
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;
            
            $parts = [];
            if ($hours > 0) $parts[] = "$hours ч";
            if ($minutes > 0) $parts[] = "$minutes мин";
            if ($seconds > 0 || empty($parts)) $parts[] = "$seconds сек";
    
            $stats[] = [
                'category' => $row['category'],
                'orders_count' => (int)$row['orders_count'],
                'time_period' => [
                    'seconds' => (int)$row['time_diff_seconds'],
                    'human_readable' => "$hours ч $minutes мин $seconds сек",
                ],
                'first_order' => $row['first_order_time'],
                'last_order' => $row['last_order_time'],
            ];
        }

        $response = [
            'success' => true,
            'data' => $stats,
            'meta' => [
                'total_categories' => count($stats),
                'total_orders' => array_sum(array_column($stats, 'orders_count')),
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];

        return $response;

    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}