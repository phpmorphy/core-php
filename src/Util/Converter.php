<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Util;

use Exception;

class Converter
{
    private const ASCII_SHIFT = 96;
    private const VERSION_GENESIS = 0x0000;
    private const FIFTEEN_BITS = 0x7FFF;

    public static function versionToPrefix(int $version): string
    {
        if ($version === self::VERSION_GENESIS) {
            return 'genesis';
        }

        if (!self::validateVersion($version)) {
            throw new Exception();
        }

        return sprintf(
            '%c%c%c',
            (($version & 0x7C00) >> 10) + self::ASCII_SHIFT,
            (($version & 0x03E0) >> 5) + self::ASCII_SHIFT,
            ($version & 0x001F) + self::ASCII_SHIFT
        );
    }

    public static function prefixToVersion(string $prefix): int
    {
        if ($prefix === 'genesis') {
            return self::VERSION_GENESIS;
        }

        if (!self::validatePrefix($prefix)) {
            throw new Exception();
        }

        $ver = (ord($prefix[0]) - self::ASCII_SHIFT) << 10;
        $ver += (ord($prefix[1]) - self::ASCII_SHIFT) << 5;
        $ver += (ord($prefix[2]) - self::ASCII_SHIFT);

        return $ver;
    }

    public static function validatePrefix(string $prefix): bool
    {
        return (bool)preg_match('/^(genesis|[a-z]{3})$/', $prefix);
    }

    public static function validateVersion(int $version): bool
    {
        return ($version !== self::FIFTEEN_BITS)
            && ($version & 0x001F) < 27
            && (($version & 0x03E0) >> 5) < 27
            && (($version & 0x7C00) >> 10) < 27;
    }
}
