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

namespace UmiTop\UmiCore\Transaction;

use Exception;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Address\AddressInterface;
use UmiTop\UmiCore\Key\SecretKeyInterface;
use UmiTop\UmiCore\Util\Converter;

/**
 * Class Transaction
 * @package UmiTop\UmiCore\Transaction
 */
class Transaction implements TransactionInterface
{
    /** @var int */
    public const LENGTH = 150;

    /** @var string */
    private string $bytes;

    /** @var array */
    private array $fields;

    /**
     * Transaction constructor.
     * @param string|null $bytes (optional)
     * @throws Exception
     */
    public function __construct(string $bytes = null)
    {
        if ($bytes === null) {
            $bytes = str_repeat("\x0", self::LENGTH);
        }

        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('bytes length must be 150 bytes');
        }

        $this->bytes = $bytes;
    }

    /**
     * @param string $bytes
     * @return TransactionInterface
     * @throws Exception
     */
    public static function fromBytes(string $bytes): TransactionInterface
    {
        return new Transaction($bytes);
    }

    /**
     * @param string $base64
     * @return TransactionInterface
     * @throws Exception
     */
    public static function fromBase64(string $base64): TransactionInterface
    {
        $bytes = base64_decode($base64, true);

        if ($bytes === false) {
            throw new Exception('could not decode base64');
        }

        return new Transaction($bytes);
    }

    /**
     * @return int
     */
    public function getFeePercent(): int
    {
        // fee offset - 39
        return (ord($this->bytes[39]) << 8) + ord($this->bytes[40]);
    }

    /**
     * @param int $percent
     * @return TransactionInterface
     * @throws Exception
     */
    public function setFeePercent(int $percent): TransactionInterface
    {
        if ($percent < 0 || $percent > 2000) {
            throw new Exception('incorrect feePercent');
        }

        // fee offset - 39
        $this->bytes[39] = chr($percent >> 8 & 0xff);
        $this->bytes[40] = chr($percent & 0xff);

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
     * @return string
     */
    public function getName(): string
    {
        // name offset - 41
        return substr($this->bytes, 42, ord($this->bytes[41]));
    }

    /**
     * @param string $name
     * @return TransactionInterface
     * @throws Exception
     */
    public function setName(string $name): TransactionInterface
    {
        // name offset - 41
        // name length - 36
        if (strlen($name) >= 36) {
            throw new Exception('name too long');
        }

        $this->bytes[41] = chr(strlen($name));
        $this->bytes = substr_replace($this->bytes, $name, 42, strlen($name));

        return $this;
    }

    /**
     * @return int
     */
    public function getNonce(): int
    {
        // nonce offset - 77
        $nonce = ord($this->bytes[77]) << 56;
        $nonce += ord($this->bytes[78]) << 48;
        $nonce += ord($this->bytes[79]) << 40;
        $nonce += ord($this->bytes[80]) << 32;
        $nonce += ord($this->bytes[81]) << 24;
        $nonce += ord($this->bytes[82]) << 16;
        $nonce += ord($this->bytes[83]) << 8;
        $nonce += ord($this->bytes[84]);

        return $nonce;
    }

    /**
     * @param int $nonce
     * @return TransactionInterface
     */
    public function setNonce(int $nonce): TransactionInterface
    {
        // nonce offset - 77
        $this->bytes[77] = chr($nonce >> 56 & 0xff);
        $this->bytes[78] = chr($nonce >> 48 & 0xff);
        $this->bytes[79] = chr($nonce >> 40 & 0xff);
        $this->bytes[80] = chr($nonce >> 32 & 0xff);
        $this->bytes[81] = chr($nonce >> 24 & 0xff);
        $this->bytes[82] = chr($nonce >> 16 & 0xff);
        $this->bytes[83] = chr($nonce >> 8 & 0xff);
        $this->bytes[84] = chr($nonce & 0xff);

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPrefix(): string
    {
        // prefix offset - 35
        $version = (ord($this->bytes[35]) << 8) + ord($this->bytes[36]);
        $cnv = new Converter();

        return $cnv->versionToPrefix($version);
    }

    /**
     * @param string $prefix
     * @return TransactionInterface
     * @throws Exception
     */
    public function setPrefix(string $prefix): TransactionInterface
    {
        $cnv = new Converter();
        $version = $cnv->prefixToVersion($prefix);

        // prefix offset - 35
        $this->bytes[35] = chr($version >> 8 & 0xff);
        $this->bytes[36] = chr($version & 0xff);

        return $this;
    }

    /**
     * @return int
     */
    public function getProfitPercent(): int
    {
        // profit offset - 37
        return (ord($this->bytes[37]) << 8) + ord($this->bytes[38]);
    }

    /**
     * @param int $percent
     * @return TransactionInterface
     * @throws Exception
     */
    public function setProfitPercent(int $percent): TransactionInterface
    {
        if ($percent < 100 || $percent > 500) {
            throw new Exception('incorrect profitPercent');
        }

        // profit offset - 37
        $this->bytes[37] = chr($percent >> 8 & 0xff);
        $this->bytes[38] = chr($percent & 0xff);

        return $this;
    }

    /**
     * @return AddressInterface
     * @throws Exception
     */
    public function getRecipient(): AddressInterface
    {
        // recipient offset - 35
        // recipient length - 34
        return new Address(substr($this->bytes, 35, 34));
    }

    /**
     * @param AddressInterface $address
     * @return TransactionInterface
     */
    public function setRecipient(AddressInterface $address): TransactionInterface
    {
        // recipient offset - 35
        // recipient length - 34
        $this->bytes = substr_replace($this->bytes, $address->toBytes(), 35, 34);

        return $this;
    }

    /**
     * @return AddressInterface
     * @throws Exception
     */
    public function getSender(): AddressInterface
    {
        // sender offset - 1
        // sender length - 34
        return new Address(substr($this->bytes, 1, 34));
    }

    /**
     * @param AddressInterface $address
     * @return TransactionInterface
     */
    public function setSender(AddressInterface $address): TransactionInterface
    {
        // sender offset - 1
        // sender length - 34
        $this->bytes = substr_replace($this->bytes, $address->toBytes(), 1, 34);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        // signature offset - 85
        // signature length - 64
        return substr($this->bytes, 85, 64);
    }

    /**
     * @param string $signature
     * @return TransactionInterface
     */
    public function setSignature(string $signature): TransactionInterface
    {
        // signature offset - 85
        // signature length - 64
        $this->bytes = substr_replace($this->bytes, $signature, 85, strlen($signature));

        return $this;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        // value offset - 69
        $val = ord($this->bytes[69]) << 56;
        $val += ord($this->bytes[70]) << 48;
        $val += ord($this->bytes[71]) << 40;
        $val += ord($this->bytes[72]) << 32;
        $val += ord($this->bytes[73]) << 24;
        $val += ord($this->bytes[74]) << 16;
        $val += ord($this->bytes[75]) << 8;
        $val += ord($this->bytes[76]);

        return $val;
    }

    /**
     * @param int $value
     * @return TransactionInterface
     * @throws Exception
     */
    public function setValue(int $value): TransactionInterface
    {
        if ($value < 1) {
            throw new Exception('value must be between 1 and 9223372036854775807');
        }

        // value offset - 69
        $this->bytes[69] = chr($value >> 56 & 0xff);
        $this->bytes[70] = chr($value >> 48 & 0xff);
        $this->bytes[71] = chr($value >> 40 & 0xff);
        $this->bytes[72] = chr($value >> 32 & 0xff);
        $this->bytes[73] = chr($value >> 24 & 0xff);
        $this->bytes[74] = chr($value >> 16 & 0xff);
        $this->bytes[75] = chr($value >> 8 & 0xff);
        $this->bytes[76] = chr($value & 0xff);

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return ord($this->bytes[0]);
    }

    /**
     * @param int $version
     * @return TransactionInterface
     */
    public function setVersion(int $version): TransactionInterface
    {
        $this->bytes[0] = chr($version);

        return $this;
    }

    /**
     * @param int $bits
     * @return bool
     * @throws Exception
     */
    public function hasPowBits(int $bits): bool
    {
        if ($bits < 0 || $bits > 24) {
            throw new Exception('bits value must be between 0 and 24');
        }

        // unsigned length = 85
        $hash = hash('sha256', substr(0, 85), true);
        $tail = (ord($hash[29]) << 16) + (ord($hash[30]) << 8) + ord($hash[31]);
        $mask = 0xffffffff >> (24 - $bits);

        return (($tail & $mask) === 0);
    }

    /**
     * @param SecretKeyInterface $secretKey
     * @return TransactionInterface
     */
    public function sign(SecretKeyInterface $secretKey): TransactionInterface
    {
        // unsigned length = 85
        return $this->setSignature($secretKey->sign(substr($this->bytes, 0, 85)));
    }

    /**
     * @return string
     */
    public function toBytes(): string
    {
        return $this->bytes;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function verify(): bool
    {
        // unsigned length = 85
        return $this->getSender()
            ->getPublicKey()
            ->verifySignature($this->getSignature(), substr($this->bytes, 0, 85));
    }
}
