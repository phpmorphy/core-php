<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


interface AddressInterface
{
    public const TYPE_GENESIS = 0x0000;
    public const TYPE_UMI = 0x0001;
    public const TYPE_ROY = 0x0002;
    public const AVALIABLE_TYPES = [
        self::TYPE_GENESIS,
        self::TYPE_UMI,
        self::TYPE_ROY,
    ];

    public const TAG_UMI = 'umi';
    public const TAG_ROY = 'roy';
    public const AVALIABLE_TAGS = [
        self::TAG_UMI,
        self::TAG_ROY,
    ];

    public static function fromMnemonic(string $mnemonic, int $type = self::TYPE_UMI): AddressInterface;

    public function __construct(int $type, string $publicKey);

    public function withType(int $type = self::TYPE_UMI): AddressInterface;

    public function withPublicKey(string $publicKey): AddressInterface;

    public function getBech32(string $tag = self::TAG_UMI): string;
}
