<?php

namespace Yoga;

/**
 * @method static Redis service()
 */
class Redis extends Service {

    /**
     * @var \Yoga\Configuration\Redis
     */
    private $configuration;

    public function set($key, $value) {
        $this->getRedis()->set($key, $value);
    }

    public function get($key) {
        return $this->getRedis()->get($key);
    }

    public function delete($key) {
        $this->getRedis()->delete($key);
    }

    public function getConfiguration() {
        if (!$this->configuration) {
            $this->configuration = Configuration::service()->getRedisConfiguration();
        }
        return $this->configuration;
    }

    public function setConfiguration(\Yoga\Configuration\Cache\Redis $configuration) {
        if ($this->configuration) {
            throw new \Exception('setConfiguration() must be called only once, before connection to the cache server is established');
        }
        $this->configuration = $configuration;
    }

    /**
     * @return \Redis
     */
    private function getRedis() {
        return ComputeOnce::service()->handle(function () {
            return $this->factory($this->getConfiguration());
        });
    }

    private function factory(\Yoga\Configuration\Redis $configuration) {
        if ($configuration instanceof \Yoga\Configuration\Cache\Redis) {
            $redis = new Predis\Client([
                'scheme' => 'tcp',
                'host' => $configuration->getHost(),
                'port' => $configuration->getPort()
            ]);
            return $redis;
        }
        throw new \Exception('Configuration not supported: `' . get_class($configuration) . '`');
    }

}