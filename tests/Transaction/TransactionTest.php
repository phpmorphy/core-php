<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Tests\Transaction;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Transaction\TransactionInterface;

class TransactionTest extends TestCase
{
    public function testCanBeCreatedEmptyTransaction(): void
    {
        $this->assertInstanceOf(TransactionInterface::class, new Transaction());
    }

    public function testEmptyTransactionMustBeBasicVersion(): void
    {
        $this->assertEquals(Transaction::BASIC, (new Transaction())->getVersion());
    }
}
