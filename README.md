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
        - [Установить префикс адреса](#установить-префикс-адреса)

    -   [Транзакции](#транзакции)
        - [Отправить монеты](#отправить-монеты)
        - [Создать структуру](#создать-структуру)
        - [Обновить настройки структуры](#обновить-настройки-структуры)
        - [Установить адрес для начисления профита](#установить-адрес-для-начисления-профита)
        - [Установить адрес для перевода комиссии](#установить-адрес-для-перевода-комиссии)
        - [Активировать транзитный адрес](#активировать-транзитный-адрес)
        - [Деактивировать транзитный адрес](#деактивировать-транзитный-адрес)

    -   [Блоки](#блоки)
        - Создать и подписать блок
        - Распарсить блок

-   [Лицензия](#лицензия)

## Введение

## Установка

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

Для примера будем использовать библиотеку [bip39](https://www.npmjs.com/package/bip39):

```javascript
// npm install bip39

const bip39 = require('bip39')
const mnemonic = bip39.generateMnemonic(256)
const seed = bip39.mnemonicToSeedSync(mnemonic)
```

### Ключи

В UMI применяется Ed25519 ([rfc8032](https://tools.ietf.org/html/rfc8032)) —
схема подписи [EdDSA](https://ru.wikipedia.org/wiki/EdDSA) использующая
SHA-512 и Curve25519. 

#### Ключи из seed'а

Seed может быть любой длины, включая нулевую.
Оптимальным вариантом является длина 32 байта (256 бит).

```javascript
const seed = new Uint8Array(32)
const secretKey = umi.SecretKey.fromSeed(seed)
const publicKey = secretKey.getPublicKey()
```

#### Подписать сообщение

В метод `SecretKey#sign()` необходимо передать массив байтов, поэтому если
требуется подписать текстовое сообщение его нужно преобразовать: 
```javascript
const message = new TextEncoder().encode('Hello World')
const signature = secretKey.sign(message)
```

#### Проверить подпись

Метод `PublicKey#verifySignature()` принимает массив байтов, поэтому если
подпись передается в текстовой кодировке ее необходимо декодировать.  
Пример для Node.js:

```javascript
const address = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6'
const message = new TextEncoder().encode('Hello World')
const signature = Buffer.from('Jbi9YfwLcxiTMednl/wTvnSzsPP9mV9Bf2vvZytP87oyg1p1c9ZBkn4gNv15ZHwEFv3bVYlowgyIKmMwJLjJCw==', 'base64')
const ver = umi.Address.fromBech32(address).getPublicKey().verifySignature(signature, message)
```

### Адреса

UMI использует адреса в формате Bech32
([bip173](https://github.com/bitcoin/bips/blob/master/bip-0173.mediawiki))
длиной 62 символа и трёхбуквенный префикс.  
Специальным случаем являются Genesis-адреса, существующие только
в Genesis-блоке, такие адреса имеют длину 65 символов
и всегда имеют префикс `genesis`.

#### Адрес в формате Bech32

Создать адрес из строки Bech32 можно используя статический метод `Address.fromBech32()`
и конвертировать обратно с помощью `Address#toBech32()`:

```javascript
const bech32 = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6'
const address = umi.Address.fromBech32(bech32)
console.log(address.toBech32())
```
 
#### Адрес из приватного или публичного ключа

Статический метод `Address.fromKey()` создает адрес из приватного
или публичного ключа:

```javascript
const seed = new Uint8Array(32)
const secKey = umi.SecretKey.fromSeed(seed)
const pubKey = secKey.getPublicKey()
const address1 = umi.Address.fromKey(secKey)
const address2 = umi.Address.fromKey(pubKey)
```

#### Установить префикс адреса

По умолчанию адреса имеют префикс `umi`.
Изменить префикс можно при помощи метода `Address#setPrefix()`:

```javascript
const bech32 = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6'
const address = umi.Address.fromBech32(bech32).setPrefix('aaa')
console.log(address.toBech32())
```

### Транзакции

#### Отправить монеты

```javascript
const secKey = umi.SecretKey.fromSeed(new Uint8Array(32))
const sender = umi.Address.fromKey(secKey).setPrefix('umi')
const recipient = umi.Address.fromKey(secKey).setPrefix('aaa')
const tx = new umi.Transaction()
  .setVersion(umi.Transaction.Basic)
  .setSender(sender)
  .setRecipient(recipient)
  .setValue(42)
  .sign(secKey)

console.log(tx.verify())
console.log(tx.toBase64())
```

#### Создать структуру

```javascript
const secKey = umi.SecretKey.fromSeed(new Uint8Array(32))
const sender = umi.Address.fromKey(secKey).setPrefix('umi')
const tx = new umi.Transaction()
  .setVersion(umi.Transaction.UpdateStructure)
  .setSender(sender)
  .setPrefix('aaa')
  .setName('🙂')
  .setProfitPercent(500)
  .setFeePercent(2000)
  .sign(secKey)

console.log(tx.verify())
console.log(tx.toBase64())
```

#### Обновить настройки структуры

Необходимо задать все поля, даже если они не изменились:

```javascript
const secKey = umi.SecretKey.fromSeed(new Uint8Array(32))
const sender = umi.Address.fromKey(secKey).setPrefix('umi')
const tx = new umi.Transaction()
  .setVersion(umi.Transaction.UpdateStructure)
  .setSender(sender)
  .setPrefix('aaa')
  .setName('🙂')
  .setProfitPercent(500)
  .setFeePercent(2000)
  .sign(secKey)

console.log(tx.verify())
console.log(tx.toBase64())
```

#### Установить адрес для начисления профита

```javascript
const secKey = umi.SecretKey.fromSeed(new Uint8Array(32))
const sender = umi.Address.fromKey(secKey).setPrefix('umi')
const newPrf = umi.Address.fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66')
const tx = new umi.Transaction()
  .setVersion(umi.Transaction.UpdateProfitAddress)
  .setSender(sender)
  .setRecipient(newPrf)
  .sign(secKey)

console.log(tx.verify())
console.log(tx.toBase64())
```

#### Установить адрес для перевода комиссии

```javascript
const secKey = umi.SecretKey.fromSeed(new Uint8Array(32))
const sender = umi.Address.fromKey(secKey).setPrefix('umi')
const newPrf = umi.Address.fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66')
const tx = new umi.Transaction()
  .setVersion(umi.Transaction.UpdateProfitAddress)
  .setSender(sender)
  .setRecipient(newPrf)
  .sign(secKey)

console.log(tx.verify())
console.log(tx.toBase64())
```

#### Активировать транзитный адрес

```javascript
const secKey = umi.SecretKey.fromSeed(new Uint8Array(32))
const sender = umi.Address.fromKey(secKey).setPrefix('umi')
const transit = umi.Address.fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66')
const tx = new umi.Transaction()
  .setVersion(umi.Transaction.CreateTransitAddress)
  .setSender(sender)
  .setRecipient(transit)
  .sign(secKey)

console.log(tx.verify())
console.log(tx.toBase64())
```

#### Деактивировать транзитный адрес

```javascript
const secKey = umi.SecretKey.fromSeed(new Uint8Array(32))
const sender = umi.Address.fromKey(secKey).setPrefix('umi')
const transit = umi.Address.fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66')
const tx = new umi.Transaction()
  .setVersion(umi.Transaction.DeleteTransitAddress)
  .setSender(sender)
  .setRecipient(transit)
  .sign(secKey)

console.log(tx.verify())
console.log(tx.toBase64())
```

### Блоки

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