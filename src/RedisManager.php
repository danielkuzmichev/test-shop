<?php

namespace App;

require __DIR__ . '/../bootstrap.php';

use Predis\Client;

class RedisManager 
{
    private $redis;
    
    public function __construct() 
    {
        $this->redis = new Client($_ENV['REDIS']);
    }

    public function acquireLock($key, $ttl = 10) {
        return $this->redis->set($key, 1, 'EX', $ttl, 'NX');
    }

    public function releaseLock($key) 
    {
        $lockKey = $key;
        $this->redis->del([$lockKey]);
    }

    public function isLocked($key) 
    {
        return $this->redis->exists($key) > 0;
    }

    public function getClient() 
    {
        return $this->redis;
    }
    
}
