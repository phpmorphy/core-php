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
use UmiTop\UmiCore\Util\Converter;

/**
 * Class Address
 */
class Address implements AddressInterface
{
    /** @var int */
    public const LENGTH = 34;

    /** @var string */
    private string $bytes;

    /**
     * Address constructor.
     * @param string|null $bytes Адрес в бинарном виде. Опциональный параметр.
     * @throws Exception Ошибка в случае некорректной длины.
     */
    public function __construct(string $bytes = null)
    {
        if ($bytes === null) {
            $bytes = str_repeat("\x0", self::LENGTH);
        }

        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('bytes size should be 34 bytes');
        }

        $this->bytes = $bytes;
    }

    /**
     * @param string $address Адрес в формате Bech32.
     * @return AddressInterface
     * @throws Exception Ошибка в случае если адрес имеет некорректный формат.
     */
    public static function fromBech32(string $address): AddressInterface
    {
        $bech32 = new Bech32();
        $bytes = $bech32->decode($address);

        return new Address($bytes);
    }

    /**
     * @param string $bytes Адрес в бинарном виде.
     * @return AddressInterface
     * @throws Exception Ошибка в случае некорректной длины.
     */
    public static function fromBytes(string $bytes): AddressInterface
    {
        return new Address($bytes);
    }

    /**
     * @param KeyInterface $key Приватный или публичный ключ.
     * @return AddressInterface
     */
    public static function fromKey(KeyInterface $key): AddressInterface
    {
        $adr = new Address();

        return $adr->setPrefix('umi')->setPublicKey($key->getPublicKey());
    }

    /**
     * @return string
     * @throws Exception Ошибка в случае если префикс не проходит валидацию.
     */
    public function getPrefix(): string
    {
        $cnv = new Converter();

        return $cnv->versionToPrefix($this->getVersion());
    }

    /**
     * @param string $prefix Префикс. Три байта лайтинцы в нижнем регистре.
     * @return AddressInterface
     * @throws Exception Ошибка в случае, если префикс не проходит валидацию.
     */
    public function setPrefix(string $prefix): AddressInterface
    {
        $cnv = new Converter();

        return $this->setVersion($cnv->prefixToVersion($prefix));
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
     * @return integer
     */
    public function getVersion(): int
    {
        // Version - uin16, first 2 bytes.
        return ((ord($this->bytes[0]) << 8) + ord($this->bytes[1]));
    }

    /**
     * @param integer $version Версия в числовом виде.
     * @return AddressInterface
     * @throws Exception Ошибка в случае если версия не проходит валидацию.
     */
    public function setVersion(int $version): AddressInterface
    {
        // Validation.
        $cnv = new Converter();
        $cnv->versionToPrefix($version);

        $this->bytes[0] = chr($version >> 8 & 0xff);
        $this->bytes[1] = chr($version & 0xff);

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
