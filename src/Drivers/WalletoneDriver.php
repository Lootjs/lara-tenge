<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Loot\Tenge\Tenge;
use Loot\Tenge\TengePayment;

class WalletoneDriver extends Driver implements DriverInterface
{
    /**
     * @param int $paymentId
     * @param int $amount
     * @param string $title Описание платежа
     *
     * @return Fluent|string
     */
    public function createPayment($paymentId, $amount, $title = '')
    {
        Tenge::log('before create payment '.$paymentId);
        $this->insertRecord($paymentId, 'walletone', $amount);

        $fields = [
            'WMI_MERCHANT_ID' => $this->config['WMI_MERCHANT_ID'],
            'WMI_PAYMENT_AMOUNT' => $amount,
            'WMI_PAYMENT_NO' => $paymentId,
            'WMI_CURRENCY_ID' => $this->config['WMI_CURRENCY_ID'],
            'WMI_DESCRIPTION' => 'BASE64:'.base64_encode($title),
            'WMI_SUCCESS_URL' => config('tenge.routes.backlink'),
            'WMI_FAIL_URL' => config('tenge.routes.failure_backlink'),
            'WMI_PTENABLED' => $this->config['WMI_PTENABLED'],
            'WMI_AUTO_LOCATION' => $this->config['WMI_AUTO_LOCATION'],
        ];

        foreach ($fields as $name => $val) {
            if (is_array($val)) {
                usort($val, 'strcasecmp');
                $fields[$name] = $val;
            }
        }

        uksort($fields, 'strcasecmp');
        $fieldValues = '';
        foreach ($fields as $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    //Конвертация из текущей кодировки (UTF-8)
                    //необходима только если кодировка магазина отлична от Windows-1251
                    $v = iconv('utf-8', 'windows-1251', $v);
                    $fieldValues .= $v;
                }
            } else {
                //Конвертация из текущей кодировки (UTF-8)
                //необходима только если кодировка магазина отлична от Windows-1251
                $value = iconv('utf-8', 'windows-1251', (string) $value);
                $fieldValues .= $value;
            }
        }

        $signature = base64_encode(pack('H*', md5($fieldValues.$this->config['key'])));
        $fields['WMI_SIGNATURE'] = $signature;

        try {
            (new Client)->post('https://wl.walletone.com/checkout/checkout/Index', [
                'form_params' => $fields,
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                },
            ]);
        } catch (ServerException $exception) {
            $message = 'Payment '.$paymentId.': fail with code 500, check your key and merchant id';
            Tenge::log($message);

            return $message;
        }

        return new Fluent([
            'pay_url' => (string) $url,
        ]);
    }

    public function cancelPayment($payment, Request $request)
    {
    }

    /**
     * @param TengePayment $payment
     * @param Request $request
     * @return int|string
     */
    public function approvePayment($payment, Request $request)
    {
        Tenge::log('before approve payment '.$payment->id, $request->all());

        $values = $this->getValues($request->all());

        $signature = base64_encode(pack('H*', md5($values.$this->config['key'])));

        if ($request->input('WMI_ORDER_STATE') == 'Accepted' && $request->input('WMI_SIGNATURE') == $signature) {
            if ($hook = config('tenge.hooks.approve.after_validation')) {
                call_user_func($hook, $payment->payment_id, $request);
            }

            Tenge::log('Payment ['.$payment->id.']: was approved', $payment);

            return 'WMI_RESULT=OK';
        }

        Tenge::log('Payment ['.$payment->id.']: signature doesnt match', $request->all());

        return 'WMI_RESULT=RETRY&WMI_DESCRIPTION=Сервер временно недоступен';
    }

    public function getValues(array $input): string
    {
        $params = [];

        foreach ($input as $name => $value) {
            if ($name !== 'WMI_SIGNATURE') {
                $params[$name] = $value;
            }
        }

        uksort($params, 'strcasecmp');
        $values = '';

        foreach ($params as $name => $value) {
            $values .= $value;
        }

        return $values;
    }
}
