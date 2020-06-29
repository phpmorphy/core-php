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
use Iterator;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Transaction\TransactionInterface;

/**
 * Class Block
 * @implements Iterator<int, TransactionInterface>
 */
class Block extends BlockHeader implements BlockInterface, Iterator
{
    /** @var array<int, string> */
    private $trxs;

    /** @var int */
    private $position = 0;

    /**
     * Block constructor.
     * @param string|null $bytes Трназакция в бинарном виде. Опциональный параметр.
     * @throws Exception Ошибка в случае некорректной длины.
     */
    public function __construct(string $bytes = null)
    {
        if ($bytes === null) {
            $bytes = str_repeat("\x0", BlockHeader::LENGTH);
        }

        if (strlen($bytes) < BlockHeader::LENGTH) {
            throw new Exception('bytes size should be at least 167 bytes');
        }

        parent::__construct(substr($bytes, 0, BlockHeader::LENGTH));

        $blockLen = BlockHeader::LENGTH + (Transaction::LENGTH * $this->getTransactionCount());

        if (strlen($bytes) !== $blockLen) {
            throw new Exception('incorrect length');
        }

        $this->trxs = str_split(substr($bytes, BlockHeader::LENGTH), Transaction::LENGTH);
    }

    /**
     * @return BlockHeaderInterface
     */
    public function getHeader(): BlockHeaderInterface
    {
        return new BlockHeader(parent::toBytes());
    }

    /**
     * @return string
     * @throws Exception
     */
    public function calculateMerkleRoot(): string
    {
        $root = [];

        foreach ($this as $idx => $trx) {
            $root[$idx] = $trx->getHash();
        }

        $lvl = count($root);
        while ($lvl > 1) {
            $lst = (int)($lvl - 1);
            $lvl = (int)ceil($lvl / 2);
            for ($i = 0; $i < $lvl; $i++) {
                $idx1 = $i * 2;
                $idx2 = min(($idx1 + 1), $lst);
                $root[$i] = hash('sha256', ($root[$idx1] . $root[$idx2]), true);
            }
        }

        return $root[0];
    }

    /**
     * @param TransactionInterface $transaction
     * @return BlockInterface
     */
    public function appendTransaction(TransactionInterface $transaction): BlockInterface
    {
        $txCount = $this->getTransactionCount();
        $this->trxs[$txCount] = $transaction->toBytes();
        $this->setTransactionCount(++$txCount);

        return $this;
    }

    /**
     * @param integer $index
     * @return TransactionInterface
     * @throws Exception
     */
    public function getTransaction(int $index): TransactionInterface
    {
        if ($index < 0 || $index >= $this->getTransactionCount()) {
            throw new Exception('incorrect index');
        }

        return new Transaction($this->trxs[$index]);
    }

    /**
     * @return bool
     */
    public function verify(): bool
    {
        return parent::verify();
    }

    /**
     * @return string
     */
    public function toBytes(): string
    {
        return parent::toBytes() . join('', $this->trxs);
    }

    /**
     * @return TransactionInterface
     * @throws Exception
     */
    public function current(): TransactionInterface
    {
        return $this->getTransaction($this->position);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return array_key_exists($this->position, $this->trxs);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
