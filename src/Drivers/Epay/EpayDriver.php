<?php
declare(strict_types=1);

namespace Loot\Tenge\Drivers\Epay;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Loot\Tenge\Drivers\ {
    Driver, DriverInterface
};
use Loot\Tenge\Tenge;
use Loot\Tenge\TengePayment;

class EpayDriver extends Driver implements DriverInterface {

    protected function getURL() {
        return $this->config['action_url'][config('tenge.environment')];
    }

    /**
     * @param int $paymentId
     * @param int $amount
     * @param string $title
     * @return Fluent
     */
    public function createPayment($paymentId, $amount, $title = null) {
        Tenge::log('before create payment '. $paymentId);
        $this->insertRecord($paymentId, 'epay', $amount);

        (new Client)->post($this->getURL(), [
            'form_params' => [
                'Signed_Order_B64' => (new KKBsign)->process_request($paymentId, $this->config['currency_id'], $amount, $this->config),
                'paymentID' => $paymentId,
                'Language' => 'rus',
                'BackLink' => config('tenge.routes.backlink'),
                'FailureBackLink' => config('tenge.routes.failure_backlink'),
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

    public function cancelPayment($payment, Request $request) {

    }

    /**
     * @param TengePayment $payment
     * @param Request $request
     * @return int|string
     */
    public function approvePayment($payment, Request $request) {
        //$payment = TengePayment::where('payment_id', $result['ORDER_ORDER_ID'])->first();
        Tenge::log('before approve payment '. $payment->id, $request->all());

        if (! $request->filled('response')) {
            $message = 'response field is empty';
            Tenge::log($message, $request->all());

            return $message;
        }

        $error = 0;
        $result = (new KKBsign)->process_response($request->input('response'), $this->config);

        if (is_array($result)) {
            if (in_array('ERROR', $result)) {
                if ($result['ERROR_TYPE'] == 'ERROR') {
                    $error = 'System error:' . $result['ERROR'];
                } elseif ($result["ERROR_TYPE"] == "system") {
                    $error = "Bank system error > Code: '" . $result["ERROR_CODE"] . "' Text: '" . $result["ERROR_CHARDATA"] . "' Time: '" . $result["ERROR_TIME"] . "' Order_ID: '" . $result["RESPONSE_ORDER_ID"] . "'";
                } elseif ($result["ERROR_TYPE"] == "auth") {
                    $error = "Bank system user autentication error > Code: '" . $result["ERROR_CODE"] . "' Text: '" . $result["ERROR_CHARDATA"] . "' Time: '" . $result["ERROR_TIME"] . "' Order_ID: '" . $result["RESPONSE_ORDER_ID"] . "'";
                }
            }
        } else {
            $error = "System error" . $result;
        }

        if ($result['PAYMENT_MERCHANT_ID'] != '98800841') {
            $error = 'merchant id doesnt match ' . $result['PAYMENT_MERCHANT_ID'];
        } else if ($result['PAYMENT_RESPONSE_CODE'] != '00') {
            $error = 'Bad response';
        } else if ($result['PAYMENT_AMOUNT'] != $payment->amount) {
            $error = 'Other amount';
        }

        if ($error) {
            $prefix = 'Payment ['.$payment->id.']: ';
            Tenge::log($prefix . $error, $result);

            return 'Error: '.$error;
        }

        if ($hook = config('tenge.hooks.approve.after_validation')) {
            call_user_func($hook, $payment->payment_id, $request);
        }

        $payment->setApproveStatus();
        Tenge::log('Payment ['.$payment->id.']: was approved', $payment);

        return 0;
    }
}
