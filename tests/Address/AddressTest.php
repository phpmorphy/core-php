<?php

declare(strict_types=1);

namespace Tests\Address;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\PublicKey;

class AddressTest extends TestCase
{
    public function testConstructorException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $bytes = str_repeat('a', Address::LENGTH - 1);
        new Address($bytes);
    }

    public function testVersion(): void
    {
        $obj = new Address();

        $expected = 1057; // 'aaa'
        $actual = $obj->setVersion($expected)->getVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testPrefix(): void
    {
        $expected = 'aaa';

        $obj = new Address();
        $actual = $obj->setPrefix($expected)->getPrefix();

        $this->assertEquals($expected, $actual);
    }

    public function testPublicKey(): void
    {
        $bytes = str_repeat("\xff", PublicKey::LENGTH);
        $expected = new PublicKey($bytes);

        $obj = new Address();
        $actual = $obj->setPublicKey($expected)->getPublicKey();

        $this->assertEquals($expected->toBytes(), $actual->toBytes());
    }

    public function testBech32(): void
    {
        $expected = 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpj';
        $actual = Address::fromBech32($expected)->toBech32();

        $this->assertEquals($expected, $actual);
    }

    public function testBech32Exception(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        Address::fromBech32('umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqu5fmc9');
    }

    public function testFromKey(): void
    {
        $key = new PublicKey(str_repeat("\xff", PublicKey::LENGTH));

        $expected = 'umi1lllllllllllllllllllllllllllllllllllllllllllllllllllsp2pfg9';
        $actual = Address::fromKey($key)->toBech32();

        $this->assertEquals($expected, $actual);
    }
}
