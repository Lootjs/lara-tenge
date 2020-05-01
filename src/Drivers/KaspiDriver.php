<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

use Illuminate\Http\Request;
use Loot\Tenge\TengePayment;

final class KaspiDriver extends Driver implements DriverInterface
{
    /**
     * @inheritDoc
     */
    public function createPayment(TengePayment $payment, string $title = null)
    {
        // TODO: Implement createPayment() method.
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(TengePayment $payment, Request $request)
    {
        // TODO: Implement cancelPayment() method.
    }

    /**
     * @inheritDoc
     */
    public function approvePayment(TengePayment $payment, Request $request)
    {
        // TODO: Implement approvePayment() method.
    }
}
