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
    }

    /**
     * @param string $bytes Транзакция в бинарном виде.
     * @return TransactionInterface
     * @throws Exception Ошибка в случае некорректной длины транзакции.
     */
    public static function fromBytes(string $bytes): TransactionInterface
    {
        $trx = new Transaction();

        return $trx->setBytes($bytes);
    }

    /**
     * @param string $base64 Транзакция в формате Base64.
     * @return TransactionInterface
     * @throws Exception Ошибка в случае некорректной строки Base64 или длины транзакции.
     */
    public static function fromBase64(string $base64): TransactionInterface
    {
        $bytes = base64_decode($base64, true);

        if ($bytes === false) {
            throw new Exception('could not decode base64');
        }

        $trx = new Transaction();

        return $trx->setBytes($bytes);
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
     * @return integer
     */
    public function getFeePercent(): int
    {
        // Fee offset - 39.
        return ((ord($this->bytes[39]) << 8) + ord($this->bytes[40]));
    }

    /**
     * @param integer $percent Комиссия в сотых долях процента с шагом в 0.01%.
     * Валидные значения от 0 до 2000 (соотвественно от 0% до 20%).
     * @return TransactionInterface
     * @throws Exception Ошибка в случае некорректного процента.
     */
    public function setFeePercent(int $percent): TransactionInterface
    {
        $this->validateInt($percent, 0, 2000);

        // Fee offset - 39.
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
     * @throws Exception
     */
    public function getName(): string
    {
        if (ord($this->bytes[41]) > 35) {
            throw new Exception('incorrect length');
        }

        return substr($this->bytes, 42, ord($this->bytes[41]));
    }

    /**
     * @param string $name Название стуктуры.
     * @return TransactionInterface
     * @throws Exception Ошибка в случае некорректной длины.
     */
    public function setName(string $name): TransactionInterface
    {
        if (strlen($name) > 35) {
            throw new Exception('name too long');
        }

        $this->bytes[41] = chr(strlen($name));
        $this->bytes = substr_replace($this->bytes, str_repeat("\x0", 35), 42, 35); // wipe
        $this->bytes = substr_replace($this->bytes, $name, 42, strlen($name));

        return $this;
    }

    /**
     * @return integer
     */
    public function getNonce(): int
    {
        // Nonce offset - 77.
        $nonce = (ord($this->bytes[77]) << 56);
        $nonce += (ord($this->bytes[78]) << 48);
        $nonce += (ord($this->bytes[79]) << 40);
        $nonce += (ord($this->bytes[80]) << 32);
        $nonce += (ord($this->bytes[81]) << 24);
        $nonce += (ord($this->bytes[82]) << 16);
        $nonce += (ord($this->bytes[83]) << 8);
        $nonce += (ord($this->bytes[84]));

        return $nonce;
    }

    /**
     * @param integer $nonce Nonce.
     * @return TransactionInterface
     */
    public function setNonce(int $nonce): TransactionInterface
    {
        // Nonce offset - 77.
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
     * @throws Exception Ошибка в случе если префикс не проходит валидацию.
     */
    public function getPrefix(): string
    {
        return $this->versionToPrefix((ord($this->bytes[35]) << 8) + ord($this->bytes[36]));
    }

    /**
     * @param string $prefix Префикс. Три символа латиницы в нижнем регистре.
     * @return TransactionInterface
     * @throws Exception Ошибка в случае если префикс не проходит валидацию.
     */
    public function setPrefix(string $prefix): TransactionInterface
    {
        $version = $this->prefixToVersion($prefix);
        $this->bytes[35] = chr($version >> 8 & 0xff);
        $this->bytes[36] = chr($version & 0xff);

        return $this;
    }

    /**
     * @return integer
     */
    public function getProfitPercent(): int
    {
        // Profit offset - 37.
        return ((ord($this->bytes[37]) << 8) + ord($this->bytes[38]));
    }

    /**
     * @param integer $percent Профит в сотых долях процента с шагом в 0.01%.
     * Валидные значения от 100 до 500 (соотвественно от 1% до 5%).
     * @return TransactionInterface
     * @throws Exception Ошибка в случае некорректного процента.
     */
    public function setProfitPercent(int $percent): TransactionInterface
    {
        $this->validateInt($percent, 100, 500);

        // Profit offset - 37.
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
        $adr = new Address();

        return $adr->setBytes(substr($this->bytes, 35, 34));
    }

    /**
     * @param AddressInterface $address
     * @return TransactionInterface
     */
    public function setRecipient(AddressInterface $address): TransactionInterface
    {
        $this->bytes = substr_replace($this->bytes, $address->toBytes(), 35, 34);

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
        $this->bytes = substr_replace($this->bytes, $address->toBytes(), 1, 34);

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
     */
    public function getValue(): int
    {
        // Value offset - 69.
        $val = (ord($this->bytes[69]) << 56);
        $val += (ord($this->bytes[70]) << 48);
        $val += (ord($this->bytes[71]) << 40);
        $val += (ord($this->bytes[72]) << 32);
        $val += (ord($this->bytes[73]) << 24);
        $val += (ord($this->bytes[74]) << 16);
        $val += (ord($this->bytes[75]) << 8);
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
        $this->validateInt($value, 1);

        // Value offset - 69.
        $this->bytes[69] = chr(($value >> 56) & 0xff);
        $this->bytes[70] = chr(($value >> 48) & 0xff);
        $this->bytes[71] = chr(($value >> 40) & 0xff);
        $this->bytes[72] = chr(($value >> 32) & 0xff);
        $this->bytes[73] = chr(($value >> 24) & 0xff);
        $this->bytes[74] = chr(($value >> 16) & 0xff);
        $this->bytes[75] = chr(($value >> 8) & 0xff);
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
     * @return integer
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
     * @param integer $powBits
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
}
