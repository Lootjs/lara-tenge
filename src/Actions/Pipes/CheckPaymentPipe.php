<?php

namespace Loot\Tenge\Actions\Pipes;

use Illuminate\Http\Request;
use Loot\Tenge\Tenge;
use Loot\Tenge\TengePayment;

class CheckPaymentPipe
{
    /**
     * @var Request
     */
    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Check that payment exists.
     *
     * @param TengePayment $payment
     * @param \Closure $next
     * @return string
     * @throws \Exception
     */
    public function handle($payment, \Closure $next)
    {
        resolve('tenge_logger')->info('Payment ['.$payment->id.']: checking payment', $payment->toArray());

        return Tenge::with($payment->driver)
            ->checkPayment($payment, $this->request);
    }
}
