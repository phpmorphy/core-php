<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key\Ed25519;

use Exception;
use UmiTop\UmiCore\Key\AbstractKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;
use UmiTop\UmiCore\Key\SecretKeyInterface;

class SecretKey extends AbstractKey implements SecretKeyInterface
{
    public function __construct(string $bytes)
    {
        if (strlen($bytes) !== \SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new Exception(
                sprintf('secretkey should be %d bytes', SODIUM_CRYPTO_SIGN_SECRETKEYBYTES)
            );
        }

        parent::__construct($bytes);
    }

    public function getPublicKey(): PublicKeyInterface
    {
        return new PublicKey(
            sodium_crypto_sign_publickey_from_secretkey($this->bytes)
        );
    }

    public function sign(string $message): string
    {
        return sodium_crypto_sign_detached($message, $this->bytes);
    }
}
