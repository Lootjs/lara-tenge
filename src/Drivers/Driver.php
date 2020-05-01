<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

use Loot\Tenge\Logger;

abstract class Driver implements DriverInterface
{
    /**
     * Keep configs for gateway.
     *
     * @var array
     */
    public $config;

    /**
     * @var \Monolog\Logger $logger
     */
    protected $logger;

    public function __construct($config, Logger $logger)
    {
        $this->logger = $logger;
        $this->config = $config;
    }
}
