<?php

namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Loot\Tenge\TengePayment;
use Loot\Tenge\Actions\Pipes\ {
    ApprovePaymentPipe,
    CheckPaymentExistsPipe,
    PaymentStatusIsReceivedPipe
};

class ApproveAction extends AbstractAction {
    public function handler($paymentId, Request $request) {
        $payment = TengePayment::where('payment_id', $paymentId)->first();
        $pipes = [
            CheckPaymentExistsPipe::class,
            PaymentStatusIsReceivedPipe::class,
        ];

        if (config('tenge.hooks.beforeApprove')) {
            $hook = config('tenge.hooks.beforeApprove');
            $pipes[] = new $hook($request);
        }

        $pipes[] = new ApprovePaymentPipe($request);

        return app(\Illuminate\Pipeline\Pipeline::class)
            ->send($payment)
            ->through($pipes)
            ->then(function ($response) {
                return $response;
            });
    }
}
