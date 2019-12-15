## Установка
#### Установить пакет
Для установки пакета, необходимо запустить:
``composer require loot/lara-tenge``

Далее, вам надо опубликовать конфиг файлы, миграции, и тестовые ключи, для этого запустите:
``php artisan vendor:publish --provider=Loot\Tenge\ServiceProvider``

Затем запустите миграции: ``php artisan migrate``

Теперь вам надо написать код, который выполнится, после успешной оплаты. 
Сохраните вызов этого кода в конфиг-файле config/tenge.php, в секции hooks.approve.after_validation
Если ваш код находится в контроллере, укажите его в конфиг примерно так:
```php
// Указать код необходимо как массив, в формате: ['название класса', 'название метода']
'after_validation' => [\App\Http\Controllers\PaymentController::class, 'approvePayment'],
// или так
'after_validation' => [\App\Services\Payment::class, 'approve'],
```
А также, необходимо сделать исключение в VerifyCsrfToken мидлваре для роутов, на которые платежные системы делают запрос:
```php
protected $except = [
    'lara-tenge/*',
];
````
