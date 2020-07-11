<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Address\Address;

// Address from bytes

$bytes = "\x00\x00" . random_bytes(32);
$address = Address::fromBytes($bytes);

echo 'From bytes: ', $address->getBech32(), PHP_EOL;


// Address from Bech32

$bech32 = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6';
$address = Address::fromBech32($bech32);

echo 'From bech32: ', $address->getBech32(), PHP_EOL;


// Address from PubKey

$pubKey = new PublicKey(random_bytes(32));
$address = Address::fromKey($pubKey);

echo 'From PubKey: ', $address->getBech32(), PHP_EOL;


// Change prefix

$address = new Address();
$address->setBech32('umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6');
$address->setPrefix('aaa');

echo 'With prefix: ', $address->getBech32(), PHP_EOL;
