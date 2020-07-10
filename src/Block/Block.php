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
use UmiTop\UmiCore\Key\SecretKeyInterface;
use UmiTop\UmiCore\Transaction\Transaction;
use UmiTop\UmiCore\Transaction\TransactionInterface;

/**
 * Class Block
 * @package UmiTop\UmiCore\Block
 * @implements Iterator<int, TransactionInterface>
 */
class Block extends BlockHeader implements BlockInterface, Iterator
{
    use BlockIteratorTrait;

    /** @var array<int, string> */
    private $trxs = [];

    /**
     * @param string $bytes
     * @return BlockInterface
     * @throws Exception
     * @override
     */
    public static function fromBytes(string $bytes)
    {
        $blk = new Block();

        return $blk->setBytes($bytes);
    }

    /**
     * @param string $bytes
     * @return BlockInterface
     * @throws Exception
     * @override
     */
    public function setBytes(string $bytes)
    {
        if (strlen($bytes) < BlockHeader::LENGTH) {
            throw new Exception('bytes size should be at least 167 bytes');
        }

        parent::setBytes(substr($bytes, 0, BlockHeader::LENGTH));

        $blockLen = BlockHeader::LENGTH + (Transaction::LENGTH * $this->getTransactionCount());

        if (strlen($bytes) !== $blockLen) {
            throw new Exception('incorrect length');
        }

        $this->trxs = str_split(substr($bytes, BlockHeader::LENGTH), Transaction::LENGTH);

        return $this;
    }

    /**
     * @return BlockHeaderInterface
     */
    public function getHeader(): BlockHeaderInterface
    {
        $hdr = new BlockHeader();

        return $hdr->setBytes(parent::toBytes());
    }

    /**
     * @return string
     */
    public function calculateMerkleRoot(): string
    {
        $root = [str_repeat("\x0", 32)];

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

        $trx = new Transaction();

        return $trx->setBytes($this->trxs[$index]);
    }

    /**
     * @param SecretKeyInterface $secretKey
     * @return BlockInterface
     * @throws Exception
     */
    public function sign(SecretKeyInterface $secretKey): BlockInterface
    {
        $this->setMerkleRootHash($this->calculateMerkleRoot());
        $this->setPublicKey($secretKey->getPublicKey());
        $this->setSignature($secretKey->sign(substr(parent::toBytes(), 0, 103)));

        return $this;
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
}
