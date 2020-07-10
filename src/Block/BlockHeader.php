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
    private $bytes;

    /**
     * BlockHeader constructor.
     */
    public function __construct()
    {
        $this->bytes = str_repeat("\x0", self::LENGTH);
    }

    /**
     * @param string $bytes
     * @return BlockHeaderInterface
     * @throws Exception
     */
    public static function fromBytes(string $bytes)
    {
        $hdr = new BlockHeader();

        return $hdr->setBytes($bytes);
    }

    /**
     * @param string $base64
     * @return BlockHeaderInterface
     * @throws Exception
     */
    public static function fromBase64(string $base64)
    {
        $bytes = base64_decode($base64, true);

        if ($bytes === false) {
            throw new Exception('could not decode base64');
        }

        $hdr = new BlockHeader();

        return $hdr->setBytes($bytes);
    }

    /**
     * @param string $bytes
     * @return BlockHeaderInterface
     * @throws Exception
     */
    public function setBytes(string $bytes)
    {
        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('incorrect length');
        }

        $this->bytes = $bytes;

        return $this;
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
        return substr($this->bytes, 1, 32);
    }

    /**
     * @param string $hash
     * @return BlockHeaderInterface
     */
    public function setPreviousBlockHash(string $hash): BlockHeaderInterface
    {
        $this->bytes = substr_replace($this->bytes, $hash, 1, 32);

        return $this;
    }

    /**
     * @return string
     */
    public function getMerkleRootHash(): string
    {
        return substr($this->bytes, 33, 32);
    }

    /**
     * @param string $hash
     * @return BlockHeaderInterface
     */
    public function setMerkleRootHash(string $hash): BlockHeaderInterface
    {
        $this->bytes = substr_replace($this->bytes, $hash, 33, 32);

        return $this;
    }

    /**
     * @return integer
     */
    public function getTimestamp(): int
    {
        return intval(unpack('N', substr($this->bytes, 65, 4))[1]);
    }

    /**
     * @param integer $epoch
     * @return BlockHeaderInterface
     */
    public function setTimestamp(int $epoch): BlockHeaderInterface
    {
        $this->bytes = substr_replace($this->bytes, pack('N', $epoch), 65, 4);

        return $this;
    }

    /**
     * @return PublicKeyInterface
     */
    public function getPublicKey(): PublicKeyInterface
    {
        return new PublicKey(substr($this->bytes, 71, 32));
    }

    /**
     * @param PublicKeyInterface $publicKey
     * @return BlockHeaderInterface
     */
    public function setPublicKey(PublicKeyInterface $publicKey): BlockHeaderInterface
    {
        $this->bytes = substr_replace($this->bytes, $publicKey->toBytes(), 71, 32);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return substr($this->bytes, 103, 64);
    }

    /**
     * @param string $signature
     * @return BlockHeaderInterface
     */
    public function setSignature(string $signature): BlockHeaderInterface
    {
        $this->bytes = substr_replace($this->bytes, $signature, 103, 64);

        return $this;
    }

    /**
     * @return int
     */
    public function getTransactionCount(): int
    {
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

        $this->bytes = substr_replace($this->bytes, pack('n', $count), 69, 2);

        return $this;
    }

    /**
     * @param SecretKeyInterface $secretKey
     * @return BlockHeaderInterface
     */
    public function sign(SecretKeyInterface $secretKey): BlockHeaderInterface
    {
        $this->setPublicKey($secretKey->getPublicKey());
        $this->setSignature($secretKey->sign(substr($this->bytes, 0, 103)));

        return $this;
    }

    /**
     * @return bool
     */
    public function verify(): bool
    {
        return $this->getPublicKey()->verifySignature($this->getSignature(), substr($this->bytes, 0, 103));
    }

    /**
     * @return string
     */
    public function toBytes(): string
    {
        return $this->bytes;
    }

    /**
     * @return string
     */
    public function toBase64(): string
    {
        return base64_encode($this->bytes);
    }
}
