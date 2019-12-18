<?php

namespace Loot\Tenge\Actions\Pipes;

use Loot\Tenge\Tenge;

class CheckPaymentExistsPipe
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
            throw new \Exception('payment '.$this->paymentId.' not found');
        }

        Tenge::log('payment '.$payment->id.' was found', $payment);

        return $next($payment);
    }
}
