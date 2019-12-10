<?php
namespace Loot\Tenge\Actions\Pipes;

use Loot\Tenge\Tenge;

class CheckPaymentExistsPipe {
    /**
     * Check that payment exists
     *
     * @param \Loot\Tenge\TengePayment $payment
     * @param \Closure $next
     * @throws \Exception
     * @return mixed
     */
    public function handle($payment, \Closure $next) {
        if (empty($payment)) {
            throw new \Exception('payment ' . $payment . ' not found');
        }

        Tenge::log('payment ' . $payment . ' was found');

        return $next($payment);
    }
}
