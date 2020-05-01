<?php

declare(strict_types=1);

namespace Loot\Tenge;

use RuntimeException;
use Illuminate\Http\Request;
use Loot\Tenge\Drivers\DriverInterface;

class Tenge implements DriverInterface
{
    /**
     * @var string[]
     */
    private $drivers = [];

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var string[]
     */
    public $config = [];

    /**
     * @var string
     */
    public $uses;

    /**
     * Set payment driver.
     *
     * @param string $driver
     *
     * @param Logger $logger
     */
    public function __construct(string $driver, Logger $logger)
    {
        $this->logger = $logger;
        $this->uses = $driver;
        $this->drivers = config('tenge.drivers');

        if (! isset($this->drivers[$driver])) {
            throw new RuntimeException(sprintf('Driver [%s] not found', $driver));
        }

        $config = $this->drivers[$driver];
        $handler = $config['handler'];
        $this->driver = \app()->makeWith($handler, ['config' => $config, 'logger' => $logger]);
    }

    /**
     * @param int $paymentId
     * @param int $amount
     * @param string|null $title
     */
    public function initPayment(int $paymentId, int $amount, string $title = null)
    {
        $payment = TengePayment::insertRecord($paymentId, $this->uses, $amount);
        $this->logger->info('Payment ['.$paymentId.']: before create payment');
        $this->driver->createPayment($payment, $title);
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(TengePayment $payment, Request $request)
    {
        $this->driver->cancelPayment(...func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function approvePayment(TengePayment $payment, Request $request)
    {
        $this->logger->info('Payment ['.$payment->id.']: before approve', $request->toArray());
        $this->driver->approvePayment(...func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function createPayment(TengePayment $payment, string $title = null)
    {
        $this->driver->createPayment(...func_get_args());
    }
}
