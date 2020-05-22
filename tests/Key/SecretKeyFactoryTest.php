<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Tests\Key;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\SecretKeyFactory;
use UmiTop\UmiCore\Key\SecretKeyInterface;

class SecretKeyFactoryTest extends TestCase
{
    /**
     * @dataProvider seedProvider
     */
    public function testCanBeCreatedFromValidString(string $seed, string $expected): void
    {
        $secKey = SecretKeyFactory::fromSeed($seed);

        $this->assertInstanceOf(
            SecretKeyInterface::class,
            $secKey
        );

        $this->assertEquals(
            $expected,
            $secKey->toBytes()
        );
    }

    /**
     * @return array[]
     */
    public function seedProvider(): array
    {
        return [
            [
                'seed' => hex2bin('d570f786f484150e17c1'),
                'expected' => hex2bin(
                    '5311b5e0180136e83d021c18eb12d039f136bb8aa1cdb8b54839a671d16525f49' .
                    '9f9329fc546ab4b9e6edfc0ff9de6e12d75e23021361c351248e7d7de7cee4e'
                )
            ],
            [
                'seed' => hex2bin('ea7092054a0d28814083925ef41a5ee53fc080b9987566eb2508a0ac83dd0b7e'),
                'expected' => hex2bin(
                    'ea7092054a0d28814083925ef41a5ee53fc080b9987566eb2508a0ac83dd0b7e' .
                    'b651cd1b47c5bebfe450e07ded442a5ece0a6585117a8e65781c298e7c02ba02'
                )
            ],
            [
                'seed' => hex2bin(
                    '5b0fac93979a9e64be744336e41c53d92b6f9c159b8ff80cdb9d05e876d2c89183c342ca81074d8b22c7'
                ),
                'expected' => hex2bin(
                    'e15a94ba721cd4165dcbb4252f36dca98df2388f269b1636cab915b438eb644d' .
                    'c26bea8d4cebe806a4c2c254c39e2b6d1cf801ac26a7e16bc932565a8f7cf993'
                )
            ]
        ];
    }
}
