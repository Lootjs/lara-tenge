<?php

return [
    'drivers' => [
        'epay' => [
            'secret_key' => env('EPAY_SECRET_KEY'),
            'cert_file' => '/path/to/cert',
            'handler' => \Loot\Tenge\EpayDriver::class,
        ],
        'prostoplateg' => [
            'handler' => \Loot\Tenge\ProstoplategDriver::class,
        ],
        'kaspi' => [
            'handler' => \Loot\Tenge\KaspiDriver::class,
        ],
        'cyberplat' => [
            'handler' => \Loot\Tenge\CyberplatDriver::class,
        ],
    ]
];
