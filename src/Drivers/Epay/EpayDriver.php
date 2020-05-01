<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers\Epay;

use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Loot\Tenge\Drivers\Driver;
use Loot\Tenge\Drivers\DriverInterface;
use Loot\Tenge\Hook;
use Loot\Tenge\TengePayment;

final class EpayDriver extends Driver implements DriverInterface
{
    /**
     * @return string|null
     */
    protected function getURL(): ?string
    {
        return $this->config['action_url'][config('tenge.environment')];
    }

    /**
     * @inheritDoc
     */
    public function createPayment(TengePayment $payment, string $title = null): Fluent
    {
        $params = http_build_query([
            'Signed_Order_B64' => (new KKBsign)
                ->process_request($payment->id, $this->config['currency_id'], $payment->amount, $this->config),
            'email' => 'laravel@gmail.com',
            'BackLink' => config('tenge.routes.backlink'),
            'PostLink' => route('tenge.approvelink', ['paymentId' => $payment->id], true),
            'FailureBackLink' => config('tenge.routes.failure_backlink'),
            'FailurePostLink' => route('tenge.faillink', ['paymentId' => $payment->id], true),
            //'appendix',
            //'template',
        ]);
        $url = $this->getURL().'?'.$params;

        return new Fluent([
            'pay_url' => $url,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(TengePayment $payment, Request $request): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function approvePayment(TengePayment $payment, Request $request)
    {
        //$payment = TengePayment::where('payment_id', $result['ORDER_ORDER_ID'])->first();

        if (! $request->filled('response')) {
            $message = 'response field is empty';
            $this->logger->info($message, $request->all());

            return $message;
        }

        $error = 0;
        try {
            $result = (new KKBsign)->process_response($request->input('response'), $this->config);
        } catch (\Exception $exception) {
            $result = $exception->getMessage();
        }

        if (is_array($result)) {
            if (in_array('ERROR', $result)) {
                if ($result['ERROR_TYPE'] == 'ERROR') {
                    $error = 'System error:'.$result['ERROR'];
                } elseif ($result['ERROR_TYPE'] == 'system') {
                    $error = "Bank system error > Code: '".$result['ERROR_CODE']."' Text: '".$result['ERROR_CHARDATA']."' Time: '".$result['ERROR_TIME']."' Order_ID: '".$result['RESPONSE_ORDER_ID']."'";
                } elseif ($result['ERROR_TYPE'] == 'auth') {
                    $error = "Bank system user autentication error > Code: '".$result['ERROR_CODE']."' Text: '".$result['ERROR_CHARDATA']."' Time: '".$result['ERROR_TIME']."' Order_ID: '".$result['RESPONSE_ORDER_ID']."'";
                }
            }
        } else {
            $error = 'System error'.$result;
            $result = [];
        }

        if ($result['PAYMENT_MERCHANT_ID'] != $this->config['MERCHANT_ID']) {
            $error = 'merchant id doesnt match '.$result['PAYMENT_MERCHANT_ID'];
        } elseif ($result['PAYMENT_RESPONSE_CODE'] != '00') {
            $error = 'Bad response';
        } elseif ($result['PAYMENT_AMOUNT'] != ($payment->amount / 100)) {
            $error = 'Other amount: '.$result['PAYMENT_AMOUNT'].' != '.$payment->amount;
        }

        if ($error) {
            $prefix = 'Payment ['.$payment->id.']: ';
            $this->logger->info($prefix.$error, $result);

            return 'Error: '.$error;
        }

        $payment->setApproveStatus();

        Hook::trigger('approve.after_validation')->with($payment->payment_id, $request);
        $this->logger->info('Payment ['.$payment->id.']: after approve', $payment->toArray());

        return 0;
    }
}
