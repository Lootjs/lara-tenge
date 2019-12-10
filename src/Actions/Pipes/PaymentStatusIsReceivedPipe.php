<?php
namespace Loot\Tenge\Actions\Pipes;

use Loot\Tenge\Tenge;
use Loot\Tenge\TengePayment;

class PaymentStatusIsReceivedPipe {
    /**
     * Check that payment exists
     *
     * @param TengePayment $payment
     * @param \Closure $next
     * @throws \Exception
     * @return mixed
     */
    public function handle($payment, \Closure $next) {
        if ($payment->status !== TengePayment::STATUS_RECEIVED) {
            throw new \Exception('payment status should be ' . TengePayment::STATUS_RECEIVED);
        }

        Tenge::log('payment ' . $payment . ' has correct status');

        return $next($payment);
    }
}
