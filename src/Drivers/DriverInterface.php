<?php

namespace Loot\Tenge\Drivers;

use Illuminate\Http\Request;
use Loot\Tenge\TengePayment;

interface DriverInterface
{
    /**
     * @param int $paymentId
     * @param $amount
     * @param null $title
     * @return mixed
     */
    public function createPayment(int $paymentId, $amount, $title = null);

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
