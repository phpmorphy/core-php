<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Block\Block;

// Create Block

$key = SecretKey::fromSeed(random_bytes(32));

$blk = new Block();
$blk->getHeader()->setPreviousBlockHash(random_bytes(32));

for ($i = 0; $i < 8; $i++) {
    $trx = new Transaction();
    $trx->setVersion($i)->sign($key);

    $blk->appendTransaction($trx);
}

$blk->sign($key);

echo 'Block: ', base64_encode($blk->getBytes()), PHP_EOL, PHP_EOL;


// Parse Block

$base64 = 'AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALrlbZ39raFUA8r896UgeKppkfwULfPMqU5SOxqJmOAtfCqcuAAFvNXzt5N4'
    . 'tj+cmZt4NPbWh1PAD9bXP8iJPM+QAuVDx2yD+iEdfwhDtiyuqU/PBFMLAqUQazv4xwvLcT12jhegBuj0Ri2EzWpZE+EonywsJkX5fhVWV/Y'
    . 'Fo7JFoW5YJkwwBValvNXzt5N4tj+cmZt4NPbWh1PAD9bXP8iJPM+QAuVDx2173bzV87eTeLY/nJmbeDT21odTwA/W1z/IiTzPkALlQ8dsAA'
    . 'AAAAAAAKgAAACUgKU4ByUyp77RER3NDPns8AgOzFkIaI9H5LDtozNZLrjlyOlRxHU+AoAuunUmVAXg4lw0B7zSLalqV/F2PLCpoPKVdAAA=';

$blk = Block::fromBytes(base64_decode($base64));

echo 'Prv Hash:   ', bin2hex($blk->getHeader()->getPreviousBlockHash()), PHP_EOL;
echo 'Blk Hash:   ', bin2hex($blk->getHeader()->getHash()), PHP_EOL;
echo 'Blk Merkle: ', bin2hex($blk->getHeader()->getMerkleRootHash()), PHP_EOL;
echo 'Transactions:', PHP_EOL, PHP_EOL;

foreach ($blk as $idx => $trx) {
    echo 'tx index:   ', $idx, PHP_EOL;
    echo 'tx type:    ', $trx->getVersion(), PHP_EOL;
    echo 'tx hash:    ', bin2hex($trx->getHash()), PHP_EOL;
    echo 'sender:     ', $trx->getSender()->getBech32(), PHP_EOL;

    switch ($trx->getVersion()) {
        case Transaction::GENESIS:
        case Transaction::BASIC:
            echo 'recipient:  ', $trx->getRecipient()->getBech32(), PHP_EOL;
            echo 'value:      ', number_format($trx->getValue() / 100, 2), ' UMI', PHP_EOL;
            break;
        case Transaction::CREATE_STRUCTURE:
        case Transaction::UPDATE_STRUCTURE:
            echo 'prefix:     ', $trx->getPrefix(), PHP_EOL;
            echo 'name:       ', $trx->getName(), PHP_EOL;
            echo 'profit (%): ', number_format($trx->getProfitPercent() / 100, 2), PHP_EOL;
            echo 'fee (%):    ', number_format($trx->getFeePercent() / 100, 2), PHP_EOL;
            break;
        case Transaction::UPDATE_PROFIT_ADDRESS:
            echo 'new profit: ', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        case Transaction::UPDATE_FEE_ADDRESS:
            echo 'new fee:    ', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        case Transaction::CREATE_TRANSIT_ADDRESS:
            echo 'new transit:', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        case Transaction::DELETE_TRANSIT_ADDRESS:
            echo 'del transit:', $trx->getRecipient()->getBech32(), PHP_EOL;
            break;
        default:
            echo 'unknown tx version', PHP_EOL;
    }

    echo PHP_EOL;
}
