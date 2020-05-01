<?php

declare(strict_types=1);

namespace Loot\Tenge\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Loot\Tenge\Hook;
use Loot\Tenge\TengePayment;

final class ProstoplategDriver extends Driver implements DriverInterface
{
    /**
     * @inheritDoc
     */
    public function createPayment(TengePayment $payment, string $title = null)
    {
        (new Client)->post($this->config['pay_gate_url'], [
            'form_params' => $this->generateFields(...func_get_args()),
            'on_stats' => function (TransferStats $stats) use (&$url): void {
                $url = $stats->getEffectiveUri();
            },
        ]);

        return new Fluent([
            'pay_url' => (string) $url,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(TengePayment $payment, Request $request)
    {
        $payment->setCanceledStatus();
        $message = 'Payment ['.$payment->id.']: fail transaction';
        $this->logger->info($message, $payment->toArray());

        return $message;
    }

    /**
     * @inheritDoc
     */
    public function approvePayment(TengePayment $payment, Request $request): string
    {
        $uniq_paygate = $_POST['RETURN_UNIQ_ID'] + 0;
        $merchant = $_POST['RETURN_MERCHANT'] + 0;
        $serversign = $_POST['RETURN_HASH'];
        $result = $_POST['RETURN_RESULT'] + 0;
        $amount = $_POST['RETURN_AMOUNT'] + 0;
        $comission = $_POST['RETURN_COMISSION'];
        $comisstype = $_POST['RETURN_COMMISSTYPE'] + 0;
        $testmode = $_POST['TEST_MODE'] + 0;
        $paymentdate = $_POST['PAYMENT_DATE'];
        $addvalue = $_POST['RETURN_ADDVALUE'];
        $paygateid = $_POST['RETURN_CLIENTORDER'] + 0;
        $mp = $_POST['RETURN_TYPE'] + 0;

        if ($result != 20) {
            return $this->cancelPayment($payment, $request);
        }

        $shopsign = "$merchant:$addvalue:$paygateid:$amount:$comission:$uniq_paygate:$testmode:$paymentdate:".$this->config['secret_code']."$result";
        $shopsign = md5($shopsign);

        if ($shopsign != $serversign) {
            $msgerror = "При проверке транзакции хеши не совпадают\n hash = $shopsign\n server_hash = $serversign\n";

            $this->logger->info($msgerror, $request->all());
            exit;
        }

        Hook::trigger('approve.after_validation')->with($payment->payment_id, $request);

        return 'OK';
    }

    /**
     * @param int $paymentId
     * @param int $amount
     * @param string $title
     * @return array
     */
    protected function generateFields(int $paymentId, int $amount, string $title = ''): array
    {
        $textcoding = $this->config['text_coding'];
        $linexmlhead = chr(60)."?xml version=\"1.0\" encoding=\"$textcoding\"?".chr(62);

        $urlresult = route('tenge.approvelink', ['paymentId' => $paymentId], true);
        $urlreturn = config('tenge.routes.backlink');
        $urlfail = config('tenge.routes.failure_backlink');

        $comis = $this->config['commission'];
        $testmode = config('tenge.environment') === 'local' ? 1 : 0;

        $mp = $this->config['payment_method'];
        $amount = round($amount, 2);
        $deliver = $this->config['deliver'];

        $amount = round($amount * 100);
        $date = date('YmdHis');
        $orderhash = md5($date.$title.$amount.$date.$paymentId.$mp);

        $operxml = <<<OPP
$linexmlhead
<MAIN>
<PAYMENT_AMOUNT>$amount</PAYMENT_AMOUNT>
<PAYMENT_INFO>$title</PAYMENT_INFO>
<PAYMENT_DELIVER>$deliver</PAYMENT_DELIVER>
<PAYMENT_ADDVALUE>$orderhash</PAYMENT_ADDVALUE>
<PAYMENT_ORDER>$paymentId</PAYMENT_ORDER>
<PAYMENT_TYPE>$mp</PAYMENT_TYPE>
<PAYMENT_RULE>$comis</PAYMENT_RULE>
<PAYMENT_VISA></PAYMENT_VISA>
<PAYMENT_RETURNRES>$urlresult</PAYMENT_RETURNRES>
<PAYMENT_RETURN><![CDATA[$urlreturn]]></PAYMENT_RETURN>
<PAYMENT_RETURNMET>2</PAYMENT_RETURNMET>
<PAYMENT_RETURNFAIL><![CDATA[$urlfail]]></PAYMENT_RETURNFAIL>
<PAYMENT_TESTMODE>$testmode</PAYMENT_TESTMODE>
</MAIN>
OPP;

        $operxml = base64_encode(rawurlencode($operxml));
        $sign = md5($operxml.$this->config['secret_code']);

        return [
            'flagxml' => 1,
            'strxml' => $operxml,
            'MERCHANT_INFO' => $this->config['merchant_id'],
            'PAYMENT_HASH' => $sign,
        ];
    }
}
