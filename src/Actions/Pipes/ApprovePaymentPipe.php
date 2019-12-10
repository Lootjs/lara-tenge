<?php
namespace Loot\Tenge\Actions\Pipes;

use Loot\Tenge\Tenge;

class ApprovePaymentPipe {
    public $request;

    public function __construct($request) {
        $this->request = $request;
    }

    /**
     * Check that payment exists
     *
     * @param \Loot\Tenge\TengePayment $payment
     * @param \Closure $next
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    public function handle($payment, \Closure $next) {
        $response = Tenge::with($payment->driver)->approvePayment($payment->payment_id, $this->request);
        Tenge::log('payment ' . $payment . ' was approved');

        return $response;
    }
}
