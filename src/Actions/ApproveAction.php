<?php

namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Loot\Tenge\TengePayment;
use Loot\Tenge\Actions\Pipes\ {
    ApprovePaymentPipe,
    CheckPaymentExistsPipe,
    PaymentStatusIsReceivedPipe
};

class ApproveAction extends Action {
    public function handler($paymentId, Request $request) {
        $payment = TengePayment::where('payment_id', $paymentId)->first();
        $pipes = [
            new CheckPaymentExistsPipe($paymentId),
            PaymentStatusIsReceivedPipe::class,
            new ApprovePaymentPipe($request),
        ];

        return app(Pipeline::class)
            ->send($payment)
            ->through($pipes)
            ->thenReturn();
    }
}
