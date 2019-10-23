<?php

namespace Yoga\Console\Command;

abstract class ConfigurationOverride extends \Yoga\Console\Command {

    /**
     * @var string(isOption = true)
     */
    public $configurationOverride;

    final public function handle() {
        \Yoga\Configuration\Override::service()
            ->overrideFromString($this->configurationOverride);
        return $this->doHandle();
    }

    abstract protected function doHandle();

}
