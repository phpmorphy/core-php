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
use UmiTop\UmiCore\Key\SecretKeyInterface;
use UmiTop\UmiCore\Util\Ed25519\Ed25519;

/**
 * Класс для работы с приватными ключами.
 * @package UmiTop\UmiCore\Key\Ed25519
 */
class SecretKey implements SecretKeyInterface
{
    /** @var int Длина приватного ключа в байтах. */
    public const LENGTH = 64;

    /** @var string Приватный ключ в бинарном виде. */
    private $bytes;

    /**
     * SecretKey constructor.
     * @param string $bytes Приватный ключ в бинарном виде. В формате libsodium, 64 байта.
     * @throws Exception
     */
    public function __construct(string $bytes)
    {
        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('secret key size should be 64 bytes');
        }

        $this->bytes = $bytes;
    }

    /**
     * Статический метод, создающий приватный ключ из seed.
     * Libsodium принимает seed длиной 32 байта, поэтому если длина отличается, то берется sha256 хэш.
     * @param string $seed Бинарная строка любой длины.
     * @return SecretKeyInterface
     * @codeCoverageIgnore
     */
    public static function fromSeed(string $seed): SecretKeyInterface
    {
        if (strlen($seed) !== Ed25519::SEED_BYTES) {
            $seed = hash('sha256', $seed, true);
        }

        if (function_exists('sodium_crypto_sign_seed_keypair') === true) {
            $bytes = sodium_crypto_sign_secretkey(
                sodium_crypto_sign_seed_keypair($seed)
            );

            return new SecretKey($bytes);
        }

        $ed25519 = new Ed25519();
        $bytes = $ed25519->secretKeyFromSeed($seed);

        return new SecretKey($bytes);
    }

    /**
     * Приватный ключ в бинарном виде. В формате libsodium, 64 байта.
     * @return string
     */
    public function getBytes(): string
    {
        return $this->bytes;
    }

    /**
     * Публичный ключ, соответствующий приватному ключу.
     * @return PublicKeyInterface
     */
    public function getPublicKey(): PublicKeyInterface
    {
        return new PublicKey(substr($this->bytes, 32, 32));
    }

    /**
     * Создает цифровую подпись сообщения.
     * @param string $message Сообщение в бинарном виде.
     * @return string
     * @codeCoverageIgnore
     */
    public function sign(string $message): string
    {
        if (function_exists('sodium_crypto_sign_detached') === true) {
            return sodium_crypto_sign_detached($message, $this->bytes);
        }

        $ed25519 = new Ed25519();

        return $ed25519->sign($message, $this->bytes);
    }
}
