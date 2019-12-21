# Lara-Tenge [Work-In-Progress]
 [![Build Status](https://travis-ci.org/Lootjs/lara-tenge.svg?branch=dev)](https://travis-ci.org/Lootjs/lara-tenge) ![GitHub repo size](https://img.shields.io/github/repo-size/lootjs/lara-tenge) ![GitHub closed pull requests](https://img.shields.io/github/issues-pr-closed/lootjs/lara-tenge) [![StyleCI](https://github.styleci.io/repos/225604318/shield?branch=dev)](https://github.styleci.io/repos/225604318) 
 ---
Система оплаты для Laravel. Из коробки поддерживаются казахстанские платежки
## Requirements
- Laravel 5.8
- php 7.0
## Install
- Install the package: ``composer require loot/lara-tenge``
- Run ``php artisan vendor:publish --provider=Loot\Tenge\ServiceProvider``
- Run ``php artisan migrate``
- Write code, that approves payment, and set it in config/tenge.php: hooks.approve.after_validation
- Add ignoring for lara-tenge routes: in your VerifyCsrfToken middleware
```php
protected $except = [
    'lara-tenge/*',
];
````
## Documentation
[Russian](docs/ru/install.md)  | English
## Todo
- [x] Epay.kz
- [x] Prostoplateg
- [x] Paybox.kz
- [x] WalletOne.com
- [x] Spay.kz 
- [ ] kassa nova
- [ ] Kazpost
- [ ] processing
- [ ] Kaspi
- [ ] Cyberplat
- [ ] wooppay
- [ ] Tarlan Payments
