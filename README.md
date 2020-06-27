<h1 align="center">
  <a href="https://umi.top"><img src="./logo.svg" alt="UMI" width="200"></a>
  <br>
  UMI Core - PHP Library
  <br>
  <br>
</h1>

<p align="center">
  <!-- release    --><a href="https://github.com/umi-top/umi-core-php"><img alt="GitHub release (latest SemVer)" src="https://img.shields.io/github/v/release/umi-top/umi-core-php?sort=semver"></a>
  <!-- build      --><a href="https://travis-ci.org/umi-top/umi-core-php"><img alt="travis" src="https://img.shields.io/travis/umi-top/umi-core-php/master"></a>
  <!-- coverage   --><img alt="Coveralls github branch" src="https://img.shields.io/coveralls/github/umi-top/umi-core-php/master">
  <!-- code style --><a href="https://www.php-fig.org/psr/psr-12/"><img alt="PSR-12" src="https://img.shields.io/badge/code_style-PSR--12-green"></a>
  <!-- license    --><a href="https://github.com/umi-top/umi-core-php/blob/master/LICENSE"><img alt="GitHub" src="https://img.shields.io/github/license/umi-top/umi-core-php"></a>
  <!-- PGP        --><a href="https://keybase.io/umitop"><img alt="Keybase PGP" src="https://img.shields.io/keybase/pgp/umitop"></a>
  <br/><!-- master -->
  <!-- packagist  --><a href="https://packagist.org/packages/umi-top/umi-core-php"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/umi-top/umi-core-php"></a>
  <!-- php support--><img alt="Packagist PHP Version Support" src="https://img.shields.io/packagist/php-v/umi-top/umi-core-php">
  <!-- downloads  --><img alt="Packagist Downloads" src="https://img.shields.io/packagist/dm/umi-top/umi-core-php">
  <br/><!-- php70 -->
  <!-- packagist  --><a href="https://packagist.org/packages/umi-top/umi-core-php"><img alt="Packagist Version" src="https://img.shields.io/badge/packagist-v1.0.70-orange"></a>
  <!-- php support--><img alt="Packagist PHP Version Support (specify version)" src="https://img.shields.io/packagist/php-v/umi-top/umi-core-php/v0.9.2">
  <br/><!-- php53 -->
  <!-- packagist  --><a href="https://packagist.org/packages/umi-top/umi-core-php"><img alt="Packagist Version" src="https://img.shields.io/badge/packagist-v1.0.53-orange"></a>
  <!-- php support--><img alt="Packagist PHP Version Support (specify version)" src="https://img.shields.io/packagist/php-v/umi-top/umi-core-php/v0.9.2">
</p>

## Оглавление
-   Введение

-   [Установка](#установка)
    - Composer

-   [Примеры](#примеры)
    -   [Ключи](#ключи)
        - Приватный ключ из seed
        - Приватный ключ из мнемонической фразы
        - Создание и проверка цифровой подписи

    -   [Адреса](#адреса)
        - Адреса в формате Bech32
        - Адрес из приватного или публичного ключа
        - Установка и смена префикса адреса

    -   [Транзакции](#транзакции)
        - Отправка монет
        - Создание структуры
        - Обновление настроек структуры
        - Установка адреса для начисления профита
        - Установка адреса для перевода комиссии
        - Активация транзитного адреса
        - Деактивация транзитного адреса

    -   [Блоки](#блоки)
        - Создание и подпись блоков
        - Парсинг блоков

-   [Лицензия](#лицензия)

## Введение

## Установка
### npm
```bash
composer require umi-top/umi-core-php
```

### Addresses
Create Address from Mnemonic
```php
<?php declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKeyFactory;
use UmiTop\UmiCore\Address\AddressFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;

$mnemonic = 'mix tooth like stock powder emerge protect index magic';
$seed = (new Bip39SeedGenerator())->getSeed($mnemonic)->getBinary();
$secKey = SecretKeyFactory::fromSeed($seed);
$pubKey = $secKey->getPublicKey();
$address = AddressFactory::fromPublicKey($pubKey);

echo $address->toBech32(), PHP_EOL; // umi1u3dam33jaf64z4s008g7su62j4za72ljqff9dthsataq8k806nfsgrhdhg
```
Change Address Prefix
```php
<?php declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

use UmiTop\UmiCore\Address\AddressFactory;

$bech32 = 'umi1kzsn227tel8aj5p5upaecz7e72k3k8w0lel3lffrnvg3d5rkh5uq3a8598';
$address = AddressFactory::fromBech32($bech32);
$address->setPrefix('sss');

echo $address->toBech32(), PHP_EOL; // sss1kzsn227tel8aj5p5upaecz7e72k3k8w0lel3lffrnvg3d5rkh5uqv9z0az
```

### Transactions
Basic Transaction
```php
<?php
declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKeyFactory;
use UmiTop\UmiCore\Address\AddressFactory;
use UmiTop\UmiCore\Transaction\Transaction;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;


$mnemonic = 'mix tooth like stock powder emerge protect index magic';
$bech32 = 'xxx1hztcwh6rh63ftkw8y8cwt63n4256u3packsxh05wv5x5cpa79raqyf98d5';

$seed = (new Bip39SeedGenerator())->getSeed($mnemonic)->getBinary();
$secKey = SecretKeyFactory::fromSeed($seed);
$sender = AddressFactory::fromSecretKey($secKey);
$recipient = AddressFactory::fromBech32($bech32);

$tx1 = (new Transaction())
    ->setVersion(Transaction::BASIC)
    ->setSender($sender)
    ->setRecipient($recipient)
    ->setValue(18446744073709551615)
    ->sign($secKey);

$tx2 = new Transaction($tx1->toBytes());

var_dump(
    [
        'hash' => bin2hex($tx2->getHash()),
        'version' => $tx2->getVersion(),
        'sender' => $tx2->getSender()->toBech32(),
        'recipient' => $tx2->getRecipient()->toBech32(),
        'value' => gmp_strval($tx2->getValue()),
        'signature' => bin2hex($tx2->getSignature()),
        'verify' => $tx2->verify()
    ]
);
```
Create Structure
```php
<?php

declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKeyFactory;
use UmiTop\UmiCore\Address\AddressFactory;
use UmiTop\UmiCore\Transaction\Transaction;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;


$mnemonic = 'mix tooth like stock powder emerge protect index magic';
$bech32 = 'xxx1hztcwh6rh63ftkw8y8cwt63n4256u3packsxh05wv5x5cpa79raqyf98d5';

$seed = (new Bip39SeedGenerator())->getSeed($mnemonic)->getBinary();
$secKey = SecretKeyFactory::fromSeed($seed);

$tx1 = (new Transaction())
    ->setVersion(Transaction::CREATE_STRUCTURE)
    ->setSender(AddressFactory::fromSecretKey($secKey))
    ->setPrefix('www')
    ->setName('World Wide Web')
    ->setProfitPercent(456) // 4.56%
    ->setFeePercent(1234) // 12.34%
    ->sign($secKey);

$tx2 = new Transaction($tx1->toBytes());

var_dump(
    [
        'hash' => bin2hex($tx2->getHash()),
        'version' => $tx2->getVersion(),
        'sender' => $tx2->getSender()->toBech32(),
        'prefix' => $tx2->getPrefix(),
        'name' => $tx2->getName(),
        'profit' => $tx2->getProfitPercent(),
        'fee' => $tx2->getFeePercent(),
        'verify' => $tx2->verify()
    ]
);
```
Create Transit Address
```php
<?php

declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKeyFactory;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Address\AddressFactory;
use UmiTop\UmiCore\Transaction\Transaction;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;


$mnemonic = 'mix tooth like stock powder emerge protect index magic';
$seed = (new Bip39SeedGenerator())->getSeed($mnemonic)->getBinary();
$secKey = SecretKeyFactory::fromSeed($seed);

$sender = new Address();
$sender->setPublicKey($secKey->getPublicKey());

$address = new Address();
$address->fromBech32('www1hztcwh6rh63ftkw8y8cwt63n4256u3packsxh05wv5x5cpa79raq9g5cvs');

$tx1 = new Transaction();
$tx1->setVersion(Transaction::CREATE_TRANSIT_ADDRESS);
$tx1->setSender($sender);
$tx1->setRecipient($address);
$tx1->sign($secKey);

$tx2 = new Transaction($tx1->toBytes());

var_dump(
    [
        'hash' => bin2hex($tx2->getHash()),
        'version' => $tx2->getVersion(),
        'sender' => $tx2->getSender()->toBech32(),
        'prefix' => $tx2->getPrefix(),
        'address' => $tx2->getRecipient()->toBech32(),
        'verify' => $tx2->verify()
    ]
);
```

## Лицензия

```text
Лицензия MIT

Copyright © 2020 UMI

Данная лицензия разрешает лицам, получившим копию данного программного
обеспечения и сопутствующей документации (в дальнейшем именуемыми
«Программное обеспечение»), безвозмездно использовать Программное обеспечение
без ограничений, включая неограниченное право на использование, копирование,
изменение, слияние, публикацию, распространение, сублицензирование и/или
продажу копий Программного обеспечения, а также лицам, которым предоставляется
данное Программное обеспечение, при соблюдении следующих условий:

Указанное выше уведомление об авторском праве и данные условия должны быть
включены во все копии или значимые части данного Программного обеспечения.

ДАННОЕ ПРОГРАММНОЕ ОБЕСПЕЧЕНИЕ ПРЕДОСТАВЛЯЕТСЯ «КАК ЕСТЬ», БЕЗ КАКИХ-ЛИБО
ГАРАНТИЙ, ЯВНО ВЫРАЖЕННЫХ ИЛИ ПОДРАЗУМЕВАЕМЫХ, ВКЛЮЧАЯ ГАРАНТИИ ТОВАРНОЙ
ПРИГОДНОСТИ, СООТВЕТСТВИЯ ПО ЕГО КОНКРЕТНОМУ НАЗНАЧЕНИЮ И ОТСУТСТВИЯ НАРУШЕНИЙ,
НО НЕ ОГРАНИЧИВАЯСЬ ИМИ. НИ В КАКОМ СЛУЧАЕ АВТОРЫ ИЛИ ПРАВООБЛАДАТЕЛИ НЕ НЕСУТ
ОТВЕТСТВЕННОСТИ ПО КАКИМ-ЛИБО ИСКАМ, ЗА УЩЕРБ ИЛИ ПО ИНЫМ ТРЕБОВАНИЯМ, В ТОМ
ЧИСЛЕ, ПРИ ДЕЙСТВИИ КОНТРАКТА, ДЕЛИКТЕ ИЛИ ИНОЙ СИТУАЦИИ, ВОЗНИКШИМ ИЗ-ЗА
ИСПОЛЬЗОВАНИЯ ПРОГРАММНОГО ОБЕСПЕЧЕНИЯ ИЛИ ИНЫХ ДЕЙСТВИЙ С ПРОГРАММНЫМ
ОБЕСПЕЧЕНИЕМ. 
```