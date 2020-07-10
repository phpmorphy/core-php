<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Address\Address;

// Address from Bech32

$bech32 = 'umi18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5s6rxnf6';
$address = Address::fromBech32($bech32);

echo 'From bech32: ', $address->toBech32(), PHP_EOL;

// Address from Key

$secKey = SecretKey::fromSeed(random_bytes(32));
$address = Address::fromKey($secKey);
$bytes = $address->toBytes();

echo 'From SecKey: ', $address->toBech32(), PHP_EOL;

// Set prefix

$address = Address::fromBytes($bytes);
$address->setPrefix('aaa');

echo 'With prefix: ', $address->toBech32(), PHP_EOL;
