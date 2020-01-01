## Пример использования
### Создание платежа
Прежде всего, необходимо создать заказ в вашей системе, и передать его данные:
```php
$paymentId = 10;
$amount = 2000;
$driver = 'epay';
$title = 'Описание платежа, опционально';

$paymentService = Loot\Tenge\Tenge::with($driver)->createPayment($paymentId, $amount, $title);
```
При успешном выполнении, в $paymentService будет ссылка на страницу оплаты:

```php
return redirect()->to($paymentService->get('pay_url'));
```

### Подтверждение платежа
Вам необходимо указать код, который выполнится после успешной оплаты. 

Для этого откройте config/tenge.php, и укажите класс и метод, например:
````php
[
    'hooks' => [
        'approve' => [
            'after_validation' => [\App\Http\Controllers\Controller::class, 'successPayment']
        ]
    ]
];
````
Controller@successPayment:
```php
public function successPayment($paymentId, Request $request) {
        $payment = Payment::find($paymentId);
        $payment->status = 1;
        $payment->save();
    }
``` 
