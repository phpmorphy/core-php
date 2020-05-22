<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key;

use UmiTop\UmiCore\Key\Ed25519\SecretKey;

class SecretKeyFactory
{
    public static function fromSeed(string $seed): SecretKeyInterface
    {
        if (strlen($seed) !== SODIUM_CRYPTO_SIGN_SEEDBYTES) {
            $seed = hash('sha256', $seed, true);
        }

        return new SecretKey(
            sodium_crypto_sign_secretkey(
                sodium_crypto_sign_seed_keypair($seed)
            )
        );
    }
}
