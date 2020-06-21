<?php

namespace Tests\Address;

use Exception;
use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\PublicKey;

class AddressTest extends TestCase
{
    public function testFromBytes()
    {
        $bytes = str_repeat('a', Address::LENGTH);
        $address = Address::fromBytes($bytes);

        $this->assertEquals($bytes, $address->toBytes());
    }

    public function testFromBytesException()
    {
        $this->expectException('Exception');

        $bytes = str_repeat('a', Address::LENGTH - 1);
        Address::fromBytes($bytes);
    }

    public function testVersion()
    {
        $bytes = str_repeat("\x0", Address::LENGTH);

        $expected = 1057; // 'aaa'
        $actual = Address::fromBytes($bytes)->setVersion($expected)->getVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testPrefix()
    {
        $bytes = str_repeat("\x0", Address::LENGTH);

        $expected = 'aaa';
        $actual = Address::fromBytes($bytes)->setPrefix($expected)->getPrefix();

        $this->assertEquals($expected, $actual);
    }

    public function testPublicKey()
    {
        $bytes = str_repeat("\xff", PublicKey::LENGTH);
        $expected = new PublicKey($bytes);

        $bytes = str_repeat("\x0", Address::LENGTH);
        $actual = Address::fromBytes($bytes)->setPublicKey($expected)->getPublicKey();

        $this->assertEquals($expected->toBytes(), $actual->toBytes());
    }

    public function testBech32()
    {
        $expected = 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpj';
        $actual = Address::fromBech32($expected)->toBech32();

        $this->assertEquals($expected, $actual);
    }

    public function testBech32Exception()
    {
        $this->expectException('Exception');

        Address::fromBech32('umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqu5fmc9');
    }

    public function testFromKey()
    {
        $key = new PublicKey(str_repeat("\xff", PublicKey::LENGTH));

        $expected = 'umi1lllllllllllllllllllllllllllllllllllllllllllllllllllsp2pfg9';
        $actual = Address::fromKey($key)->toBech32();

        $this->assertEquals($expected, $actual);
    }
}
