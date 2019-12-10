<?php
declare(strict_types=1);

namespace Loot\Tenge\Drivers\Epay;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Loot\Tenge\Drivers\ {
    BaseDriver, DriverInterface
};
use Loot\Tenge\TengePayment;

class EpayDriver extends BaseDriver implements DriverInterface {

    public function createPayment($paymentId, $amount) {
        (new Client)->post($this->getURL(), [
            'form_params' => [
                'Signed_Order_B64' => (new KKBsign)->process_request($paymentId, $this->config['currency_id'], $amount, $this->config),
                'paymentID' => $paymentId,
                'Language' => 'rus',
                'BackLink' => route('tenge.backlink', ['paymentId' => $paymentId], true),
                'FailureBackLink' => route('tenge.backlink', ['paymentId' => $paymentId], true),
                'PostLink' => route('tenge.approvelink', ['paymentId' => $paymentId], true),
                'FailurePostLink' => route('tenge.faillink', ['paymentId' => $paymentId], true),
            ],
            'on_stats' => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            }
        ]);

        return new Fluent([
            'pay_url' => (string) $url
        ]);
    }

    public function cancelPayment() {

    }

    public function approvePayment($paymentId, Request $request) {
        /**
         * @var $payment TengePayment
         */
        $payment = TengePayment::where('payment_id', $paymentId)->first();
        $payment->setApproveStatus();

        return 0;
    }

    protected function getURL() {
        return $this->config['action_url'][config('tenge.environment')];
    }
}
