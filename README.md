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
-   [Введение](#введение)

-   [Установка](#установка)
    - [Composer](#composer)

-   [Примеры](#примеры)
    -   [Мнемоники](#мнемоники)
        - [Seed из мнемонической фразы](#seed-из-мнемонической-фразы)

    -   [Ключи](#ключи)
        - [Ключи из seed'а](#ключи-из-seed'а)
        - [Подписать сообщение](#подписать-сообщение)
        - [Проверить подпись](#проверить-подпись)

    -   [Адреса](#адреса)
        - [Адрес в формате Bech32](#адрес-в-формате-bech32)
        - [Адрес из приватного или публичного ключа](#адрес-из-приватного-или-публичного-ключа)
        - [Префикс](#префикс)

    -   [Транзакции](#транзакции)
        - [Перевести монеты](#перевести-монеты)
        - [Создать структуру](#создать-структуру)
        - [Обновить настройки структуры](#обновить-настройки-структуры)
        - [Установить адрес для начисления профита](#установить-адрес-для-начисления-профита)
        - [Установить адрес для перевода комиссии](#установить-адрес-для-перевода-комиссии)
        - [Активировать транзитный адрес](#активировать-транзитный-адрес)
        - [Деактивировать транзитный адрес](#деактивировать-транзитный-адрес)
        - [Отправить транзакцию в сеть](#отправить-транзакцию-в-сеть)

    -   [Блоки](#блоки)
        - [Создать и подписать блок](#cоздать-и-подписать-блок)
        - [Распарсить блок](#распарсить-блок)

-   [Лицензия](#лицензия)

## Введение

Для работы библиотеки требуются 64-битная версия PHP >= 5.4 и стандартное
расширение [hash](https://www.php.net/manual/en/function.hash.php).

## Установка

Библиотека опубликована в репозитории [Packagist](https://packagist.org) и может
быть установлена с помощью менеджера зависимостей [Composer](https://getcomposer.org).

### Composer

```bash
composer require umi-top/umi-core-php
```

## Примеры

### Мнемоники

UMI не накладывает никаких ограничений на способ генерации и хранения приватных
ключей, предоставляя полную свободу действий разработчикам приложений.

Использование [bip39](https://github.com/bitcoin/bips/blob/master/bip-0039.mediawiki)
для генерации мнемонических фраз носит исключительно рекомендательный характер.

#### Seed из мнемонической фразы

Для примера будем использовать библиотеку [bitcoin-php](https://github.com/Bit-Wasp/bitcoin-php):

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Address\Address;

$mnemonic = 'mix tooth like stock powder emerge protect index magic';

$bip39 = new Bip39SeedGenerator();
$seed = $bip39->getSeed($mnemonic)->getBinary();

$address = Address::fromKey(SecretKey::fromSeed($seed));

echo $address->getBech32(), PHP_EOL;
```

### Ключи

В UMI применяется [Ed25519](https://ed25519.cr.yp.to)
([RFC 8032](https://tools.ietf.org/html/rfc8032)) —
схема подписи [EdDSA](https://ru.wikipedia.org/wiki/EdDSA) использующая
[SHA-512](https://en.wikipedia.org/wiki/SHA-2)
и [Curve25519](https://en.wikipedia.org/wiki/Curve25519). 

#### Ключи из seed'а

Seed может быть любой длины, включая нулевую.
Оптимальным вариантом является длина 32 байта (256 бит).

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;

$seed = random_bytes(32);
$secKey = SecretKey::fromSeed($seed);
$bytes = $secKey->getBytes();
```

#### Подписать сообщение

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;

$secKey = SecretKey::fromSeed(random_bytes(32));
$message = 'Hello World';
$signature = $secKey->sign($message);

echo base64_encode($signature), PHP_EOL;
```

#### Проверить подпись

```php
<?php declare(strict_types=1);
      
include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;

$address = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6';
$message = 'Hello World';
$signature = base64_decode(
    'Jbi9YfwLcxiTMednl/wTvnSzsPP9mV9Bf2vvZytP87oyg1p1c9ZBkn4gNv15ZHwEFv3bVYlowgyIKmMwJLjJCw=='
);
$pubKey = Address::fromBech32($address)->getPublicKey();
$isValid = $pubKey->verifySignature($signature, $message);

var_dump($isValid);
```

### Адреса

UMI использует адреса в формате Bech32
([bip173](https://github.com/bitcoin/bips/blob/master/bip-0173.mediawiki))
длиной 62 символа и трёхбуквенный префикс.  
Специальным случаем являются Genesis-адреса, существующие только
в Genesis-блоке, такие адреса имеют длину 65 символов
и всегда имеют префикс `genesis`.

#### Адрес в формате Bech32

Создать адрес из строки Bech32 можно используя статический метод `Address::fromBech32()`
и конвертировать обратно с помощью `Address->toBech32()`:

```php
<?php declare(strict_types=1);
      
include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;

$bech32 = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6';
$address = Address::fromBech32($bech32);

echo $address->toBech32(), PHP_EOL;
```
 
#### Адрес из приватного или публичного ключа

Статический метод `Address::fromKey()` создает адрес из приватного
или публичного ключа:

```php
<?php declare(strict_types=1);
      
include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Key\PublicKey;

$secKey = SecretKey::fromSeed(random_bytes(32));
$address1 = Address::fromKey($secKey);

echo $address1->getBech32(), PHP_EOL;

$pubKey = new PublicKey(random_bytes(32));
$address2 = Address::fromKey($pubKey);

echo $address2->getBech32(), PHP_EOL;
```

#### Префикс

По умолчанию адреса имеют префикс `umi`.
Изменить префикс можно при помощи метода `Address->setPrefix()`:

```php
<?php declare(strict_types=1);
      
include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;

$bech32 = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6';
$address = Address::fromBech32($bech32)->setPrefix('aaa');

echo $bech32, PHP_EOL;
echo $address->getBech32(), PHP_EOL;
```

### Транзакции

#### Перевести монеты

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$recipient = Address::fromKey($secKey)->setPrefix('aaa');
$value = 42;

$trx = new Transaction();
$trx->setVersion(Transaction::BASIC)
    ->setSender($sender)
    ->setRecipient($recipient)
    ->setValue($value)
    ->sign($secKey);

echo 'isValid: ', ($trx->verify() ? 'true' : 'false'), PHP_EOL;
echo 'base64:  ', base64_encode($trx->getBytes()), PHP_EOL;
```

#### Создать структуру

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');

$trx = new Transaction();
$trx->setVersion(Transaction::CREATE_STRUCTURE)
    ->setSender($sender)
    ->setPrefix('aaa')
    ->setName('🙂')
    ->setProfitPercent(500)
    ->setFeePercent(2000)
    ->sign($secKey);

echo 'isValid: ', ($trx->verify() ? 'true' : 'false'), PHP_EOL;
echo 'base64:  ', base64_encode($trx->getBytes()), PHP_EOL;
```

#### Обновить настройки структуры

Необходимо задать все поля, даже если они не изменились:

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');

$trx = new Transaction();
$trx->setVersion(Transaction::UPDATE_STRUCTURE)
    ->setSender($sender)
    ->setPrefix('aaa')
    ->setName('🙂')
    ->setProfitPercent(500)
    ->setFeePercent(2000)
    ->sign($secKey);

echo 'isValid: ', ($trx->verify() ? 'true' : 'false'), PHP_EOL;
echo 'base64:  ', base64_encode($trx->getBytes()), PHP_EOL;
```

#### Установить адрес для начисления профита

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$newPrf = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');

$trx = new Transaction();
$trx->setVersion(Transaction::UPDATE_PROFIT_ADDRESS)
    ->setSender($sender)
    ->setRecipient($newPrf)
    ->sign($secKey);

echo 'isValid: ', ($trx->verify() ? 'true' : 'false'), PHP_EOL;
echo 'base64:  ', base64_encode($trx->getBytes()), PHP_EOL;
```

#### Установить адрес для перевода комиссии

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$newFee = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');

$trx = new Transaction();
$trx->setVersion(Transaction::UPDATE_FEE_ADDRESS)
    ->setSender($sender)
    ->setRecipient($newFee)
    ->sign($secKey);

echo 'isValid: ', ($trx->verify() ? 'true' : 'false'), PHP_EOL;
echo 'base64:  ', base64_encode($trx->getBytes()), PHP_EOL;
```

#### Активировать транзитный адрес

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$transit = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');

$trx = new Transaction();
$trx->setVersion(Transaction::CREATE_TRANSIT_ADDRESS)
    ->setSender($sender)
    ->setRecipient($transit)
    ->sign($secKey);

echo 'isValid: ', ($trx->verify() ? 'true' : 'false'), PHP_EOL;
echo 'base64:  ', base64_encode($trx->getBytes()), PHP_EOL;
```

#### Деактивировать транзитный адрес

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$transit = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');

$trx = new Transaction();
$trx->setVersion(Transaction::DELETE_TRANSIT_ADDRESS)
    ->setSender($sender)
    ->setRecipient($transit)
    ->sign($secKey);

echo 'isValid: ', ($trx->verify() ? 'true' : 'false'), PHP_EOL;
echo 'base64:  ', base64_encode($trx->getBytes()), PHP_EOL;
```

#### Отправить транзакцию в сеть

Пример с использованием расширений
[cURL](https://www.php.net/manual/en/book.curl.php)
и [JSON](https://www.php.net/manual/en/book.json.php):

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Transaction\Transaction;

$trx = new Transaction();

$payload = json_encode(
    [
        'jsonrpc' => '2.0',
        'id' => '',
        'method' => 'sendTransaction',
        'params' => [
            'base64' => base64_encode($trx->getBytes())
        ]
    ]
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://testnet.umi.top/json-rpc");
curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);

curl_close($ch);

echo $response, PHP_EOL;
```

### Блоки

#### Создать и подписать блок

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Block\Block;

$key = SecretKey::fromSeed(random_bytes(32));

$blk = new Block();
$blk->getHeader()->setPreviousBlockHash(random_bytes(32));

for ($i = 0; $i < 8; $i++) {
    $trx = new Transaction();
    $trx->setVersion($i)->sign($key);

    $blk->appendTransaction($trx);
}

$blk->sign($key);

echo base64_encode($blk->getBytes()), PHP_EOL;
````

#### Распарсить блок

```php
<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Block\Block;

$base64 = 'AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALrlbZ39raFUA8r896UgeKppkfwULfPMqU5SOxqJmOAtfCqcuAAFvNXzt5N4'
    . 'tj+cmZt4NPbWh1PAD9bXP8iJPM+QAuVDx2yD+iEdfwhDtiyuqU/PBFMLAqUQazv4xwvLcT12jhegBuj0Ri2EzWpZE+EonywsJkX5fhVWV/Y'
    . 'Fo7JFoW5YJkwwBValvNXzt5N4tj+cmZt4NPbWh1PAD9bXP8iJPM+QAuVDx2173bzV87eTeLY/nJmbeDT21odTwA/W1z/IiTzPkALlQ8dsAA'
    . 'AAAAAAAKgAAACUgKU4ByUyp77RER3NDPns8AgOzFkIaI9H5LDtozNZLrjlyOlRxHU+AoAuunUmVAXg4lw0B7zSLalqV/F2PLCpoPKVdAAA=';

$blk = Block::fromBytes(base64_decode($base64));

echo 'Prv Hash:   ', bin2hex($blk->getHeader()->getPreviousBlockHash()), PHP_EOL;
echo 'Blk Hash:   ', bin2hex($blk->getHeader()->getHash()), PHP_EOL;
echo 'Blk Merkle: ', bin2hex($blk->getHeader()->getMerkleRootHash()), PHP_EOL;
echo 'Transactions:', PHP_EOL, PHP_EOL;

foreach ($blk as $idx => $trx) {
    echo 'tx index:   ', $idx, PHP_EOL;
    echo 'tx type:    ', $trx->getVersion(), PHP_EOL;
    echo 'tx hash:    ', bin2hex($trx->getHash()), PHP_EOL;
    echo 'sender:     ', $trx->getSender()->getBech32(), PHP_EOL;

    switch ($trx->getVersion()) {
        case Transaction::GENESIS:
        case Transaction::BASIC:
            echo 'recipient:  ', $trx->getRecipient()->getBech32(), PHP_EOL;
            echo 'value:      ', number_format($trx->getValue() / 100, 2), ' UMI', PHP_EOL;
            break;
        case Transaction::CREATE_STRUCTURE:
        case Transaction::UPDATE_STRUCTURE:
            echo 'prefix:     ', $trx->getPrefix(), PHP_EOL;
            echo 'name:       ', $trx->getName(), PHP_EOL;
            echo 'profit (%): ', number_format($trx->getProfitPercent() / 100, 2), PHP_EOL;
            echo 'fee (%):    ', number_format($trx->getFeePercent() / 100, 2), PHP_EOL;
            break;
        case Transaction::UPDATE_PROFIT_ADDRESS:
            echo 'new profit: ', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        case Transaction::UPDATE_FEE_ADDRESS:
            echo 'new fee:    ', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        case Transaction::CREATE_TRANSIT_ADDRESS:
            echo 'new transit:', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        case Transaction::DELETE_TRANSIT_ADDRESS:
            echo 'del transit:', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        default:
            echo 'unknown tx version', PHP_EOL;
    }

    echo PHP_EOL;
}
````

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