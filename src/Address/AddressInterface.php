<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Address;

use UmiTop\UmiCore\Key\PublicKeyInterface;

interface AddressInterface
{
    public const ADDRESS_LENGTH = 34;
    public const VERSION_UMI_BASIC = 0x55A9;
    public const VERSION_UMI_HD = 0xD5A9;

    public function getVersion(): int;

    public function setVersion(int $version): AddressInterface;

    public function getPrefix(): string;

    public function setPrefix(string $prefix): AddressInterface;

    public function getPublicKey(): PublicKeyInterface;

    public function setPublicKey(PublicKeyInterface $publicKey): AddressInterface;

    public function fromBech32(string $address): AddressInterface;

    public function toBytes(): string;

    public function toBech32(): string;
}
