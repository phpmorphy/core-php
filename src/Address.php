<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bech32;


class Address implements AddressInterface
{
    private /*string*/ $publicKey;
    private /*int*/ $type;

    public static function fromMnemonic(string $mnemonic, int $type = self::TYPE_UMI): AddressInterface
    {
        // Преобразуем мнемонику в 512 битный (64 байта) сид как описано в bip39
        // https://github.com/bitcoin/bips/blob/master/bip-0039.mediawiki
        $seed64 = (new Bip39SeedGenerator())->getSeed($mnemonic);

        // Сжимаем 512 битный сид до 256 битного
        $seed32 = hash('sha3-256', $seed64->getBinary(), true);

        // Получаем публичный ключ из сида
        $signPair = sodium_crypto_sign_seed_keypair($seed32);
        $publicKey = sodium_crypto_sign_publickey($signPair);

        return new Address($type, $publicKey);
    }

    public function __construct(int $type = self::TYPE_UMI, string $publicKey = '')
    {
        $this->type = $type;
        $this->publicKey = $publicKey;
    }

    public function withType(int $type = self::TYPE_UMI): AddressInterface
    {
        $new = clone $this;
        $new->type = $type;
        return $new;
    }

    public function withPublicKey(string $publicKey): AddressInterface
    {
        $new = clone $this;
        $new->publicKey = $publicKey;
        return $new;
    }

    public function getBech32(string $tag = self::TAG_UMI): string
    {
        $address = pack('n', $this->type) . $this->publicKey;

        $data = array_map(
            function (string $value): int {
                return ord($value);
            },
            str_split($address)
        );

        return Bech32\encode($tag, Bech32\convertBits($data, count($data), 8, 5, true));
    }
}
