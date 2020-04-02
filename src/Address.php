<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


use BitWasp\Bech32;

/**
 * Class Address
 * @package UmiTop\UmiCore
 */
class Address implements AddressInterface
{
    /** @var string */
    private $publicKey;

    /** @var string */
    private $tag;

    /** @var int */
    private $type;

    /**
     * Address constructor.
     * @param int $type
     * @param string $publicKey
     */
    public function __construct(int $type = null, string $publicKey = null)
    {
        $this->tag = self::TAG_UMI;
        $this->type = $type ?? self::TYPE_UMI;
        $this->publicKey = $publicKey ?? str_repeat('0', 32);
    }

    /**
     * @param string $bech32
     * @return AddressInterface
     * @throws Bech32\Exception\Bech32Exception
     */
    public static function fromBech32(string $bech32): AddressInterface
    {
        $arr = Bech32\decode($bech32);
        $data = Bech32\convertBits($arr[1], count($arr[1]), 5, 8, false);

        $raw = array_map(
            function (int $value): string {
                return chr($value);
            },
            $data
        );

        $address = self::fromRaw(implode('', $raw));
        $address->tag = $arr[0];

        return $address;
    }

    /**
     * @param string $hex
     * @return AddressInterface
     */
    public static function fromHex(string $hex): AddressInterface
    {
        return self::fromRaw(hex2bin($hex));
    }

    /**
     * @param string $mnemonic
     * @param int $type
     * @return AddressInterface
     * @throws \Exception
     */
    public static function fromMnemonic(string $mnemonic, int $type = null): AddressInterface
    {
        return new Address($type ?? self::TYPE_UMI, Mnemonic::toPublicKey($mnemonic));
    }

    /**
     * @param string $raw
     * @return AddressInterface
     */
    public static function fromRaw(string $raw): AddressInterface
    {
        return new Address(
            unpack('n', substr($raw, 0, 2))[1],
            substr($raw, 2, 32)
        );
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $tag
     * @return string
     * @throws Bech32\Exception\Bech32Exception
     */
    public function toBech32(string $tag = null): string
    {
        $address = pack('n', $this->type) . $this->publicKey;

        $data = array_map(
            function (string $value): int {
                return ord($value);
            },
            str_split($address)
        );

        return Bech32\encode($tag ?? $this->tag, Bech32\convertBits($data, count($data), 8, 5, true));
    }

    /**
     * @return string
     */
    public function toHex(): string
    {
        return bin2hex($this->toRaw());
    }

    /**
     * @return string
     */
    public function toRaw(): string
    {
        return pack('n', $this->type) . $this->publicKey;
    }

    /**
     * @param string $publicKey
     * @return AddressInterface
     */
    public function withPublicKey(string $publicKey): AddressInterface
    {
        $new = clone $this;
        $new->publicKey = $publicKey;
        return $new;
    }

    /**
     * @param int $type
     * @return AddressInterface
     */
    public function withType(int $type = null): AddressInterface
    {
        $new = clone $this;
        $new->type = $type ?? self::TYPE_UMI;
        return $new;
    }
}
