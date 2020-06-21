<?php

namespace Tests\Key;

use Exception;
use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;

class PublicKeyTest extends TestCase
{
    public function testConstructor()
    {
        $bytes = str_repeat('a', PublicKey::LENGTH);
        $key = new PublicKey($bytes);

        $this->assertEquals($bytes, $key->toBytes());
    }

    public function testConstructorException()
    {
        $this->expectException('Exception');

        $bytes = str_repeat('a', PublicKey::LENGTH - 1);
        new PublicKey($bytes);
    }

    public function testGetPublicKey()
    {
        $bytes = str_repeat('a', PublicKey::LENGTH);
        $key = new PublicKey($bytes);

        $this->assertInstanceOf('UmiTop\UmiCore\Key\PublicKey', $key->getPublicKey());
    }

    /**
     * @dataProvider signatureProvider
     */
    public function testVerifySignature($key, $message, $signature, $expected)
    {
        $key = new PublicKey($key);
        $actual = $key->verifySignature($signature, $message);
        $this->assertEquals($expected, $actual);
    }

    public function signatureProvider()
    {
        return array(
            'valid' => array(
                'key' => base64_decode('oD7CzMxo3UYjXg/URrZPluOSOjAbzYVxIXDyONlR5pI='),
                'msg' => base64_decode('K7B3Y9MILKseAlBDkjuwjc48NT3vMWrhixyh7diP8O8B'),
                'sig' => base64_decode(
                    '7mVMWMzqHgy+I9GSlS0XFAXV1IjGmeZhlDQOBMjrwua7EULygNIKgkiQ2h6kSeDq76tBomoaPbc8faFYwNO0Dg=='
                ),
                'exp' => true
            ),
            'invalid' => array(
                'key' => base64_decode('MUAmRXK6+YHhASTdWN7Xx2keYPG1V+VoVIXN3RNIBSE='),
                'msg' => base64_decode('UGffQxqOxfMcTcWRVaRklCS/MNme5j2IzUh0J8ksbPTd'),
                'sig' => base64_decode(
                    'kQ7z0+PDJBaQeihqd0hForqdBTVr8mrAO0Sg6RWMi3EbFSdHMVVicqSZVthcr+gjpnjjdOiKbxembcCoXAieCQ=='
                ),
                'exp' => false
            )
        );
    }
}
