<?php

namespace Tests\Key;

use Exception;
use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\SecretKey;

class SecretKeyTest extends TestCase
{
    public function testConstructor()
    {
        $bytes = str_repeat('a', SecretKey::LENGTH);
        $key = new SecretKey($bytes);

        $this->assertEquals($bytes, $key->toBytes());
    }

    public function testConstructorException()
    {
        $this->expectException('Exception');

        $bytes = str_repeat('a', SecretKey::LENGTH - 1);
        new SecretKey($bytes);
    }

    /**
     * @dataProvider seedProvider
     */
    public function testFromSeed($seed, $expected)
    {
        $actual = SecretKey::fromSeed($seed)->getPublicKey()->toBytes();
        $this->assertEquals($actual, $expected);
    }

    public function seedProvider()
    {
        return array(
            '31 bytes' => array(
                'seed' => base64_decode('QUwFVtVMcpHvJ5O2mhcufcGkT6vJ56w9pwuff9LozQ=='),
                'pub' => base64_decode('COHSlmM5PqA7Xu2JyTlxE7pvorkTnFOsoYT/ltw3ZPY=')
            ),
            '32 bytes' => array(
                'seed' => base64_decode('5aAVt4OuYhVSbevEhxfez89Y1MGWwKsOQqM30gapqVQ='),
                'pub' => base64_decode('dbCnSb1MfzfQ9WSsjXWl4bx1O98GXtfeMeKhKIAutPc=')
            ),
            '33 bytes' => array(
                'seed' => base64_decode('TsaQqkeQElaT79I11SauRyvY1+vgEtC8imt2t84ZPLtJ'),
                'pub' => base64_decode('KypqdqdomMVT4Ksnx+DIxZrhh/8mpRqdvdjBxNRnO9w=')
            )
        );
    }

    /**
     * @dataProvider signProvider
     */
    public function testSign($key, $message, $expected)
    {
        $obj = new SecretKey($key);
        $actual = $obj->sign($message);
        $this->assertEquals($actual, $expected);
    }

    public function signProvider()
    {
        return array(
            array(
                'key' => base64_decode(
                    'u1mzvCnmyIbgs8RNM9GGGHOWcBdMvD7GIKC0m9zTFcaGXaAPQMbuPdZ1oAnTCfR/1rHTyC3J5n7x+dlFimHM8w=='
                ),
                'msg' => base64_decode('9tJbOqCDGGU0E4F0hYQR88MExTleIverV4iYgQs1bzn+gKmf7HMO3A=='),
                'sig' => base64_decode(
                    '5a+mePEJlbUzTrqM5uxtVklI4KK+wtxBgkt4jiregPLmqasQ+4kTMu2KQfAJd7IlFYZqH2yM6lZDufXVY6ooBQ=='
                ),
            )
        );
    }
}
