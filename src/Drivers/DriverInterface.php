<?php

namespace Loot\Tenge\Drivers;

use Illuminate\Http\Request;

interface DriverInterface {
    public function createPayment($paymentId, $amount, $title = null);

    public function cancelPayment($id, Request $request);

    public function approvePayment($id, Request $request);
}
