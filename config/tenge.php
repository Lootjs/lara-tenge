<?php

return [
    'table_name' => 'tenge_payments',
    'drivers' => [
        'epay' => [
            'secret_key' => env('EPAY_SECRET_KEY'),
            'cert_file' => '/path/to/cert',
            'handler' => \Loot\Tenge\Drivers\Epay\EpayDriver::class,
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
