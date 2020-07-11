<?php

declare(strict_types=1);

namespace Tests\Key;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\SecretKey;

class SecretKeyTest extends TestCase
{
    public function testConstructor(): void
    {
        $expected = base64_decode(
            'u1mzvCnmyIbgs8RNM9GGGHOWcBdMvD7GIKC0m9zTFcaGXaAPQMbuPdZ1oAnTCfR/1rHTyC3J5n7x+dlFimHM8w=='
        );
        $key = new SecretKey($expected);
        $actual = $key->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testConstructorException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $bytes = str_repeat('a', SecretKey::LENGTH - 1);
        new SecretKey($bytes);
    }

    public function testGetPublicKey(): void
    {
        $bytes = base64_decode(
            'u1mzvCnmyIbgs8RNM9GGGHOWcBdMvD7GIKC0m9zTFcaGXaAPQMbuPdZ1oAnTCfR/1rHTyC3J5n7x+dlFimHM8w=='
        );
        $expected = base64_decode('hl2gD0DG7j3WdaAJ0wn0f9ax08gtyeZ+8fnZRYphzPM=');
        $secKey = new SecretKey($bytes);
        $actual = $secKey->getPublicKey()->getBytes();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider seedProvider
     */
    public function testFromSeed(string $seed, string $expected): void
    {
        $seed = base64_decode($seed);
        $expected = base64_decode($expected);
        $actual = SecretKey::fromSeed($seed)->getPublicKey()->getBytes();

        $this->assertEquals($actual, $expected);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function seedProvider(): array
    {
        return [
            '31 bytes' => [
                'seed' => 'QUwFVtVMcpHvJ5O2mhcufcGkT6vJ56w9pwuff9LozQ==',
                'pub' => 'COHSlmM5PqA7Xu2JyTlxE7pvorkTnFOsoYT/ltw3ZPY='
            ],
            '32 bytes' => [
                'seed' => '5aAVt4OuYhVSbevEhxfez89Y1MGWwKsOQqM30gapqVQ=',
                'pub' => 'dbCnSb1MfzfQ9WSsjXWl4bx1O98GXtfeMeKhKIAutPc='
            ],
            '33 bytes' => [
                'seed' => 'TsaQqkeQElaT79I11SauRyvY1+vgEtC8imt2t84ZPLtJ',
                'pub' => 'KypqdqdomMVT4Ksnx+DIxZrhh/8mpRqdvdjBxNRnO9w='
            ]
        ];
    }

    /**
     * @dataProvider signProvider
     */
    public function testSign(string $key, string $message, string $expected): void
    {
        $key = base64_decode($key);
        $message = base64_decode($message);
        $expected = base64_decode($expected);

        $obj = new SecretKey($key);
        $actual = $obj->sign($message);
        $this->assertEquals($actual, $expected);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function signProvider(): array
    {
        return [
            '1st' => [
                'key' => 'u1mzvCnmyIbgs8RNM9GGGHOWcBdMvD7GIKC0m9zTFcaGXaAPQMbuPdZ1oAnTCfR/1rHTyC3J5n7x+dlFimHM8w==',
                'msg' => '9tJbOqCDGGU0E4F0hYQR88MExTleIverV4iYgQs1bzn+gKmf7HMO3A==',
                'sig' => '5a+mePEJlbUzTrqM5uxtVklI4KK+wtxBgkt4jiregPLmqasQ+4kTMu2KQfAJd7IlFYZqH2yM6lZDufXVY6ooBQ==',
            ]
        ];
    }
}
