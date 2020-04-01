# umi-core-php
UMI Core PHP Library

### Install

    composer require umi-top/umi-core-php

### Example
```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$mnemonic = 'hen faculty attract curve liquid include void cereal task sibling decorate dwarf brief sibling false diagram open parade aware real mention theme session evoke';
$adr = \UmiTop\UmiCore\Address::fromMnemonic($mnemonic)->getBech32();

var_dump($adr); // umi1qqqumpt2jchfa63qc0jxztud7twd5x89ya49h58sw34hdls8kl3a60cl4qmrf
```