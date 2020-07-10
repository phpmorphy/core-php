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
 * Class Ed25519
 * Implementation derived from TweetNaCl version 20140427.
 * @see http://tweetnacl.cr.yp.to/
 * @package UmiTop\UmiCore\Util\Ed25519
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Ed25519 extends AbstractEd25519
{
    /**
     * @param string $seed
     * @return string
     */
    public function secretKeyFromSeed(string $seed): string
    {
        $p = array_fill(0, 4, array_fill(0, 16, 0));

        $d = hash('sha512', $seed, true);
        $d[0] = chr(ord($d[0]) & 248);   // d[0] &= 248;
        $d[31] = chr(ord($d[31]) & 127); // d[31] &= 127;
        $d[31] = chr(ord($d[31]) | 64);  // d[31] |= 64;

        $pub = str_repeat("\x0", 32);

        $this->scalarbase($p, $d);
        $this->pack($pub, $p);

        return $seed . $pub;
    }

    /**
     * @param string $message
     * @param string $secretKey
     * @return string
     */
    public function sign(string $message, string $secretKey): string
    {
        // хэшируем приватный ключик (32байта)
        $d = hash('sha512', substr($secretKey, 0, 32), true);
        $d[0] = chr(ord($d[0]) & 248);   // d[0] &= 248
        $d[31] = chr(ord($d[31]) & 127); // d[31] &= 127
        $d[31] = chr(ord($d[31]) | 64);  // d[31] |= 64

        $sm = str_repeat("\x0", 32) . substr($d, 32, 32) . $message;

        $r = hash('sha512', substr($sm, 32), true);
        $this->reduce($r);

        $p = array_fill(0, 4, array_fill(0, 16, 0));
        $this->scalarbase($p, $r);
        $this->pack($sm, $p);

        $sm = substr_replace($sm, substr($secretKey, 32, 32), 32, 32);

        $h = hash('sha512', $sm, true);
        $this->reduce($h);

        $x = array_fill(0, 64, 0);
        for ($i = 0; $i < 32; $i++) {
            $x[$i] = ord($r[$i]);
        }
        for ($i = 0; $i < 32; $i++) {
            for ($j = 0; $j < 32; $j++) {
                $x[$i + $j] += ord($h[$i]) * ord($d[$j]);
            }
        }

        $sm2 = substr($sm, 32);
        $this->modL($sm2, $x);

        return substr($sm, 0, 32) . substr($sm2, 0, 32);
    }

    /**
     * @param string $signature
     * @param string $message
     * @param string $publicKey
     * @return bool
     */
    public function verify(string $signature, string $message, string $publicKey): bool
    {
        $q = array_fill(0, 4, array_fill(0, 16, 0));
        if (!$this->unpackneg($q, $publicKey)) {
            return false; // @codeCoverageIgnore
        }

        $sm = $signature . $message;
        $m = substr_replace($sm, substr($publicKey, 0, 32), 32, 32);

        $h = hash('sha512', $m, true);
        $this->reduce($h);

        $p = array_fill(0, 4, array_fill(0, 16, 0));
        $this->scalarmult($p, $q, $h);
        $this->scalarbase($q, substr($sm, 32));
        $this->add($p, $q);

        $t = str_repeat("\x0", 32);
        $this->pack($t, $p);

        return $this->cryptoVerify32($sm, $t);
    }

    /**
     * @param array<int, array<int, int>> $p
     * @param array<int, array<int, int>> $q
     */
    private function add(array &$p, array $q): void
    {
        $a = $b = $c = $d = $t = $e = $f = $g = $h = array_fill(0, 16, 0);

        $this->fnZ($a, $p[1], $p[0]);
        $this->fnZ($t, $q[1], $q[0]);
        $this->fnM($a, $a, $t);
        $this->fnA($b, $p[0], $p[1]);
        $this->fnA($t, $q[0], $q[1]);
        $this->fnM($b, $b, $t);
        $this->fnM($c, $p[3], $q[3]);
        $this->fnM($c, $c, $this->D2);
        $this->fnM($d, $p[2], $q[2]);
        $this->fnA($d, $d, $d);
        $this->fnZ($e, $b, $a);
        $this->fnZ($f, $d, $c);
        $this->fnA($g, $d, $c);
        $this->fnA($h, $b, $a);

        $this->fnM($p[0], $e, $f);
        $this->fnM($p[1], $h, $g);
        $this->fnM($p[2], $g, $f);
        $this->fnM($p[3], $e, $h);
    }

    /**
     * @param string $r
     * @param array<int, int> $x
     */
    private function modL(string &$r, array &$x): void
    {
        for ($i = 63; $i >= 32; --$i) {
            $carry = 0;
            for ($j = $i - 32; $j < $i - 12; ++$j) {
                $x[$j] += $carry - 16 * $x[$i] * $this->L[$j - ($i - 32)];
                $carry = ($x[$j] + 128) >> 8;
                $x[$j] -= $carry << 8;
            }
            $x[$j] += $carry;
            $x[$i] = 0;
        }

        $carry = 0;
        for ($j = 0; $j < 32; $j++) {
            $x[$j] += $carry - ($x[31] >> 4) * $this->L[$j];
            $carry = $x[$j] >> 8;
            $x[$j] &= 255;
        }

        for ($j = 0; $j < 32; $j++) {
            $x[$j] -= $carry * $this->L[$j];
        }

        for ($i = 0; $i < 32; $i++) {
            $x[$i + 1] += $x[$i] >> 8;
            $r[$i] = chr($x[$i] & 255);
        }
    }

    /**
     * @param string $r
     * @param array<int, array<int, int>> $p
     */
    private function pack(string &$r, array $p): void
    {
        $tx = $ty = $zi = array_fill(0, 16, 0);

        $this->inv25519($zi, $p[2]);
        $this->fnM($tx, $p[0], $zi);
        $this->fnM($ty, $p[1], $zi);
        $this->pack25519($r, $ty);

        $r[31] = chr(ord($r[31]) ^ $this->par25519($tx) << 7); // r[31] ^= par25519(tx) << 7;
    }

    /**
     * @param string $r
     */
    private function reduce(string &$r): void
    {
        $x = array_fill(0, 64, 0);

        for ($i = 0; $i < 64; $i++) {
            $x[$i] = ord($r[$i]);
        }

        for ($i = 0; $i < 64; $i++) {
            $r[$i] = chr(0);
        }

        $this->modL($r, $x);
    }

    /**
     * @param array<int, array<int, int>> $p
     * @param string $s
     */
    private function scalarbase(array &$p, string $s): void
    {
        $q = array_fill(0, 4, array_fill(0, 16, 0));
        $this->set25519($q[0], $this->X);
        $this->set25519($q[1], $this->Y);
        $this->set25519($q[2], $this->gf1);
        $this->fnM($q[3], $this->X, $this->Y);
        $this->scalarmult($p, $q, $s);
    }

    /**
     * @param array<int, array<int, int>> $p
     * @param array<int, array<int, int>> $q
     * @param string $s
     */
    private function scalarmult(array &$p, array &$q, string $s): void
    {
        $this->set25519($p[0], $this->gf0);
        $this->set25519($p[1], $this->gf1);
        $this->set25519($p[2], $this->gf1);
        $this->set25519($p[3], $this->gf0);

        for ($i = 255; $i >= 0; --$i) {
            $b = (ord($s[(int)($i / 8)]) >> ($i & 7)) & 1;
            $this->cswap($p, $q, $b);
            $this->add($q, $p);
            $this->add($p, $p);
            $this->cswap($p, $q, $b);
        }
    }

    /**
     * @param array<int, array<int, int>> $r
     * @param string $p
     * @return bool
     */
    private function unpackneg(array &$r, string $p): bool
    {
        $t = $chk = $num = $den = $den2 = $den4 = $den6 = array_fill(0, 16, 0);

        $this->set25519($r[2], $this->gf1);
        $this->unpack25519($r[1], $p);

        $this->fnM($num, $r[1], $r[1]);
        $this->fnM($den, $num, $this->D);
        $this->fnZ($num, $num, $r[2]);
        $this->fnA($den, $r[2], $den);

        $this->fnM($den2, $den, $den);
        $this->fnM($den4, $den2, $den2);
        $this->fnM($den6, $den4, $den2);
        $this->fnM($t, $den6, $num);
        $this->fnM($t, $t, $den);

        $this->pow2523($t, $t);
        $this->fnM($t, $t, $num);
        $this->fnM($t, $t, $den);
        $this->fnM($t, $t, $den);
        $this->fnM($r[0], $t, $den);

        $this->fnM($chk, $r[0], $r[0]);
        $this->fnM($chk, $chk, $den);

        if (!$this->neq25519($chk, $num)) {
            $this->fnM($r[0], $r[0], $this->I);
        }

        $this->fnM($chk, $r[0], $r[0]);
        $this->fnM($chk, $chk, $den);

        if (!$this->neq25519($chk, $num)) {
            return false; // @codeCoverageIgnore
        }

        if ($this->par25519($r[0]) === (ord($p[31]) >> 7)) {
            $this->fnZ($r[0], $this->gf0, $r[0]);
        }

        $this->fnM($r[3], $r[0], $r[1]);

        return true;
    }
}
