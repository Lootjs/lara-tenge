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
     * @return string
     * @throws \Exception
     */
    public function handle($payment, \Closure $next) {
        return Tenge::with($payment->driver)
            ->approvePayment($payment, $this->request);
    }
}
