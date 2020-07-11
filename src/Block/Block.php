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
class Block implements BlockInterface, Iterator
{
    use BlockIteratorTrait;

    /** @var BlockHeaderInterface */
    private $header;

    /** @var string[] */
    private $trxs;

    /**
     * Block constructor.
     */
    public function __construct()
    {
        $this->header = new BlockHeader();
        $this->trxs = [];
    }

    /**
     * @param string $base64
     * @return BlockInterface
     * @throws Exception
     */
    public static function fromBase64(string $base64): BlockInterface
    {
        $blk = new Block();

        return $blk->setBase64($base64);
    }

    /**
     * @param string $bytes
     * @return BlockInterface
     * @throws Exception
     */
    public static function fromBytes(string $bytes): BlockInterface
    {
        $blk = new Block();

        return $blk->setBytes($bytes);
    }

    /**
     * @param TransactionInterface $transaction
     * @return BlockInterface
     * @throws Exception
     */
    public function appendTransaction(TransactionInterface $transaction): BlockInterface
    {
        $txCount = $this->header->getTransactionCount();

        if ($txCount === 65535) {
            throw new Exception('too many transactions');
        }

        $this->trxs[$txCount] = $transaction->getBytes();
        $this->header->setTransactionCount(++$txCount);

        return $this;
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
     * @return string
     */
    public function getBase64(): string
    {
        return base64_encode($this->getBytes());
    }

    /**
     * @param string $base64
     * @return BlockInterface
     * @throws Exception
     */
    public function setBase64(string $base64): BlockInterface
    {
        $bytes = base64_decode($base64, true);

        if ($bytes === false) {
            throw new Exception('could not decode base64');
        }

        return $this->setBytes($bytes);
    }

    /**
     * @return string
     */
    public function getBytes(): string
    {
        return $this->header->getBytes() . join('', $this->trxs);
    }

    /**
     * @param string $bytes
     * @return BlockInterface
     * @throws Exception
     * @override
     */
    public function setBytes(string $bytes): BlockInterface
    {
        if (strlen($bytes) < BlockHeader::LENGTH) {
            throw new Exception('bytes size should be at least 167 bytes');
        }

        $this->header->setBytes(substr($bytes, 0, BlockHeader::LENGTH));

        $blockLen = BlockHeader::LENGTH + (Transaction::LENGTH * $this->header->getTransactionCount());

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
        return $this->header;
    }

    /**
     * @param BlockHeaderInterface $header
     * @return BlockInterface
     */
    public function setHeader(BlockHeaderInterface $header): BlockInterface
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @param int $index
     * @return TransactionInterface
     * @throws Exception
     */
    public function getTransaction(int $index): TransactionInterface
    {
        if ($index < 0 || $index >= $this->header->getTransactionCount()) {
            throw new Exception('incorrect index');
        }

        $trx = new Transaction();

        return $trx->setBytes($this->trxs[$index]);
    }

    /**
     * @param SecretKeyInterface $secretKey
     * @return $this
     * @throws Exception
     */
    public function sign(SecretKeyInterface $secretKey): BlockInterface
    {
        $this->header->setMerkleRootHash($this->calculateMerkleRoot());
        $this->header->sign($secretKey);

        return $this;
    }

    /**
     * @return bool
     */
    public function verify(): bool
    {
        return $this->header->verify();
    }
}
