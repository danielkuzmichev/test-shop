<?php

namespace App;

use Predis\Client;

class RedisManager {
    private $redis;
    
    public function __construct() {
        $this->redis = new Client([
            'host' => 'redis',
            'port' => 6379
        ]);
    }
    
    /**
     * Устанавливает блокировку
     * @param string $key - ключ блокировки
     * @param int $ttl - время жизни в секундах
     * @return bool - успешность установки блокировки
     */
    public function acquireLock($key, $ttl = 10) {
        return $this->redis->set($key, 1, 'EX', $ttl, 'NX');
    }
    
    /**
     * Освобождает блокировку
     * @param string $key - ключ блокировки
     */
    public function releaseLock($key) {
        $lockKey = $key;
        $this->redis->del([$lockKey]);
    }
    
    /**
     * Проверяет существование блокировки
     * @param string $key - ключ блокировки
     * @return bool
     */
    public function isLocked($key) {
        return $this->redis->exists($key) > 0;
    }
}
