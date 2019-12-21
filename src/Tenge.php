<?php

declare(strict_types=1);

namespace Loot\Tenge;

use Illuminate\Contracts\Support\Arrayable;
use Loot\Tenge\Drivers\DriverInterface;

class Tenge
{
    /**
     * Set payment driver.
     *
     * @throws \Exception
     * @param string $driver
     *
     * @return DriverInterface
     */
    public static function with(string $driver): DriverInterface
    {
        if (! array_key_exists($driver, config('tenge.drivers'))) {
            throw new \Exception(sprintf('Driver [%s] not found', $driver));
        }

        $config = config('tenge.drivers')[$driver];

        return new $config['handler']($config);
    }

    /**
     * Logging payment lifecycle.
     * @param $message
     * @param mixed $data
     * @return void
     */
    public static function log($message, $data = []): void
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return resolve('tenge_logger')->info($message, $data);
    }
}
