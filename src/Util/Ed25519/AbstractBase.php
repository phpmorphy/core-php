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

namespace UmiTop\UmiCore\Util\Ed25519;

/**
 * Class AbstractBase
 * Implementation derived from TweetNaCl version 20140427.
 * @see http://tweetnacl.cr.yp.to/
 * @package UmiTop\UmiCore\Util\Ed25519
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
abstract class AbstractBase
{
    /** @var int */
    public const PUBLIC_KEY_BYTES = 32;

    /** @var int */
    public const SECRET_KEY_BYTES = 64;

    /** @var int */
    public const SEED_BYTES = 32;

    /** @var array<int, int> */
    protected $D2;

    /** @var array<int, int> */
    protected $D;

    /** @var array<int, int> */
    protected $gf0;

    /** @var array<int, int> */
    protected $gf1;

    /** @var array<int, int> */
    protected $I;

    /** @var array<int, int> */
    protected $L;

    /** @var array<int, int> */
    protected $X;

    /** @var array<int, int> */
    protected $Y;

    /**
     * Ed25519 constructor.
     */
    public function __construct()
    {
        $this->D2 = [
            0xf159, 0x26b2, 0x9b94, 0xebd6, 0xb156, 0x8283, 0x149a, 0x00e0,
            0xd130, 0xeef3, 0x80f2, 0x198e, 0xfce7, 0x56df, 0xd9dc, 0x2406
        ];
        $this->D = [
            0x78a3, 0x1359, 0x4dca, 0x75eb, 0xd8ab, 0x4141, 0x0a4d, 0x0070,
            0xe898, 0x7779, 0x4079, 0x8cc7, 0xfe73, 0x2b6f, 0x6cee, 0x5203
        ];
        $this->gf0 = array_fill(0, 16, 0);
        $this->gf1 = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $this->I = [
            0xa0b0, 0x4a0e, 0x1b27, 0xc4ee, 0xe478, 0xad2f, 0x1806, 0x2f43,
            0xd7a7, 0x3dfb, 0x0099, 0x2b4d, 0xdf0b, 0x4fc1, 0x2480, 0x2b83
        ];
        $this->L = [
            0xed, 0xd3, 0xf5, 0x5c, 0x1a, 0x63, 0x12, 0x58, 0xd6, 0x9c, 0xf7, 0xa2,
            0xde, 0xf9, 0xde, 0x14, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0x10
        ];
        $this->X = [
            0xd51a, 0x8f25, 0x2d60, 0xc956, 0xa7b2, 0x9525, 0xc760, 0x692c,
            0xdc5c, 0xfdd6, 0xe231, 0xc0a4, 0x53fe, 0xcd6e, 0x36d3, 0x2169
        ];
        $this->Y = [
            0x6658, 0x6666, 0x6666, 0x6666, 0x6666, 0x6666, 0x6666, 0x6666,
            0x6666, 0x6666, 0x6666, 0x6666, 0x6666, 0x6666, 0x6666, 0x6666
        ];
    }

    /**
     * @param array<int, int> $o
     */
    protected function car25519(array &$o): void
    {
        for ($i = 0; $i < 16; $i++) {
            $o[$i] += (1 << 16);
            $c = $o[$i] >> 16;
            $o[($i + 1) * (int)($i < 15)] += $c - 1 + 37 * ($c - 1) * (int)($i === 15);
            $o[$i] -= $c << 16;
        }
    }

    /**
     * @param string $x
     * @param string $y
     * @return bool
     */
    protected function cryptoVerify32(string $x, string $y): bool
    {
        $d = 0;
        for ($i = 0; $i < 32; $i++) {
            $d |= ord($x[$i]) ^ ord($y[$i]);
        }

        return (1 & (($d - 1) >> 8)) === 1;
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $a
     * @param array<int, int> $b
     */
    protected function fnA(array &$o, array $a, array $b): void
    {
        for ($i = 0; $i < 16; $i++) {
            $o[$i] = $a[$i] + $b[$i];
        }
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $a
     * @param array<int, int> $b
     */
    protected function fnM(array &$o, array $a, array $b): void
    {
        $t = array_fill(0, 31, 0);

        for ($i = 0; $i < 16; $i++) {
            for ($j = 0; $j < 16; $j++) {
                $t[$i + $j] += $a[$i] * $b[$j];
            }
        }
        for ($i = 0; $i < 15; $i++) {
            $t[$i] += 38 * $t[$i + 16];
        }
        for ($i = 0; $i < 16; $i++) {
            $o[$i] = $t[$i];
        }

        $this->car25519($o);
        $this->car25519($o);
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $a
     * @param array<int, int> $b
     */
    protected function fnZ(array &$o, array $a, array $b): void
    {
        for ($i = 0; $i < 16; $i++) {
            $o[$i] = $a[$i] - $b[$i];
        }
    }
}
