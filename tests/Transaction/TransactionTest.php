<?php

declare(strict_types=1);

namespace Tests\Transaction;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;

class TransactionTest extends TestCase
{
//    public function testConstructor()
//    {
//        $expected = str_repeat("\x0", Transaction::LENGTH);
//        $actual = new Transaction();
//
//        $this->assertEquals($expected, $actual->toBytes());
//    }

    public function testFromBytes(): void
    {
        $bytes = str_repeat("\x0", Transaction::LENGTH);
        $actual = Transaction::fromBytes($bytes);

        $this->assertEquals($bytes, $actual->toBytes());
    }

    public function testFromBase64Exception(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        Transaction::fromBase64('zzzzz');
    }

    public function testConstructorException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $bytes = str_repeat("\x0", Transaction::LENGTH - 1);
        new Transaction($bytes);
    }

    public function testHash(): void
    {
        $base64 = 'AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

        $expected = base64_decode('YXIQPOm1U39xB/DF/VT+bMThSJzYQVR6B+2UCd6yA0s=');
        $actual = Transaction::fromBase64($base64)->getHash();

        $this->assertEquals($expected, $actual);
    }

    public function testVersion(): void
    {
        $expected = Transaction::CREATE_TRANSIT_ADDRESS;
        $trx = new Transaction();
        $actual = $trx->setVersion($expected)->getVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testSender(): void
    {
        $expected = Address::fromBytes(str_repeat("\x1", Address::LENGTH));
        $trx = new Transaction();
        $actual = $trx->setSender($expected)->getSender();

        $this->assertEquals($expected->toBytes(), $actual->toBytes());
    }

    public function testRecipient(): void
    {
        $expected = Address::fromBytes(str_repeat("\x1", Address::LENGTH));
        $trx = new Transaction();
        $actual = $trx->setRecipient($expected)->getRecipient();

        $this->assertEquals($expected->toBytes(), $actual->toBytes());
    }

    public function testSetValueException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $trx = new Transaction();
        $trx->setValue(0);
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
        $actual = $trx->setPrefix($expected)->getPrefix();

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
        $trx->setName($name);
    }

    public function testName(): void
    {
        $expected = 'Hello World!';
        $trx = new Transaction();
        $actual = $trx->setName($expected)->getName();

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
        $trx->setProfitPercent(99);
    }

    public function testProfitPercent(): void
    {
        $expected = 100;
        $trx = new Transaction();
        $actual = $trx->setProfitPercent($expected)->getProfitPercent();

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
        $trx->setFeePercent(2001);
    }

    public function testFeePercent(): void
    {
        $expected = 100;
        $trx = new Transaction();
        $actual = $trx->setFeePercent($expected)->getFeePercent();

        $this->assertEquals($expected, $actual);
    }

    public function testSign(): void
    {
        $expected = base64_decode(
            'u04f+NK+ib+hQU7g/HYeQr9BjZbgZgOJidoW3YhNYM8ZI48Z8vT4kvbdqH9V2iX4z8XLr9Ay3x69eQSB6/KlCw=='
        );
        $key = SecretKey::fromSeed(str_repeat("\x0", 32));
        $trx = new Transaction();
        $actual = $trx->sign($key)->getSignature();

        $this->assertEquals($expected, $actual);
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
