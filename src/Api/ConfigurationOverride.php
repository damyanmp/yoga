<?php

namespace Yoga\Api;

abstract class ConfigurationOverride extends \Yoga\Api {

    /**
     * @var string
     */
    public $configurationOverride;

    final public function handle() {
        \Yoga\Configuration\Override::service()
            ->overrideFromString($this->configurationOverride);
        return $this->doHandle();
    }

    abstract protected function doHandle();

}