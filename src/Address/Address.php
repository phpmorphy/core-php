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

/**
 * Class Address
 */
class Address implements AddressInterface
{
    use ConverterTrait;

    /** @var int */
    public const LENGTH = 34;

    /** @var string */
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
     * @param string $address Адрес в формате Bech32.
     * @return AddressInterface
     * @throws Exception Ошибка в случае если адрес имеет некорректный формат.
     */
    public static function fromBech32(string $address): AddressInterface
    {
        $adr = new Address();

        return $adr->setBech32($address);
    }

    /**
     * @param string $bytes Адрес в бинарном виде.
     * @return AddressInterface
     * @throws Exception Ошибка в случае некорректной длины.
     */
    public static function fromBytes(string $bytes): AddressInterface
    {
        $adr = new Address();

        return $adr->setBytes($bytes);
    }

    /**
     * @param KeyInterface $key Приватный или публичный ключ.
     * @return AddressInterface
     */
    public static function fromKey(KeyInterface $key): AddressInterface
    {
        $adr = new Address();

        return $adr->setPublicKey($key->getPublicKey());
    }

    /**
     * @param string $bytes
     * @return AddressInterface
     * @throws Exception
     */
    public function setBytes(string $bytes): AddressInterface
    {
        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('bytes size should be 34 bytes');
        }

        $this->bytes = $bytes;

        return $this;
    }

    /**
     * @param string $address
     * @return AddressInterface
     * @throws Exception
     */
    public function setBech32(string $address): AddressInterface
    {
        $bech32 = new Bech32();

        return $this->setBytes($bech32->decode($address));
    }

    /**
     * @return string
     * @throws Exception Ошибка в случае если префикс не проходит валидацию.
     */
    public function getPrefix(): string
    {
        return $this->versionToPrefix((ord($this->bytes[0]) << 8) + ord($this->bytes[1]));
    }

    /**
     * @param string $prefix Префикс. Три байта лайтинцы в нижнем регистре.
     * @return AddressInterface
     * @throws Exception Ошибка в случае, если префикс не проходит валидацию.
     */
    public function setPrefix(string $prefix): AddressInterface
    {
        $version = $this->prefixToVersion($prefix);
        $this->bytes[0] = chr($version >> 8 & 0xff);
        $this->bytes[1] = chr($version & 0xff);

        return $this;
    }

    /**
     * @return PublicKeyInterface
     * @throws Exception Ошибка, в случае если прубличный ключ не был установлен.
     */
    public function getPublicKey(): PublicKeyInterface
    {
        return new PublicKey(substr($this->bytes, 2, 32));
    }

    /**
     * @param PublicKeyInterface $publicKey Публичный ключ.
     * @return AddressInterface
     */
    public function setPublicKey(PublicKeyInterface $publicKey): AddressInterface
    {
        $this->bytes = substr_replace($this->bytes, $publicKey->toBytes(), 2, 32);

        return $this;
    }

    /**
     * @return string
     */
    public function toBech32(): string
    {
        $bech32 = new Bech32();

        return $bech32->encode($this->bytes);
    }

    /**
     * @return string
     */
    public function toBytes(): string
    {
        return $this->bytes;
    }
}
