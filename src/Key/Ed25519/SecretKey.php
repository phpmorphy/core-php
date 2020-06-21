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
use UmiTop\UmiCore\Util\Ed25519;

/**
 * Class SecretKey
 * @package UmiTop\UmiCore\Key\Ed25519
 */
class SecretKey implements SecretKeyInterface
{
    /** @var int */
    public const LENGTH = Ed25519::SECRET_KEY_BYTES;

    /** @var string */
    private string $bytes;

    /**
     * SecretKey constructor.
     * @param string $bytes
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
     * @param string $seed
     * @return SecretKeyInterface
     * @throws Exception
     */
    public static function fromSeed(string $seed): SecretKeyInterface
    {
        if (strlen($seed) !== Ed25519::SEED_BYTES) {
            $seed = hash('sha256', $seed, true);
        }

        if (function_exists('sodium_crypto_sign_seed_keypair')) {
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
     * @return PublicKeyInterface
     * @throws Exception
     */
    public function getPublicKey(): PublicKeyInterface
    {
        $bytes = substr($this->bytes, 32, 32);

        return new PublicKey($bytes);
    }

    /**
     * @param string $message
     * @return string
     * @throws Exception
     */
    public function sign(string $message): string
    {
        if (function_exists('sodium_crypto_sign_detached')) {
            return sodium_crypto_sign_detached($message, $this->bytes);
        }

        $ed25519 = new Ed25519();

        return $ed25519->sign($message, $this->bytes);
    }

    /**
     * @return string
     */
    public function toBytes(): string
    {
        return $this->bytes;
    }
}
