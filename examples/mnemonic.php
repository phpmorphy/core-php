<?php

/**
 * composer require bitwasp/bitcoin
 *
 * @see https://github.com/Bit-Wasp/bitcoin-php
 */

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Address\Address;

$mnemonic = 'mix tooth like stock powder emerge protect index magic';

$bip39 = new Bip39SeedGenerator();
$seed = $bip39->getSeed($mnemonic)->getBinary();

$address = Address::fromKey(SecretKey::fromSeed($seed));

echo $address->getBech32(), PHP_EOL;
