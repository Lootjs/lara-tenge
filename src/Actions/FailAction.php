<?php
namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;
use Loot\Tenge\Tenge;

class FailAction extends Action {
    public function handler($paymentId, Request $request) {
        Tenge::log('payment ' . $paymentId. ' is failed', $paymentId);
    }
}
