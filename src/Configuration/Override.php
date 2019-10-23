<?php

namespace Yoga\Configuration;

/**
 * @method static Override service()
 */
class Override extends \Yoga\Service {

    /**
     * In this array, keys are getter names in Configuration, values are returned values.
     * For example ['getSqlConfiguration' => new ...]
     * @var array
     */
    private $override = [];

    /**
     * @param string[] $configurationKeys
     * @return string
     */
    public function encodeToString(array $configurationKeys) {
        $configurationValues = [];
        foreach ($configurationKeys as $configurationKey) {
            if (array_key_exists($configurationKey, $this->override)) {
                $configurationValue = $this->override[$configurationKey];
            } else {
                $configurationValue = $this->getConfigurationValueFromSource($configurationKey);
            }
            $configurationValues[$configurationKey] = $configurationValue;
        }
        return \Yoga\Pickler::service()->pickleToBase64String($configurationValues);
    }

    /**
     * @param string $encoded
     */
    public function overrideFromString($encoded) {
        $override = $this->decodeFromString($encoded);
        $this->override($override ?: []);
    }

    /**
     * @param array $override
     */
    public function override(array $override) {
        $this->override = $override;
    }

    /**
     * @param string $encoded
     * @return array
     */
    public function decodeFromString($encoded) {
        return \Yoga\Pickler::service()->unpickleFromBase64String($encoded);
    }

    public function __call($configurationKey, $arguments) {
        if (array_key_exists($configurationKey, $this->override)) {
            if ($arguments) {
                throw new \Exception(
                    'Configuration methods should have no arguments ($configurationKey = `' .
                        $configurationKey . '`, $arguments = `' . var_export($arguments, true) . '`'
                );
            }
            return $this->override[$configurationKey];
        }
        return $this->getConfigurationValueFromSource($configurationKey, $arguments);
    }

    /**
     * @return \Yoga\Configuration
     */
    public function getSource() {
        static $source;
        if (!$source) {
            if (!class_exists($class = '\Tests\Configuration')) {
                if (!class_exists($class = '\Configuration')) {
                    if (!class_exists($class = '\ConfigurationBase')) {
                        $class = \Yoga\Configuration::class;
                    }
                }
            }
            $source = new $class;
        }
        return $source;
    }

    private function getConfigurationValueFromSource($configurationKey, $arguments = null) {
        $callable = [$this->getSource(), $configurationKey];
        if ($arguments) {
            return call_user_func_array($callable, $arguments);
        }
        static $result = [];
        if (!array_key_exists($configurationKey, $result)) {
            $result[$configurationKey] = call_user_func($callable);
        }
        return $result[$configurationKey];
    }

}