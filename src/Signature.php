<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


/**
 * Class Signature
 * @package UmiTop\UmiCore
 */
class Signature
{
    /**
     * @param string $message
     * @param string $mnemonic
     * @return string
     * @throws \Exception
     */
    public static function signWithMnemonic(string $message, string $mnemonic): string
    {
        return self::signWithSecretKey($message, Mnemonic::toSecretKey($mnemonic));
    }

    /**
     * @param string $message
     * @param string $secretKey
     * @return string
     */
    public static function signWithSecretKey(string $message, string $secretKey): string
    {
        return sodium_crypto_sign_detached($message, $secretKey);
    }
}
