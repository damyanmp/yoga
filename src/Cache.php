<?php

namespace Yoga;

/**
 * @method static Cache service()
 */
class Cache extends Service {

    public function set($key, $value, $lifetimeSeconds = 86400) {
        $this->getMemcached()->set($key, $value, $lifetimeSeconds);
    }

    public function get($key) {
        return $this->getMemcached()->get($key);
    }

    public function delete($key) {
        $this->getMemcached()->delete($key);
    }

    public function increment($key, $offset = 1) {
        return $this->getMemcached()->increment($key, $offset);
    }

    protected function getConfiguration() {
        return Configuration::service()->getCacheConfiguration();
    }

    /**
     * @return \Memcached
     */
    private function getMemcached() {
        return ComputeOnce::service()->handle(function () {
            return $this->factory($this->getConfiguration());
        });
    }

    private function factory(\Yoga\Configuration\Cache $configuration) {
        if ($configuration instanceof \Yoga\Configuration\Cache\Memcache) {
            $memcached = new \Memcached;
            $memcached->addServer($configuration->getHost(), $configuration->getPort());
            return $memcached;
        }
        throw new \Exception('Configuration not supported: `' . get_class($configuration) . '`');
    }

}