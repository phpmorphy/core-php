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
use UmiTop\UmiCore\Util\ConverterTrait;
use UmiTop\UmiCore\Util\ValidatorTrait;

/**
 * Class Transaction
 * @package UmiTop\UmiCore\Transaction
 */
class Transaction implements TransactionInterface
{
    use ValidatorTrait;
    use ConverterTrait;

    /** @var int */
    public const LENGTH = 150;

    /** @var string */
    private $bytes;

    /**
     * Transaction constructor.
     */
    public function __construct()
    {
        $this->bytes = str_repeat("\x0", self::LENGTH);
        $this->setVersion(self::BASIC);
    }

    /**
     * @param string $bytes Транзакция в бинарном виде.
     * @return TransactionInterface
     * @throws Exception
     */
    public static function fromBytes(string $bytes): TransactionInterface
    {
        $trx = new Transaction();

        return $trx->setBytes($bytes);
    }

    /**
     * @return string
     */
    public function getBytes(): string
    {
        return $this->bytes;
    }

    /**
     * @param string $bytes
     * @return TransactionInterface
     * @throws Exception
     */
    public function setBytes(string $bytes): TransactionInterface
    {
        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('bytes length must be 150 bytes');
        }
        $this->bytes = $bytes;

        return $this;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getFeePercent(): int
    {
        $this->checkVersion([2, 3]);

        return $this->bytesToUint16(substr($this->bytes, 39, 2));
    }

    /**
     * @param int $percent Комиссия в сотых долях процента с шагом в 0.01%.
     * Валидные значения от 0 до 2000 (соответственно от 0% до 20%).
     * @return TransactionInterface
     * @throws Exception
     */
    public function setFeePercent(int $percent): TransactionInterface
    {
        $this->checkVersion([2, 3]);
        $this->validateInt($percent, 0, 2000);
        $this->bytes = substr_replace($this->bytes, $this->uint16ToBytes($percent), 39, 2);

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
     * @throws Exception
     */
    public function getName(): string
    {
        $this->checkVersion([2, 3]);
        $this->validateInt(ord($this->bytes[41]), 0, 35);

        return substr($this->bytes, 42, ord($this->bytes[41]));
    }

    /**
     * @param string $name Название структуры.
     * @return TransactionInterface
     * @throws Exception
     */
    public function setName(string $name): TransactionInterface
    {
        $this->checkVersion([2, 3]);
        $this->validateInt(strlen($name), 0, 35);

        $this->bytes[41] = chr(strlen($name));
        $this->bytes = substr_replace($this->bytes, str_repeat("\x0", 35), 42, 35); // wipe
        $this->bytes = substr_replace($this->bytes, $name, 42, strlen($name));

        return $this;
    }

    /**
     * @return int
     */
    public function getNonce(): int
    {
        return $this->bytesToInt64(substr($this->bytes, 77, 8));
    }

    /**
     * @param int $nonce Nonce.
     * @return TransactionInterface
     */
    public function setNonce(int $nonce): TransactionInterface
    {
        $this->bytes = substr_replace($this->bytes, $this->int64ToBytes($nonce), 77, 8);

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPrefix(): string
    {
        $this->checkVersion([2, 3]);

        return $this->versionToPrefix($this->bytesToUint16(substr($this->bytes, 35, 2)));
    }

    /**
     * @param string $prefix Префикс. Три символа латиницы в нижнем регистре.
     * @return TransactionInterface
     * @throws Exception
     */
    public function setPrefix(string $prefix): TransactionInterface
    {
        $this->checkVersion([2, 3]);
        $this->bytes = substr_replace($this->bytes, $this->uint16ToBytes($this->prefixToVersion($prefix)), 35, 2);

        return $this;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getProfitPercent(): int
    {
        $this->checkVersion([2, 3]);

        return $this->bytesToUint16(substr($this->bytes, 37, 2));
    }

    /**
     * @param integer $percent Профит в сотых долях процента с шагом в 0.01%.
     * Валидные значения от 100 до 500 (соответственно от 1% до 5%).
     * @return TransactionInterface
     * @throws Exception
     */
    public function setProfitPercent(int $percent): TransactionInterface
    {
        $this->checkVersion([2, 3]);
        $this->validateInt($percent, 100, 500);
        $this->bytes = substr_replace($this->bytes, $this->uint16ToBytes($percent), 37, 2);

        return $this;
    }

    /**
     * @return AddressInterface
     * @throws Exception
     */
    public function getRecipient(): AddressInterface
    {
        $this->checkVersion([0, 1, 4, 5, 6, 7]);
        $adr = new Address();

        return $adr->setBytes(substr($this->bytes, 35, 34));
    }

    /**
     * @param AddressInterface $address
     * @return TransactionInterface
     * @throws Exception
     */
    public function setRecipient(AddressInterface $address): TransactionInterface
    {
        $this->checkVersion([0, 1, 4, 5, 6, 7]);
        $this->bytes = substr_replace($this->bytes, $address->getBytes(), 35, 34);

        return $this;
    }

    /**
     * @return AddressInterface
     * @throws Exception
     */
    public function getSender(): AddressInterface
    {
        $adr = new Address();

        return $adr->setBytes(substr($this->bytes, 1, 34));
    }

    /**
     * @param AddressInterface $address
     * @return TransactionInterface
     */
    public function setSender(AddressInterface $address): TransactionInterface
    {
        $this->bytes = substr_replace($this->bytes, $address->getBytes(), 1, 34);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return substr($this->bytes, 85, 64);
    }

    /**
     * @param string $signature
     * @return TransactionInterface
     */
    public function setSignature(string $signature): TransactionInterface
    {
        $this->bytes = substr_replace($this->bytes, $signature, 85, strlen($signature));

        return $this;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getValue(): int
    {
        $this->checkVersion([0, 1]);

        return $this->bytesToInt64(substr($this->bytes, 69, 8));
    }

    /**
     * @param int $value
     * @return TransactionInterface
     * @throws Exception
     */
    public function setValue(int $value): TransactionInterface
    {
        $this->checkVersion([0, 1]);
        $this->bytes = substr_replace($this->bytes, $this->int64ToBytes($value), 69, 8);

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
     * @throws Exception
     */
    public function setVersion(int $version): TransactionInterface
    {
        $this->validateInt($version, 0, 7);
        $this->bytes[0] = chr($version);

        return $this;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getPowBits(): int
    {
        // Unsigned length = 85.
        $hash = hash('sha256', substr($this->bytes, 0, 85), true);
        $tail = ((ord($hash[29]) << 16) + (ord($hash[30]) << 8) + ord($hash[31]));

        $mask = 0xffffff;
        $bits = 24;

        while ($tail & $mask) {
            $bits -= 1;
            $mask >>= 1;
        }

        return $bits;
    }

    /**
     * @param SecretKeyInterface $secretKey
     * @param int $powBits
     * @return TransactionInterface
     * @throws Exception
     */
    public function sign(SecretKeyInterface $secretKey, int $powBits = null): TransactionInterface
    {
        $powBits = (int)$powBits;
        $this->validateInt($powBits, 0, 24);

        $mask = 0xffffff >> (24 - $powBits);
        do {
            $csec = (int)(microtime(true) * 100);

            // Nonce offset - 77.
            $this->bytes[80] = chr(($csec >> 32) & 0xff);
            $this->bytes[81] = chr(($csec >> 24) & 0xff);
            $this->bytes[82] = chr(($csec >> 16) & 0xff);
            $this->bytes[83] = chr(($csec >> 8) & 0xff);
            $this->bytes[84] = chr($csec & 0xff);

            $iter = 0;
            do {
                $this->bytes[77] = chr(($iter >> 16) & 0xff);
                $this->bytes[78] = chr(($iter >> 8) & 0xff);
                $this->bytes[79] = chr($iter & 0xff);

                $hash = hash('sha256', substr($this->bytes, 0, 85), true);
                $tail = (ord($hash[29]) << 16) + (ord($hash[30]) << 8) + ord($hash[31]);

                $iter++;
            } while (($tail & $mask) && ($iter < 0xffffff));
        } while ($iter === 0xffffff);

        // Unsigned length = 85.
        return $this->setSignature($secretKey->sign(substr($this->bytes, 0, 85)));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function verify(): bool
    {
        // Unsigned length = 85.
        return $this->getSender()
            ->getPublicKey()
            ->verifySignature($this->getSignature(), substr($this->bytes, 0, 85));
    }

    /**
     * @param int[] $versions
     * @throws Exception
     */
    private function checkVersion(array $versions): void
    {
        if (!in_array($this->getVersion(), $versions, true)) {
            throw new Exception('incorrect version');
        }
    }
}
