<?php

namespace Loot\Tenge\Drivers;

use Illuminate\Http\Request;

interface DriverInterface {
    public function createPayment(...$args);

    public function cancelPayment();

    public function approvePayment($id, Request $request);
}
