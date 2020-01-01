## Добавление своего обработчика
В файле /config/tenge.php, находятся все поддерживаемые платежные системы. Для добавления новой системы, в конфиг файле вам надо создать новые элемент, в tenge.drivers:

---
Вам надо указать название системы (допустим, kazkom), и класс обработчик
```php
'kazkom' => [
   'handler' => \App\Services\Kazkom::class,
],
```
Класс \App\Services\Kazkom должен расширять класс \Loot\Tenge\Drivers\Drivers\Driver и имплементить интерфейс \Loot\Tenge\Drivers\Drivers\DriverInterface, а значит, должен реализовать методы:
- createPayment
- cancelPayment
- approvePayment
---
## Использование своего обработчика
Вы можете поменять класс-обработчик для уже поставляемых систем, например:
```php
 'epay' => [
   'handler' => \App\Services\MyEpayDriver::class,
]
```
