<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key\Ed25519;

use Exception;
use UmiTop\UmiCore\Key\AbstractKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;

class PublicKey extends AbstractKey implements PublicKeyInterface
{
    public function __construct(string $binary)
    {
        if (strlen($binary) !== \SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new Exception(
                sprintf('public key size should be %d bytes', SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES)
            );
        }

        parent::__construct($binary);
    }

    public function verifySignature(string $message, string $signature): bool
    {
        return sodium_crypto_sign_verify_detached($signature, $message, $this->bytes);
    }
}
