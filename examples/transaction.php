<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Transaction\Transaction;

// Basic

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$recipient = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');
$trx1 = (new Transaction())
    ->setVersion(Transaction::BASIC)
    ->setSender($sender)
    ->setRecipient($recipient)
    ->setValue(42)
    ->sign($secKey);

echo 'Ver1: ', $trx1->toBase64(), PHP_EOL;

// Create Structure

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$trx2 = (new Transaction())
    ->setVersion(Transaction::CREATE_STRUCTURE)
    ->setSender($sender)
    ->setPrefix('aaa')
    ->setName('The best struct 🙂')
    ->setProfitPercent(500)
    ->setFeePercent(2000)
    ->sign($secKey);

echo 'Ver2: ', $trx2->toBase64(), PHP_EOL;

// Update Structure

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');
$trx3 = (new Transaction())
    ->setVersion(Transaction::UPDATE_STRUCTURE)
    ->setSender($sender)
    ->setPrefix('aaa')
    ->setName('The best struct 🙂')
    ->setProfitPercent(500)
    ->setFeePercent(1000)
    ->sign($secKey);

echo 'Ver3: ', $trx3->toBase64(), PHP_EOL;

// Update Profit Address

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$newProfit = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');
$trx4 = (new Transaction())
    ->setVersion(Transaction::UPDATE_PROFIT_ADDRESS)
    ->setSender($sender)
    ->setRecipient($newProfit)
    ->sign($secKey);

echo 'Ver4: ', $trx4->toBase64(), PHP_EOL;

// Update Fee Address

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$newFee = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');
$trx5 = (new Transaction())
    ->setVersion(Transaction::UPDATE_FEE_ADDRESS)
    ->setSender($sender)
    ->setRecipient($newFee)
    ->sign($secKey);

echo 'Ver5: ', $trx5->toBase64(), PHP_EOL;

// Create Transit Address

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$newTransit = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');
$trx6 = (new Transaction())
    ->setVersion(Transaction::CREATE_TRANSIT_ADDRESS)
    ->setSender($sender)
    ->setRecipient($newTransit)
    ->sign($secKey);

echo 'Ver6: ', $trx6->toBase64(), PHP_EOL;

// Delete Transit Address

$secKey = SecretKey::fromSeed(random_bytes(32));
$sender = Address::fromKey($secKey)->setPrefix('umi');
$oldTransit = Address::fromBech32('aaa18d4z00xwk6jz6c4r4rgz5mcdwdjny9thrh3y8f36cpy2rz6emg5svsuw66');
$trx7 = (new Transaction())
    ->setVersion(Transaction::CREATE_TRANSIT_ADDRESS)
    ->setSender($sender)
    ->setRecipient($oldTransit)
    ->sign($secKey);

echo 'Ver7: ', $trx7->toBase64(), PHP_EOL;
