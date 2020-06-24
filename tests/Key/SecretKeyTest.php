<?php

namespace Tests\Key;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\SecretKey;

class SecretKeyTest extends TestCase
{
    public function testConstructor()
    {
        $expected = base64_decode(
            'u1mzvCnmyIbgs8RNM9GGGHOWcBdMvD7GIKC0m9zTFcaGXaAPQMbuPdZ1oAnTCfR/1rHTyC3J5n7x+dlFimHM8w=='
        );
        $key = new SecretKey($expected);
        $actual = $key->toBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testConstructorException()
    {
        method_exists($this, 'expectException')
            ? $this->expectException('Exception')
            : $this->setExpectedException('Exception'); // PHPUnit 4

        $bytes = str_repeat('a', SecretKey::LENGTH - 1);
        new SecretKey($bytes);
    }

    public function testGetPublicKey()
    {
        $bytes = base64_decode(
            'u1mzvCnmyIbgs8RNM9GGGHOWcBdMvD7GIKC0m9zTFcaGXaAPQMbuPdZ1oAnTCfR/1rHTyC3J5n7x+dlFimHM8w=='
        );
        $expected = base64_decode('hl2gD0DG7j3WdaAJ0wn0f9ax08gtyeZ+8fnZRYphzPM=');
        $secKey = new SecretKey($bytes);
        $actual = $secKey->getPublicKey()->toBytes();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider seedProvider
     */
    public function testFromSeed($seed, $expected)
    {
        $actual = SecretKey::fromSeed(base64_decode($seed))->getPublicKey()->toBytes();
        $this->assertEquals($actual, base64_decode($expected));
    }

    public function seedProvider()
    {
        return array(
            '31 bytes' => array(
                'seed' => 'QUwFVtVMcpHvJ5O2mhcufcGkT6vJ56w9pwuff9LozQ==',
                'pub' => 'COHSlmM5PqA7Xu2JyTlxE7pvorkTnFOsoYT/ltw3ZPY='
            ),
            '32 bytes' => array(
                'seed' => '5aAVt4OuYhVSbevEhxfez89Y1MGWwKsOQqM30gapqVQ=',
                'pub' => 'dbCnSb1MfzfQ9WSsjXWl4bx1O98GXtfeMeKhKIAutPc='
            ),
            '33 bytes' => array(
                'seed' => 'TsaQqkeQElaT79I11SauRyvY1+vgEtC8imt2t84ZPLtJ',
                'pub' => 'KypqdqdomMVT4Ksnx+DIxZrhh/8mpRqdvdjBxNRnO9w='
            )
        );
    }

    /**
     * @dataProvider signProvider
     */
    public function testSign($key, $message, $expected)
    {
        $obj = new SecretKey(base64_decode($key));
        $actual = $obj->sign(base64_decode($message));
        $this->assertEquals($actual, base64_decode($expected));
    }

    public function signProvider()
    {
        return array(
            '1st' => array(
                'key' => 'u1mzvCnmyIbgs8RNM9GGGHOWcBdMvD7GIKC0m9zTFcaGXaAPQMbuPdZ1oAnTCfR/1rHTyC3J5n7x+dlFimHM8w==',
                'msg' => '9tJbOqCDGGU0E4F0hYQR88MExTleIverV4iYgQs1bzn+gKmf7HMO3A==',
                'sig' => '5a+mePEJlbUzTrqM5uxtVklI4KK+wtxBgkt4jiregPLmqasQ+4kTMu2KQfAJd7IlFYZqH2yM6lZDufXVY6ooBQ==',
            )
        );
    }
}
