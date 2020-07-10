<?php

declare(strict_types=1);

// composer require bitwasp/bitcoin

include __DIR__ . '/../vendor/autoload.php';

use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Address\Address;

$mnemonic = 'mix tooth like stock powder emerge protect index magic';

$seed = (new Bip39SeedGenerator())->getSeed($mnemonic)->getBinary();

$address = Address::fromKey(SecretKey::fromSeed($seed));

echo $address->toBech32(), PHP_EOL;
