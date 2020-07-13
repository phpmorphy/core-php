<?php

/**
 * Copyright (c) 2020 UMI
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace UmiTop\UmiCore\Address;

use Exception;
use UmiTop\UmiCore\Key\KeyInterface;
use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;
use UmiTop\UmiCore\Util\Bech32;
use UmiTop\UmiCore\Util\ConverterTrait;
use UmiTop\UmiCore\Util\ValidatorTrait;

/**
 * Класс для работы с адресами.
 * @package UmiTop\UmiCore\Address
 */
class Address implements AddressInterface
{
    use ConverterTrait;
    use ValidatorTrait;

    /** @var int Длина адреса в байтах. */
    public const LENGTH = 34;

    /** @var string Адрес в бинарном виде. */
    private $bytes;

    /**
     * Address constructor.
     */
    public function __construct()
    {
        $this->bytes = str_repeat("\x0", self::LENGTH);
        $this->setPrefix('umi');
    }

    /**
     * Статический метод, создает объект из адреса в формате Bech32.
     * @param string $address Адрес в формате Bech32.
     * @return AddressInterface
     * @throws Exception
     */
    public static function fromBech32(string $address): AddressInterface
    {
        $adr = new Address();

        return $adr->setBech32($address);
    }

    /**
     * Статический метод, создает объект из бинарного представления.
     * @param string $bytes Адрес в бинарном виде.
     * @return AddressInterface
     * @throws Exception
     */
    public static function fromBytes(string $bytes): AddressInterface
    {
        $adr = new Address();

        return $adr->setBytes($bytes);
    }

    /**
     * Статический метод, создает объект из публичного или приватного ключа.
     * @param KeyInterface $key Приватный или публичный ключ.
     * @return AddressInterface
     */
    public static function fromKey(KeyInterface $key): AddressInterface
    {
        $adr = new Address();

        return $adr->setPublicKey($key->getPublicKey());
    }

    /**
     * Адрес в формате Bech32, длина 62 или 65 символов.
     * @return string
     */
    public function getBech32(): string
    {
        $bech32 = new Bech32();

        return $bech32->encode($this->bytes);
    }

    /**
     * Устанавливает адрес из строки в формате Bech32 и возвращает $this.
     * @param string $address Адрес в формате Bech32.
     * @return AddressInterface
     * @throws Exception
     */
    public function setBech32(string $address): AddressInterface
    {
        $bech32 = new Bech32();

        return $this->setBytes($bech32->decode($address));
    }

    /**
     * Адрес в бинарном виде, длина 34 байта.
     * @return string
     */
    public function getBytes(): string
    {
        return $this->bytes;
    }

    /**
     * Устанавливает адрес из бинарной строки и возвращает $this.
     * @param string $bytes Адрес в бинарном виде, длина 34 байта.
     * @return AddressInterface
     * @throws Exception
     */
    public function setBytes(string $bytes): AddressInterface
    {
        $this->validateStr($bytes, self::LENGTH);
        $this->bytes = $bytes;

        return $this;
    }

    /**
     * Префикс адреса, три символа латиницы в нижнем регистре.
     * @return string
     * @throws Exception
     */
    public function getPrefix(): string
    {
        return $this->bytesToPrefix(substr($this->bytes, 0, 2));
    }

    /**
     * Устанавливает префикс адреса и возвращает $this.
     * @param string $prefix Префикс. Три символа латиницы в нижнем регистре.
     * @return AddressInterface
     * @throws Exception
     */
    public function setPrefix(string $prefix): AddressInterface
    {
        $this->bytes = substr_replace($this->bytes, $this->prefixToBytes($prefix), 0, 2);

        return $this;
    }

    /**
     * Публичный ключ.
     * @return PublicKeyInterface
     */
    public function getPublicKey(): PublicKeyInterface
    {
        return new PublicKey(substr($this->bytes, 2, 32));
    }

    /**
     * Устанавливает публичный ключи и возвращает $this.
     * @param PublicKeyInterface $publicKey Публичный ключ.
     * @return AddressInterface
     */
    public function setPublicKey(PublicKeyInterface $publicKey): AddressInterface
    {
        $this->bytes = substr_replace($this->bytes, $publicKey->getBytes(), 2, 32);

        return $this;
    }
}
