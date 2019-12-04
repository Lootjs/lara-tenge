<?php
declare(strict_types=1);

namespace Loot\Tenge\Drivers;

abstract class BaseDriver implements DriverInterface {
    /**
     * Keep configs for gateway
     *
     * @var $config array
     */
    public $config;

    public function __construct($config) {
        $this->config = $config;
    }
}
