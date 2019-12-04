<?php
declare(strict_types=1);

namespace Loot\Tenge;

use Loot\Tenge\Drivers\DriverInterface;

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
}
