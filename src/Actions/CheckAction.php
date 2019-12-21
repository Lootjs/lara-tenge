<?php

namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Loot\Tenge\Actions\Pipes\CheckPaymentPipe;
use Loot\Tenge\Actions\Pipes\PaymentExistsPipe;
use Loot\Tenge\TengePayment;

class CheckAction extends Action
{
    /**
     * @param $paymentId
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function handler($paymentId, Request $request)
    {
        /**
         * @var TengePayment
         */
        $payment = TengePayment::where('payment_id', $paymentId)->first();
        $pipes = [
            new PaymentExistsPipe($paymentId),
            new CheckPaymentPipe($request),
        ];

        return app(Pipeline::class)
            ->send($payment)
            ->through($pipes)
            ->thenReturn();
    }
}
