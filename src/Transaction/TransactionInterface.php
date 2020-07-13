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

use UmiTop\UmiCore\Address\AddressInterface;
use UmiTop\UmiCore\Key\SecretKeyInterface;

/**
 * Interface TransactionInterface
 * @package UmiTop\UmiCore\Transaction
 */
interface TransactionInterface
{
    /** @var int Genesis-транзакция. */
    public const GENESIS = 0;

    /** @var int Стандартная транзакция. Перевод монет из одного кошелька в другой. */
    public const BASIC = 1;

    /** @var int Создание новой структуры. */
    public const CREATE_STRUCTURE = 2;

    /** @var int Обновление настроек существующей структуры. */
    public const UPDATE_STRUCTURE = 3;

    /** @var int Изменение адреса для начисления профита. */
    public const UPDATE_PROFIT_ADDRESS = 4;

    /** @var int Изменение адреса на который переводится комиссия. */
    public const UPDATE_FEE_ADDRESS = 5;

    /** @var int Активация транзитного адреса. */
    public const CREATE_TRANSIT_ADDRESS = 6;

    /** @var int Деактивация транзитного адреса. */
    public const DELETE_TRANSIT_ADDRESS = 7;

    /**
     * Транзакция в бинарном виде, длина 150 байт.
     * @return string
     */
    public function getBytes(): string;

    /**
     * Устанавливает транзакцию из бинарной строки и возвращает $this.
     * @param string $bytes Транзакция в бинарном виде, длина 150 байт.
     * @return TransactionInterface
     */
    public function setBytes(string $bytes): TransactionInterface;

    /**
     * Комиссия в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 0 до 2000 (соответственно от 0% до 20%).
     * Доступно только для CreateStructure и UpdateStructure.
     * @return int
     */
    public function getFeePercent(): int;

    /**
     * Устанавливает размер комиссии и возвращает this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param int $percent Комиссия в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 0 до 2000 (соответственно от 0% до 20%).
     * @return TransactionInterface
     */
    public function setFeePercent(int $percent): TransactionInterface;

    /**
     * Хэш (txid) транзакции в бинарном виде.
     * @return string
     */
    public function getHash(): string;

    /**
     * Название структуры в кодировке UTF-8.
     * Доступно только для CreateStructure и UpdateStructure.
     * @return string
     */
    public function getName(): string;

    /**
     * Устанавливает название структуры и возвращает this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param string $name Название структуры в кодировке UTF-8.
     * @return TransactionInterface
     */
    public function setName(string $name): TransactionInterface;

    /**
     * Nonce, целое число в промежутке от 0 до 18446744073709551615.
     * Генерируется автоматически при вызове sign().
     * @return int
     */
    public function getNonce(): int;

    /**
     * Устанавливает nonce и возвращает this.
     * @param int $value Целое число в промежутке от 0 до 18446744073709551615.
     * @return TransactionInterface
     */
    public function setNonce(int $value): TransactionInterface;

    /**
     * Префикс адресов, принадлежащих структуре.
     * Доступно только для CreateStructure и UpdateStructure.
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Устанавливает префикс и возвращает $this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param string $prefix Три символа латиницы в нижнем регистре.
     * @return TransactionInterface
     */
    public function setPrefix(string $prefix): TransactionInterface;

    /**
     * Профита в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 100 до 500 (соответственно от 1% до 5%).
     * Доступно только для CreateStructure и UpdateStructure.
     * @return int
     */
    public function getProfitPercent(): int;

    /**
     * Устанавливает процент профита и возвращает $this.
     * Доступно только для CreateStructure и UpdateStructure.
     * @param int $percent Профит в сотых долях процента с шагом в 0.01%.
     * Принимает значения от 100 до 500 (соответственно от 1% до 5%).
     * @return TransactionInterface
     */
    public function setProfitPercent(int $percent): TransactionInterface;

    /**
     * Получатель.
     * Недоступно для транзакций CreateStructure и UpdateStructure.
     * @return AddressInterface
     */
    public function getRecipient(): AddressInterface;

    /**
     * Устанавливает получателя и возвращает $this.
     * Недоступно для транзакций CreateStructure и UpdateStructure.
     * @param AddressInterface $address Адрес получателя.
     * @return TransactionInterface
     */
    public function setRecipient(AddressInterface $address): TransactionInterface;

    /**
     * Отправитель.
     * Доступно для всех типов транзакций.
     * @return AddressInterface
     */
    public function getSender(): AddressInterface;

    /**
     * Устанавливает отправителя и возвращает $this.
     * @param AddressInterface $address Адрес отправителя.
     * @return TransactionInterface
     */
    public function setSender(AddressInterface $address): TransactionInterface;

    /**
     * Цифровая подпись транзакции, длина 64 байта.
     * @return string
     */
    public function getSignature(): string;

    /**
     * Устанавливает цифровую подпись и возвращает $this.
     * @param string $signature Подпись, длина 64 байта.
     * @return TransactionInterface
     */
    public function setSignature(string $signature): TransactionInterface;

    /**
     * Сумма перевода в UMI-центах, цело число в промежутке от 1 до 18446744073709551615.
     * Доступно только для Genesis и Basic транзакций.
     * @return int
     */
    public function getValue(): int;

    /**
     * Устанавливает сумму и возвращает $this.
     * Принимает значения в промежутке от 1 до 18446744073709551615.
     * Доступно только для Genesis и Basic транзакций.
     * @param int $value Целое число от 1 до 18446744073709551615.
     * @return TransactionInterface
     */
    public function setValue(int $value): TransactionInterface;

    /**
     * Версия (тип) транзакции.
     * @return int
     */
    public function getVersion(): int;

    /**
     * Устанавливает версию и возвращает $this.
     * @param int $version Версия (тип) транзакции.
     * @return TransactionInterface
     */
    public function setVersion(int $version): TransactionInterface;

    /**
     * Подписать транзакцию приватным ключом.
     * @param SecretKeyInterface $secretKey Приватный ключ.
     * @return TransactionInterface
     */
    public function sign(SecretKeyInterface $secretKey): TransactionInterface;

    /**
     * Проверить транзакцию на соответствие формальным правилам.
     * @return bool
     */
    public function verify(): bool;
}
