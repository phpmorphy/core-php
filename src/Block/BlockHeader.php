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

namespace UmiTop\UmiCore\Block;

use Exception;
use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Key\PublicKeyInterface;
use UmiTop\UmiCore\Key\SecretKeyInterface;

/**
 * Class BlockHeader
 */
class BlockHeader implements BlockHeaderInterface
{
    /** @var int */
    public const GENESIS = 0;

    /** @var int */
    public const BASIC = 1;

    /** @var int */
    public const LENGTH = 167;

    /** @var string */
    private string $bytes;

    /**
     * BlockHeader constructor.
     * @param string|null $bytes
     * @throws Exception
     */
    public function __construct(string $bytes = null)
    {
        if ($bytes === null) {
            $bytes = str_repeat("\x0", self::LENGTH);
        }

        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('некорректная длина');
        }

        $this->bytes = $bytes;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return hash('sha256', $this->bytes, true);
    }

    /**
     * @return integer
     */
    public function getVersion(): int
    {
        return ord($this->bytes[0]);
    }

    /**
     * @param integer $version
     * @return BlockHeaderInterface
     */
    public function setVersion(int $version): BlockHeaderInterface
    {
        $this->bytes[0] = chr($version);

        return $this;
    }

    /**
     * @return string
     */
    public function getPreviousBlockHash(): string
    {
        // Prev block hash offset = 1.
        // Prev block hash length = 32.
        return substr($this->bytes, 1, 32);
    }

    /**
     * @param string $hash
     * @return BlockHeaderInterface
     */
    public function setPreviousBlockHash(string $hash): BlockHeaderInterface
    {
        // Prev block hash offset = 1.
        // Prev block hash length = 32.
        $this->bytes = substr_replace($this->bytes, $hash, 1, 32);

        return $this;
    }

    /**
     * @return string
     */
    public function getMerkleRootHash(): string
    {
        // Merkle hash offset = 33.
        // Merkle hash offset = 32.
        return substr($this->bytes, 33, 32);
    }

    /**
     * @param string $hash
     * @return BlockHeaderInterface
     */
    public function setMerkleRootHash(string $hash): BlockHeaderInterface
    {
        // Merkle hash offset = 33.
        // Merkle hash offset = 32.
        $this->bytes = substr_replace($this->bytes, $hash, 33, 32);

        return $this;
    }

    /**
     * @return integer
     */
    public function getTimestamp(): int
    {
        // Timestamp offset = 65.
        // Timestamp length = 4.
        return intval(unpack('N', substr($this->bytes, 65, 4))[1]);
    }

    /**
     * @param integer $epoch
     * @return BlockHeaderInterface
     */
    public function setTimestamp(int $epoch): BlockHeaderInterface
    {
        // Timestamp offset = 65.
        // Timestamp length = 4. unsigned long, big endian.
        $this->bytes = substr_replace($this->bytes, pack('N', $epoch), 65, 4);

        return $this;
    }

    /**
     * @return PublicKeyInterface
     */
    public function getPublicKey(): PublicKeyInterface
    {
        // Public key offset = 71.
        // Public key length = 32.
        return new PublicKey(substr($this->bytes, 71, 32));
    }

    /**
     * @param PublicKeyInterface $publicKey
     * @return BlockHeaderInterface
     */
    public function setPublicKey(PublicKeyInterface $publicKey): BlockHeaderInterface
    {
        // Public key offset = 71.
        // Public key length = 32.
        $this->bytes = substr_replace($this->bytes, $publicKey->toBytes(), 71, 32);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        // Signature offset = 103.
        // Signature length = 64.
        return substr($this->bytes, 103, 64);
    }

    /**
     * @param string $signature
     * @return BlockHeaderInterface
     */
    public function setSignature(string $signature): BlockHeaderInterface
    {
        // Signature offset = 103.
        // Signature length = 64.
        $this->bytes = substr_replace($this->bytes, $signature, 103, 64);

        return $this;
    }

    /**
     * @return int
     */
    public function getTransactionCount(): int
    {
        // Tx count offset = 69.
        // Tx count length = 2.
        return intval(unpack('n', substr($this->bytes, 69, 2))[1]);
    }

    /**
     * @param integer $count
     * @return BlockHeaderInterface
     * @throws Exception
     */
    public function setTransactionCount(int $count): BlockHeaderInterface
    {
        if ($count < 0 || $count > 0xffff) {
            throw new Exception('invalid count');
        }

        // Tx count offset = 69.
        // Tx count length = 2. unsigned short, big endian.
        $this->bytes = substr_replace($this->bytes, pack('n', $count), 69, 2);

        return $this;
    }

    /**
     * @param SecretKeyInterface $secretKey
     * @return BlockHeaderInterface
     */
    public function sign(SecretKeyInterface $secretKey): BlockHeaderInterface
    {
        // Unsigned offset = 0.
        // Unsigned length = 103.
        $this->setPublicKey($secretKey->getPublicKey());
        $this->setSignature($secretKey->sign(substr($this->bytes, 0, 103)));

        return $this;
    }

    /**
     * @return bool
     */
    public function verify(): bool
    {
        // Unsigned offset = 0.
        // Unsigned length = 103.
        return $this->getPublicKey()->verifySignature($this->getSignature(), substr($this->bytes, 0, 103));
    }

    /**
     * @return string
     */
    public function toBytes(): string
    {
        return $this->bytes;
    }
}
