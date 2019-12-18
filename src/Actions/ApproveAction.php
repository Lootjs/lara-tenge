<?php

namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Loot\Tenge\Actions\Pipes\ApprovePaymentPipe;
use Loot\Tenge\Actions\Pipes\CheckPaymentExistsPipe;
use Loot\Tenge\Actions\Pipes\PaymentStatusIsReceivedPipe;
use Loot\Tenge\DeterminateDriver;
use Loot\Tenge\TengePayment;

class ApproveAction extends Action
{
    public function handler($paymentId, Request $request)
    {
        if (empty($paymentId)) {
            $paymentId = (new DeterminateDriver($request))
                ->process()
                ->get('payment_id');
        }

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
