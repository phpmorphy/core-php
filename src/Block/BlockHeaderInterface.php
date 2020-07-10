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

use UmiTop\UmiCore\Key\PublicKeyInterface;

/**
 * Interface BlockHeaderInterface
 * @package UmiTop\UmiCore\Block
 */
interface BlockHeaderInterface
{
    public function getHash(): string;

    public function getMerkleRootHash(): string;

    public function setMerkleRootHash(string $hash): BlockHeaderInterface;

    public function getPreviousBlockHash(): string;

    public function setPreviousBlockHash(string $hash): BlockHeaderInterface;

    public function getPublicKey(): PublicKeyInterface;

    public function setPublicKey(PublicKeyInterface $publicKey): BlockHeaderInterface;

    public function getSignature(): string;

    public function setSignature(string $signature): BlockHeaderInterface;

    public function getTimestamp(): int;

    public function setTimestamp(int $epoch): BlockHeaderInterface;

    public function getTransactionCount(): int;

    public function setTransactionCount(int $count): BlockHeaderInterface;

    public function getVersion(): int;

    public function setVersion(int $version): BlockHeaderInterface;

    public function toBase64(): string;

    public function toBytes(): string;

    public function verify(): bool;
}
