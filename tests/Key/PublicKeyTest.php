<?php

declare(strict_types=1);

namespace Tests\Key;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\PublicKey;

class PublicKeyTest extends TestCase
{
    public function testConstructorException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $bytes = str_repeat('a', PublicKey::LENGTH - 1);
        new PublicKey($bytes);
    }

    /**
     * @dataProvider signatureProvider
     */
    public function testVerifySignature(string $key, string $msg, string $sig, bool $expected): void
    {
        $pubKey = new PublicKey(base64_decode($key));
        $signature = base64_decode($sig);
        $message = base64_decode($msg);
        $actual = $pubKey->verifySignature($signature, $message);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array<string, string|bool>>
     */
    public function signatureProvider(): array
    {
        return [
            'valid' => [
                'key' => 'oD7CzMxo3UYjXg/URrZPluOSOjAbzYVxIXDyONlR5pI=',
                'msg' => 'K7B3Y9MILKseAlBDkjuwjc48NT3vMWrhixyh7diP8O8B',
                'sig' => '7mVMWMzqHgy+I9GSlS0XFAXV1IjGmeZhlDQOBMjrwua7EULygNIKgkiQ2h6kSeDq76tBomoaPbc8faFYwNO0Dg==',
                'exp' => true
            ],
            'invalid' => [
                'key' => 'MUAmRXK6+YHhASTdWN7Xx2keYPG1V+VoVIXN3RNIBSE=',
                'msg' => 'UGffQxqOxfMcTcWRVaRklCS/MNme5j2IzUh0J8ksbPTd',
                'sig' => 'kQ7z0+PDJBaQeihqd0hForqdBTVr8mrAO0Sg6RWMi3EbFSdHMVVicqSZVthcr+gjpnjjdOiKbxembcCoXAieCQ==',
                'exp' => false
            ]
        ];
    }
}
