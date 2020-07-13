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
 * Класс для работы с транзакциями.
 * @package UmiTop\UmiCore\Transaction
 */
class Transaction implements TransactionInterface
{
    use ValidatorTrait;
    use ConverterTrait;

    /** @var int Длина транзакции в байтах. */
    public const LENGTH = 150;

    /** @var string Транзакция в бинарном виде. */
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
     * Статический метод, создает объект из массива байтов.
     * @param string $bytes Транзакция в бинарном виде, длина 150 байт.
     * @return TransactionInterface
     * @throws Exception
     */
    public static function fromBytes(string $bytes): TransactionInterface
    {
        $trx = new Transaction();

        return $trx->setBytes($bytes);
    }

    /**
     * Транзакция в бинарном виде, длина 150 байт.
     * @return string
     */
    public function getBytes(): string
    {
        return $this->bytes;
    }

    /**
     * Устанавливает транзакцию из бинарной строки и возвращает $this.
     * @param string $bytes Транзакция в бинарном виде, длина 150 байт.
     * @return TransactionInterface
     * @throws Exception
     */
    public function setBytes(string $bytes): TransactionInterface
    {
        if (strlen($bytes) !== self::LENGTH) {
            throw new Exception('length must be 150 bytes');
        }
        $this->bytes = $bytes;

        return $this;
    }

    /**
     * Комиссия в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 0 до 2000 (соответственно от 0% до 20%).
     * Доступно только для CreateStructure и UpdateStructure.
     * @return int
     * @throws Exception
     */
    public function getFeePercent(): int
    {
        $this->checkVersion([2, 3]);

        return $this->bytesToUint16(substr($this->bytes, 39, 2));
    }

    /**
     * Устанавливает размер комиссии и возвращает this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param int $percent Комиссия в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 0 до 2000 (соответственно от 0% до 20%).
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
     * Хэш (txid) транзакции в бинарном виде.
     * @return string
     */
    public function getHash(): string
    {
        return hash('sha256', $this->bytes, true);
    }

    /**
     * Название структуры в кодировке UTF-8.
     * Доступно только для CreateStructure и UpdateStructure.
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
     * Устанавливает название структуры и возвращает this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param string $name Название структуры в кодировке UTF-8.
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
     * Nonce, целое число в промежутке от 0 до 18446744073709551615.
     * Генерируется автоматически при вызове sign().
     * @return int
     */
    public function getNonce(): int
    {
        return $this->bytesToInt64(substr($this->bytes, 77, 8));
    }

    /**
     * Устанавливает nonce и возвращает this.
     * @param int $nonce Целое число в промежутке от 0 до 18446744073709551615.
     * @return TransactionInterface
     */
    public function setNonce(int $nonce): TransactionInterface
    {
        $this->bytes = substr_replace($this->bytes, $this->int64ToBytes($nonce), 77, 8);

        return $this;
    }

    /**
     * Префикс адресов, принадлежащих структуре.
     * Доступно только для CreateStructure и UpdateStructure.
     * @return string
     * @throws Exception
     */
    public function getPrefix(): string
    {
        $this->checkVersion([2, 3]);

        return $this->versionToPrefix($this->bytesToUint16(substr($this->bytes, 35, 2)));
    }

    /**
     * Устанавливает префикс и возвращает $this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param string $prefix Три символа латиницы в нижнем регистре.
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
     * Профита в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 100 до 500 (соответственно от 1% до 5%).
     * Доступно только для CreateStructure и UpdateStructure.
     * @return int
     * @throws Exception
     */
    public function getProfitPercent(): int
    {
        $this->checkVersion([2, 3]);

        return $this->bytesToUint16(substr($this->bytes, 37, 2));
    }

    /**
     * Устанавливает процент профита и возвращает $this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param int $percent Профит в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 100 до 500 (соответственно от 1% до 5%).
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
     * Получатель.
     * Недоступно для транзакций CreateStructure и UpdateStructure.
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
     * Устанавливает получателя и возвращает $this.
     * Недоступно для транзакций CreateStructure и UpdateStructure.
     * @param AddressInterface $address Адрес получателя.
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
     * Отправитель.
     * Доступно для всех типов транзакций.
     * @return AddressInterface
     * @throws Exception
     */
    public function getSender(): AddressInterface
    {
        $adr = new Address();

        return $adr->setBytes(substr($this->bytes, 1, 34));
    }

    /**
     * Устанавливает отправителя и возвращает $this.
     * @param AddressInterface $address Адрес отправителя.
     * @return TransactionInterface
     */
    public function setSender(AddressInterface $address): TransactionInterface
    {
        $this->bytes = substr_replace($this->bytes, $address->getBytes(), 1, 34);

        return $this;
    }

    /**
     * Цифровая подпись транзакции, длина 64 байта.
     * @return string
     */
    public function getSignature(): string
    {
        return substr($this->bytes, 85, 64);
    }

    /**
     * Устанавливает цифровую подпись и возвращает $this.
     * @param string $signature Подпись, длина 64 байта.
     * @return TransactionInterface
     */
    public function setSignature(string $signature): TransactionInterface
    {
        $this->bytes = substr_replace($this->bytes, $signature, 85, strlen($signature));

        return $this;
    }

    /**
     * Сумма перевода в UMI-центах, цело число в промежутке от 1 до 18446744073709551615.
     * Доступно только для Genesis и Basic транзакций.
     * @return int
     * @throws Exception
     */
    public function getValue(): int
    {
        $this->checkVersion([0, 1]);

        return $this->bytesToInt64(substr($this->bytes, 69, 8));
    }

    /**
     * Устанавливает сумму и возвращает $this.
     * Принимает значения в промежутке от 1 до 18446744073709551615.
     * Доступно только для Genesis и Basic транзакций.
     * @param int $value Целое число от 1 до 18446744073709551615.
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
     * Версия (тип) транзакции.
     * @return int
     */
    public function getVersion(): int
    {
        return ord($this->bytes[0]);
    }

    /**
     * Устанавливает версию и возвращает $this.
     * @param int $version Версия (тип) транзакции.
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
     * Подписать транзакцию приватным ключом.
     * @param SecretKeyInterface $secretKey Приватный ключ.
     * @return TransactionInterface
     * @throws Exception
     */
    public function sign(SecretKeyInterface $secretKey): TransactionInterface
    {
        $this->setNonce((int)(microtime(true) * 100));
        $this->setSignature($secretKey->sign(substr($this->bytes, 0, 85)));

        return $this;
    }

    /**
     * Проверить транзакцию на соответствие формальным правилам.
     * @return bool
     * @throws Exception
     */
    public function verify(): bool
    {
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
            throw new Exception('invalid version');
        }
    }
}
