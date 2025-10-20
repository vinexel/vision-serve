<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Deeper\Libraries;

class Redis
{
    protected $host;
    protected $port;
    protected $timeout;
    protected $retries;
    protected $debug;
    protected $socket;
    protected $ttlMap = []; // internal TTL for keys
    protected $pipelineQueue = [];
    protected $inTransaction = false;

    public function __construct($host = '127.0.0.1', $port = 6379, $timeout = 2, $retries = 3, $debug = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->retries = $retries;
        $this->debug = $debug;

        $this->connect();
    }

    protected function log($msg)
    {
        if ($this->debug) error_log("[RedisInternalUltra] $msg");
    }

    protected function connect()
    {
        for ($i = 0; $i < $this->retries; $i++) {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            if ($this->socket) {
                stream_set_timeout($this->socket, $this->timeout);
                $this->log("Connected to Redis {$this->host}:{$this->port}");
                return;
            }
            usleep(100000);
        }
        throw new \Exception("Cannot connect to Redis: $errstr ($errno)");
    }

    protected function sendCommand(array $args)
    {
        if (!$this->socket) $this->connect();

        $command = "*" . count($args) . "\r\n";
        foreach ($args as $arg) {
            $command .= "$" . strlen($arg) . "\r\n$arg\r\n";
        }

        fwrite($this->socket, $command);
        return $this->readResponse();
    }

    protected function readResponse()
    {
        $line = fgets($this->socket);
        if ($line === false) throw new \Exception("Redis did not respond");

        $prefix = $line[0];
        $payload = substr($line, 1, -2);

        switch ($prefix) {
            case '+':
                return $payload;
            case '-':
                throw new \Exception($payload);
            case ':':
                return (int)$payload;
            case '$':
                $length = (int)$payload;
                if ($length === -1) return null;
                $data = fread($this->socket, $length + 2);
                return substr($data, 0, -2);
            case '*':
                $count = (int)$payload;
                $items = [];
                for ($i = 0; $i < $count; $i++) $items[] = $this->readResponse();
                return $items;
            default:
                throw new \Exception("Unknown Redis response: $line");
        }
    }

    protected function checkTTL($key)
    {
        if (isset($this->ttlMap[$key]) && time() > $this->ttlMap[$key]) {
            $this->del($key);
            unset($this->ttlMap[$key]);
        }
    }

    protected function serialize($value)
    {
        if (is_array($value) || is_object($value)) return serialize($value);
        return $value;
    }

    protected function unserialize($value)
    {
        $unserialized = @unserialize($value);
        return $unserialized === false && $value !== 'b:0;' ? $value : $unserialized;
    }

    // ================= BASIC =================
    public function set($key, $value, $expire = null)
    {
        $this->checkTTL($key);
        $value = $this->serialize($value);
        $res = $this->sendCommand(['SET', $key, $value]);
        if ($expire) $this->ttlMap[$key] = time() + $expire;
        return $res;
    }

    public function get($key)
    {
        $this->checkTTL($key);
        $value = $this->sendCommand(['GET', $key]);
        return $this->unserialize($value);
    }

    public function del($key)
    {
        unset($this->ttlMap[$key]);
        return $this->sendCommand(['DEL', $key]);
    }

    public function exists($key)
    {
        $this->checkTTL($key);
        return $this->sendCommand(['EXISTS', $key]) == 1;
    }

    public function expire($key, $seconds)
    {
        $this->checkTTL($key);
        $this->ttlMap[$key] = time() + $seconds;
        return $this->sendCommand(['EXPIRE', $key, $seconds]);
    }

    public function ttl($key)
    {
        $this->checkTTL($key);
        if (isset($this->ttlMap[$key])) return $this->ttlMap[$key] - time();
        return -1;
    }

    public function flushAll()
    {
        $this->ttlMap = [];
        return $this->sendCommand(['FLUSHALL']);
    }

    public function incr($key)
    {
        $this->checkTTL($key);
        return (int)$this->sendCommand(['INCR', $key]);
    }

    public function decr($key)
    {
        $this->checkTTL($key);
        return (int)$this->sendCommand(['DECR', $key]);
    }

    public function keys($pattern = '*')
    {
        return $this->sendCommand(['KEYS', $pattern]);
    }

    // ================= HASH =================
    public function hSet($hash, $field, $value)
    {
        $value = $this->serialize($value);
        return $this->sendCommand(['HSET', $hash, $field, $value]);
    }

    public function hGet($hash, $field)
    {
        $value = $this->sendCommand(['HGET', $hash, $field]);
        return $this->unserialize($value);
    }

    public function hDel($hash, $field)
    {
        return $this->sendCommand(['HDEL', $hash, $field]);
    }

    public function hGetAll($hash)
    {
        $all = $this->sendCommand(['HGETALL', $hash]);
        $result = [];
        for ($i = 0; $i < count($all); $i += 2) $result[$all[$i]] = $this->unserialize($all[$i + 1]);
        return $result;
    }

    // ================= LIST =================
    public function lPush($key, $value)
    {
        $value = $this->serialize($value);
        return $this->sendCommand(['LPUSH', $key, $value]);
    }
    public function rPush($key, $value)
    {
        $value = $this->serialize($value);
        return $this->sendCommand(['RPUSH', $key, $value]);
    }
    public function lPop($key)
    {
        return $this->unserialize($this->sendCommand(['LPOP', $key]));
    }
    public function rPop($key)
    {
        return $this->unserialize($this->sendCommand(['RPOP', $key]));
    }
    public function lRange($key, $start, $end)
    {
        return array_map([$this, 'unserialize'], $this->sendCommand(['LRANGE', $key, $start, $end]));
    }

    // ================= SET =================
    public function sAdd($key, $value)
    {
        $value = $this->serialize($value);
        return $this->sendCommand(['SADD', $key, $value]);
    }
    public function sRem($key, $value)
    {
        $value = $this->serialize($value);
        return $this->sendCommand(['SREM', $key, $value]);
    }
    public function sMembers($key)
    {
        return array_map([$this, 'unserialize'], $this->sendCommand(['SMEMBERS', $key]));
    }

    // ================= SORTED SET =================
    public function zAdd($key, $score, $value)
    {
        $value = $this->serialize($value);
        return $this->sendCommand(['ZADD', $key, $score, $value]);
    }
    public function zRange($key, $start, $end, $withScores = false)
    {
        $cmd = ['ZRANGE', $key, $start, $end];
        if ($withScores) $cmd[] = 'WITHSCORES';
        return $this->sendCommand($cmd);
    }

    // ================= PIPELINE / MULTI =================
    public function multi()
    {
        $this->inTransaction = true;
        $this->pipelineQueue = [];
    }
    public function exec()
    {
        $this->inTransaction = false;
        $results = [];
        foreach ($this->pipelineQueue as $cmd) {
            $results[] = $this->sendCommand($cmd);
        }
        $this->pipelineQueue = [];
        return $results;
    }
    public function pipelineCommand($cmd)
    {
        if ($this->inTransaction) $this->pipelineQueue[] = $cmd;
        else return $this->sendCommand($cmd);
    }

    public function pipeline(callable $callback)
    {
        $this->multi();
        $pipe = new class($this) {
            private $redis;
            public function __construct($redis)
            {
                $this->redis = $redis;
            }
            public function __call($name, $args)
            {
                $method = $name;
                array_unshift($args, $name);
                return $this->redis->pipelineCommand([$method, ...$args]);
            }
        };
        $callback($pipe);
        return $this->exec();
    }

    public function close()
    {
        if ($this->socket) fclose($this->socket);
    }
}
