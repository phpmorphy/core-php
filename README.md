# umi-core-php
UMI Core PHP Library

### Install

    composer require umi-top/umi-core-php

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
    ->setValue(gmp_init('18446744073709551615'))
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
