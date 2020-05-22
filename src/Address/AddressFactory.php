<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Address;

use UmiTop\UmiCore\Key\PublicKeyInterface;
use UmiTop\UmiCore\Key\SecretKeyInterface;

class AddressFactory
{
    public static function fromPublicKey(PublicKeyInterface $publicKey): AddressInterface
    {
        return (new Address())->setPublicKey($publicKey);
    }

    public static function fromSecretKey(SecretKeyInterface $secretKey): AddressInterface
    {
        return (new Address())->setPublicKey($secretKey->getPublicKey());
    }

    public static function fromBech32(string $address): AddressInterface
    {
        return (new Address())->fromBech32($address);
    }
}
