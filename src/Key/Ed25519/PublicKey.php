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

namespace UmiTop\UmiCore\Key\Ed25519;

use Exception;
use UmiTop\UmiCore\Key\PublicKeyInterface;
use UmiTop\UmiCore\Util\Ed25519\Ed25519;

/**
 * Класс для работы с публичными ключами.
 * @package UmiTop\UmiCore\Key\Ed25519
 */
class PublicKey implements PublicKeyInterface
{
    /** @var int Длина публичного ключа в байтах. */
    public const LENGTH = 32;

    /** @var string Публичный ключ в бинарном виде. */
    private $bytes;

    /**
     * PublicKey constructor.
     * @param string $bytes Публичный ключ в бинарном виде, в формате libsodium.
     * @throws Exception
     */
    public function __construct(string $bytes)
    {
        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('public key size should be 32 bytes');
        }

        $this->bytes = $bytes;
    }

    /**
     * Публичный ключ в формате libsodium, длина 32 байта.
     * @return string
     */
    public function getBytes(): string
    {
        return $this->bytes;
    }

    /**
     * Публичный ключ.
     * @return PublicKeyInterface
     */
    public function getPublicKey(): PublicKeyInterface
    {
        return $this;
    }

    /**
     * Проверяет цифровую подпись.
     * @param string $signature Подпись в бинарном виде.
     * @param string $message Сообщение в бинарном виде.
     * @return bool
     * @codeCoverageIgnore
     */
    public function verifySignature(string $signature, string $message): bool
    {
        if (function_exists('sodium_crypto_sign_verify_detached') === true) {
            return sodium_crypto_sign_verify_detached($signature, $message, $this->bytes);
        }

        $ed25519 = new Ed25519();

        return $ed25519->verify($signature, $message, $this->bytes);
    }
}
