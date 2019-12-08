<?php

namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Loot\Tenge\{
    Tenge,
    TengePayment
};
class ApproveAction {
    public function __invoke($paymentId, Request $request) {
        try {
            $payment = TengePayment::where('payment_id', $paymentId)->first();

            if (empty($payment)) {
                throw new \Exception('payment ' . $paymentId . ' not found');
            }

            Tenge::with($payment->driver)->approvePayment($paymentId, $request);

        } catch (\Exception $exception) {
            Tenge::log($exception->getMessage());

            return $exception->getMessage();
        }

        return true;
    }
}
