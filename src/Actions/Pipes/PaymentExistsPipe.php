<?php

namespace Loot\Tenge\Actions\Pipes;

class PaymentExistsPipe
{
    public $paymentId;

    public function __construct($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * Check that payment exists.
     *
     * @param \Loot\Tenge\TengePayment $payment
     * @param \Closure $next
     * @throws \Exception
     * @return mixed
     */
    public function handle($payment, \Closure $next)
    {
        if (empty($payment)) {
            throw new \Exception('Payment ['.$payment->id.']: not found');
        }

        resolve('tenge_logger')->info('Payment ['.$payment->id.']: was found', $payment->toArray());

        return $next($payment);
    }
}
