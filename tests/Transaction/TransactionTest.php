<?php

namespace Tests\Transaction;

use Exception;
use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;

class TransactionTest extends TestCase
{
    public function testConstructor()
    {
        $expected = str_repeat("\x0", Transaction::LENGTH);
        $actual = new Transaction();

        $this->assertEquals($expected, $actual->toBytes());
    }

    public function testFromBytes()
    {
        $bytes = str_repeat("\x0", Transaction::LENGTH);
        $actual = Transaction::fromBytes($bytes);

        $this->assertEquals($bytes, $actual->toBytes());
    }

    public function testFromBase64Exception()
    {
        $this->expectException('Exception');

        Transaction::fromBase64('zzzzz');
    }

    public function testConstructorException()
    {
        $this->expectException('Exception');

        $bytes = str_repeat("\x0", Transaction::LENGTH - 1);
        new Transaction($bytes);
    }

    public function testHash()
    {
        $base64 = 'AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

        $expected = base64_decode('YXIQPOm1U39xB/DF/VT+bMThSJzYQVR6B+2UCd6yA0s=');
        $actual = Transaction::fromBase64($base64)->getHash();

        $this->assertEquals($expected, $actual);
    }

    public function testVersion()
    {
        $expected = Transaction::CREATE_TRANSIT_ADDRESS;
        $trx = new Transaction();
        $actual = $trx->setVersion($expected)->getVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testSender()
    {
        $expected = Address::fromBytes(str_repeat("\x1", Address::LENGTH));
        $trx = new Transaction();
        $actual = $trx->setSender($expected)->getSender();

        $this->assertEquals($expected->toBytes(), $actual->toBytes());
    }

    public function testRecipient()
    {
        $expected = Address::fromBytes(str_repeat("\x1", Address::LENGTH));
        $trx = new Transaction();
        $actual = $trx->setRecipient($expected)->getRecipient();

        $this->assertEquals($expected->toBytes(), $actual->toBytes());
    }

    public function testSetValueException()
    {
        $this->expectException('Exception');

        $trx = new Transaction();
        $trx->setValue(0);
    }

    public function testValue()
    {
        $expected = 9223372036854775807; // PHP_INT_MAX
        $trx = new Transaction();
        $actual = $trx->setValue($expected)->getValue();

        $this->assertEquals($expected, $actual);
    }

    public function testNonce()
    {
        $expected = -9223372036854775808; // PHP_INT_MIN
        $trx = new Transaction();
        $actual = $trx->setNonce($expected)->getNonce();

        $this->assertEquals($expected, $actual);
    }

    public function testPrefix()
    {
        $expected = 'zzz';
        $trx = new Transaction();
        $actual = $trx->setPrefix($expected)->getPrefix();

        $this->assertEquals($expected, $actual);
    }

    public function testNameException()
    {
        $this->expectException('Exception');

        $name = str_repeat('a', 36);
        $trx = new Transaction();
        $trx->setName($name);
    }

    public function testName()
    {
        $expected = 'Hello World!';
        $trx = new Transaction();
        $actual = $trx->setName($expected)->getName();

        $this->assertEquals($expected, $actual);
    }

    public function testProfitPercentException()
    {
        $this->expectException('Exception');

        $trx = new Transaction();
        $trx->setProfitPercent(99);
    }

    public function testProfitPercent()
    {
        $expected = 100;
        $trx = new Transaction();
        $actual = $trx->setProfitPercent($expected)->getProfitPercent();

        $this->assertEquals($expected, $actual);
    }

    public function testFeePercentException()
    {
        $this->expectException('Exception');

        $trx = new Transaction();
        $trx->setFeePercent(2001);
    }

    public function testFeePercent()
    {
        $expected = 100;
        $trx = new Transaction();
        $actual = $trx->setFeePercent($expected)->getFeePercent();

        $this->assertEquals($expected, $actual);
    }

    public function testSign()
    {
        $expected = base64_decode(
            'u04f+NK+ib+hQU7g/HYeQr9BjZbgZgOJidoW3YhNYM8ZI48Z8vT4kvbdqH9V2iX4z8XLr9Ay3x69eQSB6/KlCw=='
        );
        $key = SecretKey::fromSeed(str_repeat("\x0", 32));
        $trx = new Transaction();
        $actual = $trx->sign($key)->getSignature();

        $this->assertEquals($expected, $actual);
    }

    public function testVerify()
    {
        $sig = base64_decode(
            'u04f+NK+ib+hQU7g/HYeQr9BjZbgZgOJidoW3YhNYM8ZI48Z8vT4kvbdqH9V2iX4z8XLr9Ay3x69eQSB6/KlCw=='
        );
        $trx = new Transaction();
        $actual = $trx->setSignature($sig)->verify();

        $this->assertFalse($actual);
    }
}
