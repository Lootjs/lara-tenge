<?php

namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Loot\Tenge\Tenge;
use Loot\Tenge\TengePayment;

class FailAction extends Action
{
    public function handler($paymentId, Request $request)
    {
        /**
         * @var TengePayment $payment
         */
        $payment = TengePayment::where('payment_id', $paymentId)->first();
        resolve('tenge_logger')->info('Payment ['.$paymentId.']: transaction is failed', $payment->toArray());

        return Tenge::with($payment->driver)->cancelPayment($payment, $request);
    }
}
