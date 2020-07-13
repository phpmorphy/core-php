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

use UmiTop\UmiCore\Key\PublicKeyInterface;

/**
 * Interface AddressInterface
 * @package UmiTop\UmiCore\Address
 */
interface AddressInterface
{
    /**
     * Адрес в формате Bech32, длина 62 или 65 символов.
     * @return string
     */
    public function getBech32(): string;

    /**
     * Устанавливает адрес из строки в формате Bech32 и возвращает $this.
     * @param string $bech32
     * @return AddressInterface
     */
    public function setBech32(string $bech32): AddressInterface;

    /**
     * Адрес в бинарном виде, длина 34 байта.
     * @return string
     */
    public function getBytes(): string;

    /**
     * Устанавливает адрес из бинарной строки и возвращает $this.
     * @param string $bytes Адрес в бинарном виде, длина 34 байта.
     * @return AddressInterface
     */
    public function setBytes(string $bytes): AddressInterface;

    /**
     * Префикс адреса, три символа латиницы в нижнем регистре.
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Устанавливает префикс адреса и возвращает $this.
     * @param string $prefix Префикс. Три символа латиницы в нижнем регистре.
     * @return AddressInterface
     */
    public function setPrefix(string $prefix): AddressInterface;

    /**
     * Публичный ключ.
     * @return PublicKeyInterface
     */
    public function getPublicKey(): PublicKeyInterface;

    /**
     * Устанавливает публичный ключи и возвращает $this.
     * @param PublicKeyInterface $publicKey Публичный ключ.
     * @return AddressInterface
     */
    public function setPublicKey(PublicKeyInterface $publicKey): AddressInterface;
}
