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
    public function testSign($seed, $message, $expected)
    {
        $actual = SecretKey::fromSeed($seed)->sign($message);
        $this->assertEquals($actual, $expected);
    }

    public function signProvider()
    {
        return array(
            array(
                'seed' => base64_decode('Jg7S6tOU82oXVCpHhQ8TVQ+5VdQGMA+AJS4IcBB7mTo='),
                'msg' => base64_decode('rfJMjOmtp2iaB7tp9SkpmKE5zI6EBZW3KXr42qZG3xPS'),
                'sig' => base64_decode(
                    'uURMdnx1MrafIDh/TauY96NSRmjFmkLcdqragAN55Eqa3cOL8+YZD+J3z+9XCeYWOfuEi8fjGSfQR225X0WVDA=='
                ),
            )
        );
    }
}
