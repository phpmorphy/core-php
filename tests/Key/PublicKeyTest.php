<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Tests\Key;

use Exception;
use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;

class PublicKeyTest extends TestCase
{
    public function testCanBeCreatedFromValidString(): void
    {
        $this->assertInstanceOf(
            PublicKeyInterface::class,
            new PublicKey(
                str_repeat("\x0", SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES)
            )
        );
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(Exception::class);
        new PublicKey(
            str_repeat("\x0", (SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES + 1))
        );
    }

    public function testMustReturnValidKey(): void
    {
        $rnd = random_bytes(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
        $this->assertEquals(
            $rnd,
            (new PublicKey($rnd))->toBytes()
        );
    }

    public function testMustVerifyValidKey(): void
    {
        $keyPair = sodium_crypto_sign_keypair();
        $secKey = sodium_crypto_sign_secretkey($keyPair);
        $pubKey = sodium_crypto_sign_publickey($keyPair);
        $message = random_bytes(85);
        $signature = sodium_crypto_sign_detached($message, $secKey);

        $this->assertTrue(
            (new PublicKey($pubKey))->verifySignature($message, $signature)
        );
    }
}
