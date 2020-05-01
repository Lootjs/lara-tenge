<?php

namespace Loot\Tenge\Drivers;

use Illuminate\Http\Request;
use Loot\Tenge\TengePayment;

interface DriverInterface
{
    /**
     * @param TengePayment $payment
     * @param string $title
     * @return mixed
     */
    public function createPayment(TengePayment $payment, string $title = null);

    /**
     * @param TengePayment $payment
     * @param Request $request
     * @return string
     */
    public function cancelPayment(TengePayment $payment, Request $request);

    /**
     * @param TengePayment $payment
     * @param Request $request
     * @return mixed
     */
    public function approvePayment(TengePayment $payment, Request $request);
}
