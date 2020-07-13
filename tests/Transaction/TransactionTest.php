<?php

declare(strict_types=1);

namespace Tests\Transaction;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class TransactionTest extends TestCase
{
    public function testFromBytes(): void
    {
        $bytes = str_repeat("\x0", Transaction::LENGTH);
        $actual = Transaction::fromBytes($bytes)->getBytes();

        $this->assertEquals($bytes, $actual);
    }

    public function testSetBytesException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $bytes = str_repeat("\x0", Transaction::LENGTH - 1);
        $obj = new Transaction();
        $obj->setBytes($bytes);
    }

    public function testHash(): void
    {
        $base64 = 'AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $bytes = base64_decode($base64);

        $expected = base64_decode('YXIQPOm1U39xB/DF/VT+bMThSJzYQVR6B+2UCd6yA0s=');
        $actual = Transaction::fromBytes($bytes)->getHash();

        $this->assertEquals($expected, $actual);
    }

    public function testVersion(): void
    {
        $expected = Transaction::CREATE_TRANSIT_ADDRESS;
        $trx = new Transaction();
        $actual = $trx->setVersion($expected)->getVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testIncorrectVersionException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $trx = new Transaction();
        $trx->setPrefix('aaa');
    }

    public function testSender(): void
    {
        $adr = new Address();
        $adr->setBytes(str_repeat("\x11", Address::LENGTH));
        $expected = $adr->getBytes();

        $trx = new Transaction();
        $actual = $trx->setSender($adr)->getSender()->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testRecipient(): void
    {
        $adr = new Address();
        $adr->setBytes(str_repeat("\x22", Address::LENGTH));
        $expected = $adr->getBytes();

        $trx = new Transaction();
        $actual = $trx->setRecipient($adr)->getRecipient()->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testValue(): void
    {
        $expected = 9223372036854775807; // PHP_INT_MAX
        $trx = new Transaction();
        $actual = $trx->setValue($expected)->getValue();

        $this->assertEquals($expected, $actual);
    }

    public function testNonce(): void
    {
        $expected = -9223372036854775807; // PHP_INT_MIN
        $trx = new Transaction();
        $actual = $trx->setNonce($expected)->getNonce();

        $this->assertEquals($expected, $actual);
    }

    public function testPrefix(): void
    {
        $expected = 'zzz';
        $trx = new Transaction();
        $actual = $trx->setVersion(Transaction::CREATE_STRUCTURE)->setPrefix($expected)->getPrefix();

        $this->assertEquals($expected, $actual);
    }

    public function testNameException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $name = str_repeat('a', 36);
        $trx = new Transaction();
        $trx->setVersion(Transaction::CREATE_STRUCTURE)->setName($name);
    }

    public function testNameExceptionBytes(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $bytes = str_repeat("\x0", 150);
        $bytes[0] = chr(2);
        $bytes[41] = chr(255);

        $trx = new Transaction();
        $trx->setBytes($bytes)->getName();
    }

    public function testName(): void
    {
        $expected = 'Hello World!';
        $trx = new Transaction();
        $actual = $trx->setVersion(Transaction::CREATE_STRUCTURE)->setName($expected)->getName();

        $this->assertEquals($expected, $actual);
    }

    public function testProfitPercentException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $trx = new Transaction();
        $trx->setVersion(Transaction::CREATE_STRUCTURE)->setProfitPercent(99);
    }

    public function testProfitPercent(): void
    {
        $expected = 100;
        $trx = new Transaction();
        $actual = $trx->setVersion(Transaction::CREATE_STRUCTURE)->setProfitPercent($expected)->getProfitPercent();

        $this->assertEquals($expected, $actual);
    }

    public function testFeePercentException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $trx = new Transaction();
        $trx->setVersion(Transaction::CREATE_STRUCTURE)->setFeePercent(2001);
    }

    public function testFeePercent(): void
    {
        $expected = 100;
        $trx = new Transaction();
        $actual = $trx->setVersion(Transaction::CREATE_STRUCTURE)->setFeePercent($expected)->getFeePercent();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testSign(): void
    {
        $key = SecretKey::fromSeed(str_repeat("\x42", 32));
        $adr = new Address();
        $adr->setPublicKey($key->getPublicKey());

        $trx = new Transaction();
        $actual = $trx->setSender($adr)->sign($key)->verify();

        $this->assertTrue($actual);
    }

    public function testVerify(): void
    {
        $sig = base64_decode(
            'u04f+NK+ib+hQU7g/HYeQr9BjZbgZgOJidoW3YhNYM8ZI48Z8vT4kvbdqH9V2iX4z8XLr9Ay3x69eQSB6/KlCw=='
        );
        $trx = new Transaction();
        $actual = $trx->setSignature($sig)->verify();
        $this->assertFalse($actual);
    }
}
