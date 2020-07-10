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
    /** @var int */
    public const GENESIS = 0;

    /** @var int */
    public const BASIC = 1;

    /** @var int */
    public const CREATE_STRUCTURE = 2;

    /** @var int */
    public const UPDATE_STRUCTURE = 3;

    /** @var int */
    public const UPDATE_PROFIT_ADDRESS = 4;

    /** @var int */
    public const UPDATE_FEE_ADDRESS = 5;

    /** @var int */
    public const CREATE_TRANSIT_ADDRESS = 6;

    /** @var int */
    public const DELETE_TRANSIT_ADDRESS = 7;

    public function getFeePercent(): int;

    public function setFeePercent(int $percent): TransactionInterface;

    public function getHash(): string;

    public function getName(): string;

    public function setName(string $name): TransactionInterface;

    public function getNonce(): int;

    public function setNonce(int $value): TransactionInterface;

    public function getPowBits(): int;

    public function getPrefix(): string;

    public function setPrefix(string $prefix): TransactionInterface;

    public function getProfitPercent(): int;

    public function setProfitPercent(int $percent): TransactionInterface;

    public function getRecipient(): AddressInterface;

    public function setRecipient(AddressInterface $address): TransactionInterface;

    public function getSender(): AddressInterface;

    public function setSender(AddressInterface $address): TransactionInterface;

    public function getSignature(): string;

    public function setSignature(string $signature): TransactionInterface;

    public function getValue(): int;

    public function setValue(int $value): TransactionInterface;

    public function getVersion(): int;

    public function setVersion(int $version): TransactionInterface;

    public function sign(SecretKeyInterface $secretKey, int $powBits = null): TransactionInterface;

    public function toBytes(): string;

    public function toBase64(): string;

    public function verify(): bool;
}
