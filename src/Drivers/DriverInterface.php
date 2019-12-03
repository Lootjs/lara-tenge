<?php

namespace Loot\Tenge\Drivers;

interface DriverInterface {
    public function createPayment();

    public function cancelPayment();

    public function approvePayment();
}
