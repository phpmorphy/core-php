<?php

declare(strict_types=1);

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Util\Bech32;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Bech32Test extends TestCase
{
    /**
     * @dataProvider invalidAddressProvider
     */
    public function testDecodeException(string $address): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $obj = new Bech32();
        $obj->decode($address);
    }

    /**
     * @return array<array <string, string>>
     */
    public function invalidAddressProvider(): array
    {
        return [
            'invalid checksum' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpf'
            ],
            'invalid character' => [
                'adr' => 'umi1iqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpj'
            ],
            'empty HRP' => [
                'adr' => '1qqpxkr44'
            ],
            'missing separator' => [
                'adr' => 'umiqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr5zcpf'
            ],
            'short' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqu5fmc9'
            ],
            'non-zero padding' => [
                'adr' => 'umi1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqlfceute'
            ]
        ];
    }
}
