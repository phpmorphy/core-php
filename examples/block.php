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

$trx = new Transaction();
$trx->setVersion(Transaction::BASIC)
    ->setSender($sender)
    ->setRecipient($recipient)
    ->setValue(42)
    ->sign($secKey);

$blk = new Block();
$blk->appendTransaction($trx)
    ->setTimestamp(time())
    ->sign($secKey);

echo 'Block: ', $blk->toBase64(), PHP_EOL;

// Parse Block

$blk2 = Block::fromBase64($blk->toBase64());