<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


/**
 * Interface AddressInterface
 * @package UmiTop\UmiCore
 */
interface AddressInterface
{
    public const TYPE_GENESIS = 0x0000;
    public const TYPE_UMI = 0x0001;
    public const TYPE_ROY = 0x0002;
    public const AVAILABLE_TYPES = [
        self::TYPE_UMI,
        self::TYPE_ROY,
    ];

    public const TAG_UMI = 'umi';
    public const TAG_ROY = 'roy';
    public const AVAILABLE_TAGS = [
        self::TAG_UMI,
        self::TAG_ROY,
    ];

    /**
     * AddressInterface constructor.
     * @param int $type
     * @param string $publicKey
     */
    public function __construct(int $type, string $publicKey);

    /**
     * @param string $bech32
     * @return AddressInterface
     */
    public static function fromBech32(string $bech32): AddressInterface;

    /**
     * @param string $hex
     * @return AddressInterface
     */
    public static function fromHex(string $hex): AddressInterface;

    /**
     * @param string $mnemonic
     * @param int $type
     * @return AddressInterface
     */
    public static function fromMnemonic(string $mnemonic, int $type = nullI): AddressInterface;

    /**
     * @param string $publicKey
     * @param int $type
     * @return AddressInterface
     */
    public static function fromPublicKey(string $publicKey, int $type = null): AddressInterface;

    /**
     * @param string $raw
     * @return AddressInterface
     */
    public static function fromRaw(string $raw): AddressInterface;

    /**
     * @param string $secretKey
     * @param int $type
     * @return AddressInterface
     */
    public static function fromSecretKey(string $secretKey, int $type = null): AddressInterface;

    /**
     * @return string
     */
    public function getPublicKey(): string;

    /**
     * @param string $tag
     * @return string
     */
    public function toBech32(string $tag = self::TAG_UMI): string;

    /**
     * @return string
     */
    public function toHex(): string;

    /**
     * @return string
     */
    public function toRaw(): string;

    /**
     * @param string $publicKey
     * @return AddressInterface
     */
    public function withPublicKey(string $publicKey): AddressInterface;

    /**
     * @param int $type
     * @return AddressInterface
     */
    public function withType(int $type): AddressInterface;
}
