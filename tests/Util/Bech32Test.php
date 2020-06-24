<?php

declare(strict_types=1);

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Util\Bech32;

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
                'adr' => 'umi1qq4kruxf'
            ],
            'short checksum' => [
                'adr' => 'li1dgmt3'
            ],
            'empty HRP' => [
                'adr' => '1qqpxkr44'
            ],
            'separator character' => [
                'adr' => 'qqpxkr44'
            ],
            'mixed' => [
                'adr' => 'Umi1qqnlgn4t'
            ],
            'character' => [
                'adr' => "umi1\x0q4kruxd"
            ],
            'short' => [
                'adr' => 'umi1'
            ],
            'non-zero padding' => [
                'adr' => 'tb1qrp33g0q5c5txsp9arysrx4k6zdkfs4nce4xj0gdcccefvpysxf3pjxtptv'
            ]
        ];
    }
}
