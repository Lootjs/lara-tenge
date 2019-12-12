<?php

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
            'handler' => \Loot\Tenge\Drivers\Epay\EpayDriver::class,

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
        'prostoplateg' => [
            'handler' => \Loot\Tenge\Drivers\ProstoplategDriver::class,
        ],
        'kaspi' => [
            'handler' => \Loot\Tenge\Drivers\KaspiDriver::class,
        ],
        'cyberplat' => [
            'handler' => \Loot\Tenge\Drivers\CyberplatDriver::class,
        ],
    ]
];
