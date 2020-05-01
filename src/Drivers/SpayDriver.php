<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Loot\Tenge\Hook;
use Loot\Tenge\TengePayment;

final class SpayDriver extends Driver implements DriverInterface
{
    /**
     * @inheritDoc
     */
    public function createPayment(TengePayment $payment, string $title = null)
    {
        resolve('tenge_logger')->info('before create payment '.$payment->id);
        TengePayment::insertRecord($payment->id, 'spay', $payment->amount);

        $fields = [
            'MERCHANT_ID' => $this->config['MERCHANT_ID'],
            'PAYMENT_AMOUNT' => $payment->amount,
            'PAYMENT_TYPE' => $this->config['PAYMENT_TYPE'],
            'PAYMENT_ORDER_ID' => $payment->id,
            'PAYMENT_INFO' => $title,
            'PAYMENT_RETURN_URL' => config('tenge.routes.backlink'),
            'PAYMENT_RETURN_FAIL_URL' => config('tenge.routes.failure_backlink'),
            'PAYMENT_CALLBACK_URL' => route('tenge.approvelink', ['paymentId' => $payment->id]),
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

        $hash = base64_encode(pack('H*', md5($fieldValues.$this->config['secret_key'])));
        $fields['PAYMENT_HASH'] = $hash;

        try {
            $request = (new Client)->post('https://spos.kz/merchant/api/create_invoice', [
                'form_params' => $fields,
            ]);
            $response = json_decode($request->getBody()->getContents());

            if ($response->status > 0) {
                throw new \Exception($response->desc);
            }

            return new Fluent([
                'pay_url' => $response->data->url,
            ]);
        } catch (\Exception $exception) {
            $message = 'Payment ['.$payment->id.']: '.$exception->getMessage();
            $this->logger->info($message);

            return $message;
        }
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
        $values = $this->getValues($request->all());

        $signature = base64_encode(pack('H*', md5($values.$this->config['secret_key'])));

        if ($request->input('WMI_ORDER_STATE') == 'Accepted' && $request->input('WMI_SIGNATURE') == $signature) {
            Hook::trigger('approve.after_validation')->with($payment->payment_id, $request);

            $this->logger->info('Payment ['.$payment->id.']: was approved', $payment->toArray());

            return 'RESULT=OK';
        }

        $this->logger->info('Payment ['.$payment->id.']: signature doesnt match', $request->all());

        return 'RESULT=RETRY&DESCRIPTION=Сервер временно недоступен';
    }

    /**
     * @param array $input
     * @return string
     */
    public function getValues(array $input): string
    {
        $params = [];

        foreach ($input as $name => $value) {
            if ($name !== 'PAYMENT_HASH') {
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
