# umi-core-php
UMI Core PHP Library

### Install

    composer require umi-top/umi-core-php

### Example
```php
<?php declare(strict_types=1);

use UmiTop\UmiCore\Address;
use UmiTop\UmiCore\Transaction;

require __DIR__ . '/vendor/autoload.php';

$mnemonic =
    'hen faculty attract curve liquid include void cereal task sibling decorate dwarf ' .
    'brief sibling false diagram open parade aware real mention theme session evoke';

$sender = Address::fromMnemonic($mnemonic);
$recipient = Address::fromBech32('umi1qqqnau6tregpsvew37qvwjd448j79j4m8pzk4ydzjwsvuqev4vm975sjtgw6g');

var_dump($sender->toBech32());   // umi1qqqumpt2jchfa63qc0jxztud7twd5x89ya49h58sw34hdls8kl3a60cl4qmrf

$trx = (new Transaction())
    ->withSender($sender)
    ->withRecipient($recipient)
    ->withValue(gmp_init(100500))
    ->signWithMnemonic($mnemonic);

var_dump($trx->verify());         // true

var_dump($trx->toHex());          // 010001cd856a962e9eea20c3e4612f8df2dcda18e5276a5bd0...

```