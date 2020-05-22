<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Address;

use Exception;
use BitWasp\Bech32;
use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;
use UmiTop\UmiCore\Util\Converter;

class Address implements AddressInterface
{
    private const VERSION_OFFSET = 0;
    private const VERSION_LENGTH = 2;
    private const PUBKEY_OFFSET = 2;
    private const PUBKEY_LENGTH = 32;
    private const FIFTEEN_BITS = 0x7FFF; // 01111111_11111111 ($x >> 1 << 1)
    private const FIRST_BIT = 0x8000;    // 10000000_00000000 (1 << 15)

    private string $bytes;

    public function __construct(string $bytes = null)
    {
        if ($bytes === null) {
            $this->bytes = str_repeat("\x0", self::ADDRESS_LENGTH);
            $this->setVersion(self::VERSION_UMI_BASIC);
        } elseif (strlen($bytes) !== self::ADDRESS_LENGTH) {
            throw new Exception(
                sprintf('address size should be %d bytes', self::ADDRESS_LENGTH)
            );
        } else {
            $this->bytes = $bytes;
        }
    }

    public function getVersion(): int
    {
        return intval(unpack('n', substr($this->bytes, self::VERSION_OFFSET, self::VERSION_LENGTH))[1]);
    }

    public function setVersion(int $version): self
    {
        $version = intval($this->getVersion() & self::FIRST_BIT) + intval($version & self::FIFTEEN_BITS);
        $this->bytes = substr_replace(
            $this->bytes,
            pack('n', $version), // unsigned short, big endian
            self::VERSION_OFFSET,
            self::VERSION_LENGTH
        );

        return $this;
    }

    public function getPrefix(): string
    {
        return Converter::versionToPrefix($this->getVersion());
    }

    public function setPrefix(string $prefix): self
    {
        return $this->setVersion(Converter::prefixToVersion($prefix));
    }

    public function getPublicKey(): PublicKeyInterface
    {
        return new PublicKey(
            substr($this->bytes, self::PUBKEY_OFFSET, self::PUBKEY_LENGTH)
        );
    }

    public function setPublicKey(PublicKeyInterface $publicKey): AddressInterface
    {
        $this->bytes = substr_replace(
            $this->bytes,
            $publicKey->toBytes(),
            self::PUBKEY_OFFSET,
            self::PUBKEY_LENGTH
        );

        return $this;
    }

    public function fromBech32(string $address): self
    {
        /**
         * @var string $prefix
         * @var array<array-key, int> $words
         */
        [$prefix, $words] = Bech32\decode($address);
        $pubKey = array_reduce(
            Bech32\convertBits($words, count($words), 5, 8, false),
            function (string $carry, int $item): string {
                $carry .= chr($item);
                return $carry;
            },
            ''
        );

        if (strlen($pubKey) !== self::PUBKEY_LENGTH) {
            throw new Exception(
                sprintf('data should be %d bytes', self::PUBKEY_LENGTH)
            );
        }

        $this->bytes = substr_replace($this->bytes, $pubKey, self::PUBKEY_OFFSET, self::PUBKEY_LENGTH);

        $this->setPrefix($prefix);

        return $this;
    }

    public function toBytes(): string
    {
        return $this->bytes;
    }

    public function toBech32(): string
    {
        $data = array_map(
            function (string $value): int {
                return ord($value);
            },
            str_split(substr($this->bytes, self::PUBKEY_OFFSET, self::PUBKEY_LENGTH))
        );

        return Bech32\encode($this->getPrefix(), Bech32\convertBits($data, count($data), 8, 5, true));
    }

    public function __toString(): string
    {
        return $this->toBech32();
    }

    /**
     * @return array{bech32: string}
     */
    public function __debugInfo(): array
    {
        return [
            'bech32' => $this->toBech32()
        ];
    }
}
