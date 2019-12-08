<?php

namespace Loot\Tenge\Drivers;

use Illuminate\Http\Request;

interface DriverInterface {
    public function createPayment($paymentId, $amount);

    public function cancelPayment();

    public function approvePayment($id, Request $request);
}
