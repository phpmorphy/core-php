<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;

// SecretKey from Seed

$seed = random_bytes(32);
$secKey = SecretKey::fromSeed($seed);
$bytes = $secKey->getBytes();

echo 'SecKey: ', base64_encode($bytes), PHP_EOL;

// SecretKey from bytes

$secKey = new SecretKey($bytes);
$pubKey = $secKey->getPublicKey();

echo 'PubKey: ', base64_encode($pubKey->getBytes()), PHP_EOL;

// Sign

$message = 'Hello World';
$signature = $secKey->sign($message);

echo 'Signature: ', base64_encode($signature), PHP_EOL;

// Verify

$verify = $pubKey->verifySignature($signature, $message);

echo 'Verify: ', $verify ? 'true' : 'false', PHP_EOL;
