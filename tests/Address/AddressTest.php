<?php

declare(strict_types=1);

namespace Tests\Address;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Key\PublicKey;

class AddressTest extends TestCase
{
    public function testFromBech32(): void
    {
        $expected = 'umi1lllllllllllllllllllllllllllllllllllllllllllllllllllsp2pfg9';
        $actual = Address::fromBech32($expected)->getBech32();

        $this->assertEquals($expected, $actual);
    }

    public function testFromBytes(): void
    {
        $expected = str_repeat("\x01", Address::LENGTH);
        $actual = Address::fromBytes($expected)->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testFromKey(): void
    {
        $key = new PublicKey(str_repeat("\xff", PublicKey::LENGTH));

        $expected = 'umi1lllllllllllllllllllllllllllllllllllllllllllllllllllsp2pfg9';
        $actual = Address::fromKey($key)->getBech32();

        $this->assertEquals($expected, $actual);
    }

    public function testSetBytesException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $bytes = str_repeat('a', Address::LENGTH - 1);
        $obj = new Address();
        $obj->setBytes($bytes);
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
        $pubKey = new PublicKey($bytes);
        $expected = $pubKey->getBytes();

        $obj = new Address();
        $actual = $obj->setPublicKey($pubKey)->getPublicKey()->getBytes();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider validAddressProvider
     */
    public function testBech32(string $expected): void
    {
        $obj = new Address();
        $actual = $obj->setBech32($expected)->getBech32();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<array <string, string>>
     */
    public function validAddressProvider(): array
    {
        return [
            'umi 0xFF' => [
                'adr' => 'umi1lllllllllllllllllllllllllllllllllllllllllllllllllllsp2pfg9'
            ],
            'umi 0x00' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpj'
            ],
            'genesis 0xFF' => [
                'adr' => 'genesis1llllllllllllllllllllllllllllllllllllllllllllllllllls5c7uy0'
            ],
            'genesis 0x00' => [
                'adr' => 'genesis1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqkxaddc'
            ],
            'aaa rand' => [
                'adr' => 'aaa1nfgzzgkr3nd69jes5kw87s2tuv46mhmrqpnw8ksffaujycenxx6sl48tkv'
            ]
        ];
    }

    /**
     * @dataProvider invalidAddressProvider
     */
    public function testBech32Exception(string $address): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $obj = new Address();
        $obj->setBech32($address);
    }

    /**
     * @return array<array <string, string>>
     */
    public function invalidAddressProvider(): array
    {
        return [
            'invalid prefix 1' => [
                'adr' => 'geneziz1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqwa7qv0'
            ],
            'invalid prefix 2' => [
                'adr' => '+++1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq2trd4a'
            ],
            'empty prefix' => [
                'adr' => '1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqugay46'
            ],
            'invalid checksum' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpf'
            ],
            'invalid character' => [
                'adr' => 'umi1iqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpj'
            ],
            'no separator' => [
                'adr' => 'umilqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpj'
            ],
            'too short' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqu5fmc9'
            ],
            'too long' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq63dha7'
            ],
            'non-zero padding' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqlfceute'
            ]
        ];
    }
}
