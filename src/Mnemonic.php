<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;


/**
 * Class Mnemonic
 * @package UmiTop\UmiCore
 */
class Mnemonic
{
    /**
     * @param string $mnemonic
     * @return string
     * @throws \Exception
     */
    public static function toSeed(string $mnemonic): string
    {
        // Преобразуем мнемонику в 512 битный (64 байта) сид как описано в bip39
        // https://github.com/bitcoin/bips/blob/master/bip-0039.mediawiki
        $seed64 = (new Bip39SeedGenerator())->getSeed($mnemonic);

        // Сжимаем 512 битный сид до 256 битного
        return hash('sha3-256', $seed64->getBinary(), true);
    }

    /**
     * @param string $mnemonic
     * @return string
     * @throws \Exception
     */
    public static function toPublicKey(string $mnemonic): string
    {
        $signPair = sodium_crypto_sign_seed_keypair(self::toSeed($mnemonic));

        return sodium_crypto_sign_publickey($signPair);
    }

    /**
     * @param string $mnemonic
     * @return string
     * @throws \Exception
     */
    public static function toSecretKey(string $mnemonic): string
    {
        $signPair = sodium_crypto_sign_seed_keypair(self::toSeed($mnemonic));

        return sodium_crypto_sign_secretkey($signPair);
    }
}
