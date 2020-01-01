<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

abstract class Driver implements DriverInterface
{
    /**
     * Keep configs for gateway.
     *
     * @var array
     */
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }
}
