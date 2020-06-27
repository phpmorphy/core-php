<?php

declare(strict_types=1);

namespace Tests\Transaction;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\SecretKey;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class TransactionTest extends TestCase
{
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
        $key = SecretKey::fromSeed(str_repeat("\x0", 32));
        $adr = Address::fromKey($key);
        $trx = new Transaction();
        $actual = $trx->setSender($adr)->sign($key)->verify();
        $this->assertTrue($actual);
    }

    public function testSignException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $key = SecretKey::fromSeed(str_repeat("\x0", 32));
        $obj = new Transaction();
        $obj->sign($key, -1);
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

    /**
     * @dataProvider bytesProvider
     */
    public function testGetPowBits(string $bytes, int $powBits): void
    {
        $obj = new Transaction(base64_decode($bytes));
        $this->assertEquals($powBits, $obj->getPowBits());
    }

    /**
     * @return array<int|string, array<string, int|string>>
     */
    public function bytesProvider(): array
    {
        return [
            '24' => [
                'bytes' =>
                    'AQQhO2onvM62pC1io6jQKm8Nc2UyFXcd4kOmOsBIoYtZ2ikIQjtqJ7zOtqQtYqOo0CpvDXN' .
                    'lMhV3HeJDpjrASKGLWdopAAAAAAdbzRXQ8islGAiVgitiax/TuTl1q+G/4ClbtUqUGB32Ii' .
                    'bsC3SwmcFzGo5Yja+xzddRrKSSXYzAWCtqRSrfIRaEPLlytLz6D6jkGgYA',
                'bits' => 24
            ],
            '20' => [
                'bytes' =>
                    'AQQhO2onvM62pC1io6jQKm8Nc2UyFXcd4kOmOsBIoYtZ2ikIQjtqJ7zOtqQtYqOo0CpvDXN' .
                    'lMhV3HeJDpjrASKGLWdopAAAAAAdbzRUl2AolGAlxvYEyT6/5aQt7V01G7tUu7quGMH2N3G' .
                    'cI3TFleG4kuTsO7/dAgnmOFv8Jtd3H6hoTJwebaADPUZrmTi1xr314LAMA',
                'bits' => 20
            ],
            '15' => [
                'bytes' =>
                    'AQQhO2onvM62pC1io6jQKm8Nc2UyFXcd4kOmOsBIoYtZ2ikIQjtqJ7zOtqQtYqOo0CpvDXN' .
                    'lMhV3HeJDpjrASKGLWdopAAAAAAdbzRUAqk0lGAmeamcwzijOBt5NfbdCwj813UZ4wgbogs' .
                    'wZHBHzzm0zy1yQeNUmieTCE0ZrM5l8aZ5PhgrP9DuhvxiQWaicswd/oAgA',
                'bits' => 15
            ],
            '8' => [
                'bytes' =>
                    'AQQhO2onvM62pC1io6jQKm8Nc2UyFXcd4kOmOsBIoYtZ2ikIQjtqJ7zOtqQtYqOo0CpvDXN' .
                    'lMhV3HeJDpjrASKGLWdopAAAAAAdbzRUAAVolGAnq0aOujiTIumZBqEV7wVcdBvaNrXJXKO' .
                    'jiuSAHlpphpvBXTBX4GrXK3WP3aXh6yLqN9vr0r6vs1hLuxKUxFlIwdgkA',
                'bits' => 8
            ],
            '0' => [
                'bytes' =>
                    'AQQhO2onvM62pC1io6jQKm8Nc2UyFXcd4kOmOsBIoYtZ2ikIQjtqJ7zOtqQtYqOo0CpvDXN' .
                    'lMhV3HeJDpjrASKGLWdopAAAAAAdbzRUAAAAlGAn77OKoVDAJWplpbukaD0kwirLUv3vB8N' .
                    'GxpUGpcaC+k0fWWHnL485umMbHgwtL3/ChjCUZDgNpCSXK2fD2UC6RLAQA',
                'bits' => 0
            ]
        ];
    }
}
