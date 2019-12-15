<?php

use Loot\Tenge\Drivers\{PayboxDriver,
    CyberplatDriver,
    KaspiDriver,
    ProstoplategDriver,
    SpayDriver,
    WalletoneDriver,
    Epay\EpayDriver};

return [
    /*
     * Название таблицы, для хранения оплат
     */
    'table_name' => 'tenge_payments',

    /*
     * Текущая среда
     */
    'environment' => env('APP_ENV', 'production'), // local

    /*
     * Логирование
     */
    'logging' => true,

    /*
     * Поддерживаемые платежные системы
     */
    'drivers' => [
        'epay' => [
            'action_url' => [
                'production' => 'https://epay.kkb.kz/jsp/process/logon.jsp',
                'local' => 'https://testpay.kkb.kz/jsp/process/logon.jsp',
            ],

            /*
             * Класс-обработчик
             */
            'handler' => EpayDriver::class,

            /*
             * Серийный номер сертификата Cert Serial Number
             */
            'MERCHANT_CERTIFICATE_ID' => env('EPAY_MERCHANT_CERTIFICATE_ID', '00C182B189'),

            /*
             * Путь к XML шаблону для команд (возврат/подтверждение)
             */
            'XML_COMMAND_TEMPLATE_FN' => storage_path('tenge/epay/command_template.xml'),

            /*
             * Пароль к закрытому ключу Private cert password
             */
            'PRIVATE_KEY_PASS' => env('EPAY_PRIVATE_KEY_PASS', 'nissan'),

            /*
             * Путь к XML шаблону XML template path
             */
            'XML_TEMPLATE_FN' => storage_path('tenge/epay/template.xml'),

            /*
             * Путь к закрытому ключу Private cert path
             */
            'PRIVATE_KEY_FN' => storage_path('tenge/epay/test_prv.pem'),

            /*
             * Путь к открытому ключу Public cert path
             */
            'PUBLIC_KEY_FN' => storage_path('tenge/epay/kkbca.pem'),

            /*
             * Терминал ИД в банковской Системе
             */
            'MERCHANT_ID' => env('EPAY_MERCHANT_ID', 92061101),

            /*
             * Название магазина (продавца) Shop/merchant Name
             */
            'MERCHANT_NAME' => 'Lara-Tenge Shop',

            /*
             * Шифр валюты  - 840-USD, 398-Tenge
             */

            'currency_id' => 398,
        ],
        'walletone' => [
            'handler' => WalletoneDriver::class,
            'key' => env('WALLETONE_KEY', '34755c361a6c313'),
            'WMI_MERCHANT_ID' => env('WALLETONE_MERCHANT_ID', 123),
            'WMI_CURRENCY_ID' => 398,

            /*
             * Способ оплаты
             *
             * В тестовом режиме доступен только CreditCardRUB
             */
            'WMI_PTENABLED' => 'CashTerminalKZT',

            /*
             * Показывать способы оплаты, соответствующие стране нахождения
             *
             * 1 — страна пользователя и отображение способов определяется по IP
             */
            'WMI_AUTO_LOCATION' => 1,
        ],
        'prostoplateg' => [
            'handler' => ProstoplategDriver::class,

            /*
             * номер мерчанта (магазина), полученный при регистрации
             */
            'merchant_id' => 292,

            /*
             * секретный ключ, с помощью которого формируется цифровая подпись
             */
            'secret_code' => env('PROSTOPLATEG_SECRET_CODE', 'KKKjfd4i9hhhcn3h3h3dhchc'),

            /*
             * информация о доставке
             */
            'deliver' => '',

            /*
             * тип валюты, который используется при оплате
             * 31 — termkz (Касса24, Qiwi);
             * 26 — WMK;
             * 1 — WMZ;
             * 5 — Яндекс.Деньги;
             * 33 — Visa/Master
             * 34 — BTC (Bitcoin)
             */
            'payment_method' => 26,
            'text_coding' => 'UTF-8',

            /*
             * указание, на чей счет будет отнесена комиссия по транзакции
             * 1 — за счет интернет-магазина;
             * 2 — за счет клиента;
             */
            'commission' => 1,

            /*
             * https://order.prostoplateg.kz/sale.php
             */
            'pay_gate_url' => 'http://prostoplateg.kz/salekz.php',
        ],
        'paybox' => [
            'handler' => PayboxDriver::class,
            'currency' => 'KZT',
            'encoding' => 'UTF-8',
            'merchant_id' => env('PAYBOX_MERCHANT_ID', 1),
            'secret' => env('PAYBOX_SECRET', null),
        ],
        'spay' => [
            'handler' => SpayDriver::class,
            'MERCHANT_ID' => env('SPAY_MERCHANT_ID', 1),

            /*
             * Типы оплаты:
             * card       Visa / MasterCard
             * webmoney   WebMoney (WMK)
             * webmoney_z WebMoney (WMZ)
             * qiwi       Qiwi-кошелек
             * w1         WalletOne
             * yandex     Yandex.Деньги
             * ekzt       E-KZT
             * btc        BitCoin
             * onay       Карты ONAY
             */
            'PAYMENT_TYPE' => 'card',
            'secret_key' => env('SPAY_SECRET_KEY', '123'),
        ],
        'kaspi' => [
            'handler' => KaspiDriver::class,
        ],
        'cyberplat' => [
            'handler' => CyberplatDriver::class,
        ],
    ],
    'hooks' => [
        'approve' => [
            //'after_validation' => [\App\Http\Controllers\PaymentController::class, 'approvePayment'],
            'after_validation' => [],
        ],
    ],
    'routes' => [
        'backlink' => env('APP_URL'),
        'failure_backlink' => env('APP_URL'),
    ],
];
