<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Tests\Key;

use Exception;
use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Key\SecretKeyInterface;

final class SecretKeyTest extends TestCase
{
    public function testCanBeCreatedFromValidString(): void
    {
        $this->assertInstanceOf(
            SecretKeyInterface::class,
            new SecretKey(
                str_repeat("\x0", SODIUM_CRYPTO_SIGN_SECRETKEYBYTES)
            )
        );
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(Exception::class);
        new SecretKey(
            str_repeat("\x0", (SODIUM_CRYPTO_SIGN_SECRETKEYBYTES + 1))
        );
    }

    public function testMustReturnValidKey(): void
    {
        $rnd = random_bytes(SODIUM_CRYPTO_SIGN_SECRETKEYBYTES);
        $this->assertEquals(
            $rnd,
            (new SecretKey($rnd))->toBytes()
        );
    }

    public function testMustReturnValidPublicKey(): void
    {
        $keyPair = sodium_crypto_sign_keypair();
        $secKey = sodium_crypto_sign_secretkey($keyPair);
        $pubKey = sodium_crypto_sign_publickey($keyPair);

        $this->assertEquals(
            $pubKey,
            (new SecretKey($secKey))->getPublicKey()->toBytes()
        );
    }

    public function testMustReturnValidSignature(): void
    {
        $keyPair = sodium_crypto_sign_keypair();
        $secKey = sodium_crypto_sign_secretkey($keyPair);
        $pubKey = sodium_crypto_sign_publickey($keyPair);
        $message = random_bytes(85);
        $signature = (new SecretKey($secKey))->sign($message);

        $this->assertTrue(
            sodium_crypto_sign_verify_detached($signature, $message, $pubKey)
        );
    }
}
