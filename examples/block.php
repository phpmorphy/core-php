<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Block\Block;

// Create Block

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$recipient = Address::fromKey($secKey)->setPrefix('www');
$value = 42;

$trx = new Transaction();
$trx->setVersion(Transaction::BASIC)
    ->setSender($sender)
    ->setRecipient($recipient)
    ->setValue($value)
    ->sign($secKey);

$blk = new Block();
$blk->appendTransaction($trx)
    ->sign($secKey);

echo 'Block: ', $blk->getBase64(), PHP_EOL;


// Parse Block

$blk2 = Block::fromBase64($blk->getBase64());

echo 'Verify: ', $blk2->verify() ? 'true' : 'false', PHP_EOL;
echo 'Hash:   ', bin2hex($blk2->getHeader()->getHash()), PHP_EOL;
echo 'Merkle: ', bin2hex($blk2->getHeader()->getMerkleRootHash()), PHP_EOL;
