<?php
declare(strict_types=1);

namespace Loot\Tenge;

use Loot\Tenge\Drivers\DriverInterface;
use Loot\Tenge\Loggers\LoggerInterface;

class Tenge {
    /**
     * Set payment driver
     *
     * @throws \Exception
     * @param string $driver
     *
     * @return DriverInterface
     */
    public static function with(string $driver): DriverInterface {
        if (! array_key_exists($driver, config('tenge.drivers'))) {
            throw new \Exception(sprintf('Driver [%s] not found', $driver));
        }

        $configs = config('tenge.drivers')[$driver];

        return new $configs['handler']($configs);
    }

    /**
     * Logging payment lifecycle
     */

    public static function log($data) {
        return resolve('tenge_logger')->log($data);
    }
}
