<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

use Loot\Tenge\TengePayment;

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

    /**
     * Create record in payments table.
     *
     * @param int $paymentId
     * @param string $driver
     * @param int $amount
     * @return TengePayment
     */
    public function insertRecord($paymentId, $driver, $amount): TengePayment
    {
        return TengePayment::create([
            'payment_id' => $paymentId,
            'driver' => $driver,
            'amount' => ($amount * 100),
            'status' => TengePayment::STATUS_RECEIVED,
            'data' => [],
        ]);
    }
}
