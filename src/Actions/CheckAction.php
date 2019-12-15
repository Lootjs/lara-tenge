<?php
namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Loot\Tenge\Tenge;
use Loot\Tenge\TengePayment;

class CheckAction extends Action {
    public function handler($paymentId, Request $request) {
        /**
         * @var $payment TengePayment
         */
        $payment = TengePayment::where('payment_id', $paymentId)->first();

        return Tenge::with($payment->driver)->checkPayment($payment, $request);
    }
}
