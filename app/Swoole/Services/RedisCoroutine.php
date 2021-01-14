<?php

namespace App\Swoole\Services;

use Swoole\Coroutine\Redis;
use SwooleTW\Http\Coroutine\ConnectionException;

class RedisCoroutine
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();

        $this->redis->connect(
            config('database.redis.default.host'),
            config('database.redis.default.port')
        );

        $this->redis->auth(config('database.redis.default.password'));

        if (!$this->redis->connected) {
            throw new ConnectionException($this->redis->errMsg, $this->redis->errCode);
        }

        $this->redis->select(config('database.redis.default.database'));
    }

    public function __destruct()
    {
        if ($this->redis->connected) {
            $this->redis->close();
        }
    }

    public function __call($name, $arguments)
    {
        if (isset($arguments[0])) {
            $arguments[0] = config('database.redis.options.prefix') . $arguments[0];
        }

        return $this->redis->$name(...$arguments);
    }
}
