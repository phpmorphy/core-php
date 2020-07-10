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
 * Class AbstractEd25519
 * Implementation derived from TweetNaCl version 20140427.
 * @see http://tweetnacl.cr.yp.to/
 * @package UmiTop\UmiCore\Util\Ed25519
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
abstract class AbstractEd25519 extends AbstractBase
{
    /**
     * @param array<int, array<int, int>> $p
     * @param array<int, array<int, int>> $q
     * @param int $b
     */
    protected function cswap(array &$p, array &$q, int $b): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->sel25519($p[$i], $q[$i], $b);
        }
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $i
     */
    protected function inv25519(array &$o, array $i): void
    {
        $c = $i;
        for ($a = 253; $a >= 0; $a--) {
            $this->fnM($c, $c, $c);
            if ($a != 2 && $a != 4) {
                $this->fnM($c, $c, $i);
            }
        }
        $o = $c;
    }

    /**
     * @param array<int, int> $a
     * @param array<int, int> $b
     * @return bool
     */
    protected function neq25519(array $a, array $b): bool
    {
        $c = $d = str_repeat("\x0", 32);

        $this->pack25519($c, $a);
        $this->pack25519($d, $b);

        return $this->cryptoVerify32($c, $d);
    }

    /**
     * @param string $o
     * @param array<int, int> $n
     */
    protected function pack25519(string &$o, array $n): void
    {
        $m = array_fill(0, 16, 0);
        $t = $n;

        $this->car25519($t);
        $this->car25519($t);
        $this->car25519($t);

        for ($j = 0; $j < 2; $j++) {
            $m[0] = $t[0] - 0xffed;
            for ($i = 1; $i < 15; $i++) {
                $m[$i] = $t[$i] - 0xffff - (($m[$i - 1] >> 16) & 1);
                $m[$i - 1] &= 0xffff;
            }
            $m[15] = $t[15] - 0x7fff - (($m[14] >> 16) & 1);
            $b = ($m[15] >> 16) & 1;
            $m[14] &= 0xffff;
            $this->sel25519($t, $m, 1 - $b);
        }

        for ($i = 0; $i < 16; $i++) {
            $o[2 * $i] = chr($t[$i] & 0xff);
            $o[2 * $i + 1] = chr($t[$i] >> 8);
        }
    }

    /**
     * @param array<int, int> $a
     * @return int
     */
    protected function par25519(array $a): int
    {
        $d = str_repeat("\x0", 32);
        $this->pack25519($d, $a);

        return ord($d[0]) & 1;
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $i
     */
    protected function pow2523(array &$o, array $i): void
    {
        $c = $i;

        for ($a = 250; $a >= 0; $a--) {
            $this->fnM($c, $c, $c);
            if ($a != 1) {
                $this->fnM($c, $c, $i);
            }
        }

        $o = $c;
    }

    /**
     * @param array<int, int> $r
     * @param array<int, int> $a
     */
    protected function set25519(array &$r, array $a): void
    {
        for ($i = 0; $i < 16; $i++) {
            $r[$i] = $a[$i];
        }
    }

    /**
     * @param array<int, int> $o
     * @param string $n
     */
    protected function unpack25519(array &$o, string $n): void
    {
        for ($i = 0; $i < 16; $i++) {
            $o[$i] = ord($n[2 * $i]) + (ord($n[2 * $i + 1]) << 8);
        }
        $o[15] &= 0x7fff;
    }

    /**
     * @param array<int, int> $p
     * @param array<int, int> $q
     * @param int $b
     */
    private function sel25519(array &$p, array &$q, int $b): void
    {
        $c = ~($b - 1);
        for ($i = 0; $i < 16; $i++) {
            $ttt = $c & ($p[$i] ^ $q[$i]);
            $p[$i] ^= $ttt;
            $q[$i] ^= $ttt;
        }
    }
}
