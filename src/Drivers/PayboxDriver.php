<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Loot\Tenge\Hook;
use Loot\Tenge\Tenge;
use Loot\Tenge\TengePayment;

class PayboxDriver extends Driver implements DriverInterface
{
    public function createPayment($paymentId, $amount, $title = '')
    {
        resolve('tenge_logger')->info('Payment ['.$paymentId.']: before create payment');
        $payment = TengePayment::insertRecord($paymentId, 'paybox', $amount);

        $params = [
            'pg_amount'         => $amount,
            'pg_check_url'      => route('tenge.checklink', ['paymentId' => $paymentId]),
            'pg_description'    => $title,
            'pg_encoding'       => $this->config['encoding'],
            'pg_currency'       => $this->config['currency'],
            'pg_user_ip'        => request()->ip(),
            'pg_lifetime'       => 86400,
            'pg_merchant_id'    => $this->config['merchant_id'],
            'pg_order_id'       => $paymentId,
            'pg_result_url'     => route('tenge.approvelink', ['paymentId' => $paymentId]),
            'pg_request_method' => 'POST',
            'pg_salt'           => uniqid(),
            'pg_success_url'    => config('tenge.routes.backlink'),
            'pg_failure_url'    => config('tenge.routes.failure_backlink'),
            'pg_testing_mode'   => config('tenge.environment') === 'local' ? 1 : 0,
        ];

        $url = 'payment.php';
        ksort($params);
        array_unshift($params, $url);
        array_push($params, $this->config['secret']);
        $params['pg_sig'] = md5(implode(';', $params));
        unset($params[0], $params[1]);

        $query = http_build_query($params);
        $url = 'https://api.paybox.money/'.$url.'?'.$query;

        $data = $payment->data;
        $data['pg_sig'] = $params['pg_sig'];
        $payment->data = $data;
        $payment->save();

        return new Fluent([
            'pay_url' => $url,
        ]);
    }

    /**
     * @param TengePayment $payment
     * @param Request $request
     * @return string
     */
    public function cancelPayment($payment, Request $request)
    {
        $payment->setCanceledStatus();
        $message = 'Payment ['.$payment->id.']: fail transaction';
        resolve('tenge_logger')->info($message, $payment);

        return $message;
    }

    /**
     * @param TengePayment $payment
     * @param Request $request
     * @return string
     */
    public function checkPayment($payment, Request $request)
    {
        if ($payment->status === TengePayment::STATUS_RECEIVED) {
            return 'OK';
        }

        return $payment->status;
    }

    /**
     * @param TengePayment $payment
     * @param Request $request
     * @return string
     */
    public function approvePayment($payment, Request $request)
    {
        resolve('tenge_logger')->info('Payment ['.$payment->id.']: before approve payment ', $request->all());

        if ($request->input('pg_result') == 0) {
            resolve('tenge_logger')->info('Payment ['.$payment->id.']: failed transaction', $request->all());

            return 'failed transaction';
        }

        if ($request->input('pg_sig') != $payment->data['pg_sig']) {
            resolve('tenge_logger')->info('Payment ['.$payment->id.']: signature doesnt match', $request->all());

            return 'signature doesnt match';
        }

        Hook::trigger('approve.after_validation')->with($payment->payment_id, $request);

        $payment->setApproveStatus();
        resolve('tenge_logger')->info('Payment ['.$payment->id.']: was approved', $payment);

        return 'OK';
    }
}
