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

namespace UmiTop\UmiCore\Util;

use Exception;

/**
 * Class Ed25519
 * Implementation derived from TweetNaCl version 20140427.
 * @see http://tweetnacl.cr.yp.to/
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Ed25519
{
    /** @var int */
    public const PUBLIC_KEY_BYTES = 32;

    /** @var int */
    public const SECRET_KEY_BYTES = 64;

    /** @var int */
    public const SEED_BYTES = 32;

    /** @var int */
    public const SIGNATURE_BYTES = 64;

    /** @var array<int, int> */
    private $D2;

    /** @var array<int, int> */
    private $D;

    /** @var array<int, int> */
    private $gf0;

    /** @var array<int, int> */
    private $gf1;

    /** @var array<int, int> */
    private $I;

    /** @var array<int, int> */
    private $L;

    /** @var array<int, int> */
    private $X;

    /** @var array<int, int> */
    private $Y;

    /**
     * Ed25519 constructor.
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct()
    {
        $this->D2 = [
            0xf159,
            0x26b2,
            0x9b94,
            0xebd6,
            0xb156,
            0x8283,
            0x149a,
            0x00e0,
            0xd130,
            0xeef3,
            0x80f2,
            0x198e,
            0xfce7,
            0x56df,
            0xd9dc,
            0x2406
        ];
        $this->D = [
            0x78a3,
            0x1359,
            0x4dca,
            0x75eb,
            0xd8ab,
            0x4141,
            0x0a4d,
            0x0070,
            0xe898,
            0x7779,
            0x4079,
            0x8cc7,
            0xfe73,
            0x2b6f,
            0x6cee,
            0x5203
        ];
        $this->gf0 = array_fill(0, 16, 0);
        $this->gf1 = [
            1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        ];
        $this->I = [
            0xa0b0,
            0x4a0e,
            0x1b27,
            0xc4ee,
            0xe478,
            0xad2f,
            0x1806,
            0x2f43,
            0xd7a7,
            0x3dfb,
            0x0099,
            0x2b4d,
            0xdf0b,
            0x4fc1,
            0x2480,
            0x2b83
        ];
        $this->L = [
            0xed,
            0xd3,
            0xf5,
            0x5c,
            0x1a,
            0x63,
            0x12,
            0x58,
            0xd6,
            0x9c,
            0xf7,
            0xa2,
            0xde,
            0xf9,
            0xde,
            0x14,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0x10
        ];
        $this->X = [
            0xd51a,
            0x8f25,
            0x2d60,
            0xc956,
            0xa7b2,
            0x9525,
            0xc760,
            0x692c,
            0xdc5c,
            0xfdd6,
            0xe231,
            0xc0a4,
            0x53fe,
            0xcd6e,
            0x36d3,
            0x2169
        ];
        $this->Y = [
            0x6658,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666,
            0x6666
        ];
    }

    /**
     * @param string $secretKey
     * @return string
     * @throws Exception
     */
    public function publicKeyFromSecretKey(string $secretKey): string
    {
        if (strlen($secretKey) !== self::SECRET_KEY_BYTES) {
            throw new Exception('length must be 64 bytes');
        }

        return substr($secretKey, 32, 32);
    }

    /**
     * @param string $seed
     * @return string
     * @throws Exception
     */
    public function secretKeyFromSeed(string $seed): string
    {
        if (strlen($seed) !== self::SEED_BYTES) {
            throw new Exception('seed length must be 32 bytes');
        }

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
     * @throws Exception
     */
    public function sign(string $message, string $secretKey): string
    {
        if (strlen($secretKey) !== self::SECRET_KEY_BYTES) {
            throw new Exception('secretKey length must be 64 bytes');
        }

        $sm = str_pad($message, 64 + strlen($message), "\x0", STR_PAD_LEFT);

        // хэшируем приватный ключик (32байта)
        $d = hash('sha512', substr($secretKey, 0, 32), true);
        $d[0] = chr(ord($d[0]) & 248); // d[0] &= 248
        $d[31] = chr(ord($d[31]) & 127); // d[31] &= 127
        $d[31] = chr(ord($d[31]) | 64); // d[31] |= 64

        // добавляем вторую половинку хэша к подписанному сообщению
        for ($i = 32; $i < 64; $i++) {
            $sm[$i] = $d[$i];
        }

        $r = hash('sha512', substr($sm, 32), true);
        $this->reduce($r);

        $p = array_fill(0, 4, array_fill(0, 16, 0));
        $this->scalarbase($p, $r);
        $this->pack($sm, $p);

        // добавляем публичный ключик?
        for ($i = 32; $i < 64; $i++) {
            $sm[$i] = $secretKey[$i];
        }

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
     * @throws Exception
     */
    public function verify(string $signature, string $message, string $publicKey): bool
    {
        if (strlen($signature) !== self::SIGNATURE_BYTES) {
            throw new Exception('signature length must be 64 bytes');
        }

        if (strlen($publicKey) !== self::PUBLIC_KEY_BYTES) {
            throw new Exception('publicKey length must be 32 bytes');
        }

        $q = array_fill(0, 4, array_fill(0, 16, 0));
        if (!$this->unpackneg($q, $publicKey)) {
            return false; // @codeCoverageIgnore
        }

        $m = $sm = $signature . $message;

        for ($i = 0; $i < 32; $i++) {
            $m[$i + 32] = $publicKey[$i];
        }

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
        $a = array_fill(0, 16, 0);
        $b = array_fill(0, 16, 0);
        $c = array_fill(0, 16, 0);
        $d = array_fill(0, 16, 0);
        $t = array_fill(0, 16, 0);
        $e = array_fill(0, 16, 0);
        $f = array_fill(0, 16, 0);
        $g = array_fill(0, 16, 0);
        $h = array_fill(0, 16, 0);

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
     * @param array<int, int> $o
     */
    private function car25519(array &$o): void
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
    private function cryptoVerify32(string $x, string $y): bool
    {
        $d = 0;
        for ($i = 0; $i < 32; $i++) {
            $d |= ord($x[$i]) ^ ord($y[$i]);
        }

        return (1 & (($d - 1) >> 8)) === 1;
    }

    /**
     * @param array<int, array<int, int>> $p
     * @param array<int, array<int, int>> $q
     * @param int $b
     */
    private function cswap(array &$p, array &$q, int $b): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->sel25519($p[$i], $q[$i], $b);
        }
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $a
     * @param array<int, int> $b
     */
    private function fnA(array &$o, array $a, array $b): void
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
    private function fnM(array &$o, array $a, array $b): void
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
    private function fnZ(array &$o, array $a, array $b): void
    {
        for ($i = 0; $i < 16; $i++) {
            $o[$i] = $a[$i] - $b[$i];
        }
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $i
     */
    private function inv25519(array &$o, array $i): void
    {
        $c = array_fill(0, 16, 0);

        for ($a = 0; $a < 16; $a++) {
            $c[$a] = $i[$a];
        }

        for ($a = 253; $a >= 0; $a--) {
            $this->fnM($c, $c, $c);
            if ($a != 2 && $a != 4) {
                $this->fnM($c, $c, $i);
            }
        }

        for ($a = 0; $a < 16; $a++) {
            $o[$a] = $c[$a];
        }
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
     * @param array<int, int> $a
     * @param array<int, int> $b
     * @return bool
     */
    private function neq25519(array $a, array $b): bool
    {
        $c = str_repeat("\x0", 32);
        $d = str_repeat("\x0", 32);

        $this->pack25519($c, $a);
        $this->pack25519($d, $b);

        return $this->cryptoVerify32($c, $d);
    }

    /**
     * @param string $r
     * @param array<int, array<int, int>> $p
     */
    private function pack(string &$r, array $p): void
    {
        $tx = array_fill(0, 16, 0);
        $ty = array_fill(0, 16, 0);
        $zi = array_fill(0, 16, 0);

        $this->inv25519($zi, $p[2]);
        $this->fnM($tx, $p[0], $zi);
        $this->fnM($ty, $p[1], $zi);
        $this->pack25519($r, $ty);

        $r[31] = chr(ord($r[31]) ^ $this->par25519($tx) << 7); // r[31] ^= par25519(tx) << 7;
    }

    /**
     * @param string $o
     * @param array<int, int> $n
     */
    private function pack25519(string &$o, array $n): void
    {
        $m = array_fill(0, 16, 0);
        $t = array_fill(0, 16, 0);

        for ($i = 0; $i < 16; $i++) {
            $t[$i] = $n[$i];
        }

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
            $bbb = ($m[15] >> 16) & 1;
            $m[14] &= 0xffff;
            $this->sel25519($t, $m, 1 - $bbb);
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
    private function par25519(array $a): int
    {
        $d = str_repeat("\x0", 32);
        $this->pack25519($d, $a);

        return ord($d[0]) & 1;
    }

    /**
     * @param array<int, int> $o
     * @param array<int, int> $i
     */
    private function pow2523(array &$o, array $i): void
    {
        $c = array_fill(0, 16, 0);

        for ($a = 0; $a < 16; $a++) {
            $c[$a] = $i[$a];
        }

        for ($a = 250; $a >= 0; $a--) {
            $this->fnM($c, $c, $c);
            if ($a != 1) {
                $this->fnM($c, $c, $i);
            }
        }

        for ($a = 0; $a < 16; $a++) {
            $o[$a] = $c[$a];
        }
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

    /**
     * @param array<int, int> $r
     * @param array<int, int> $a
     */
    private function set25519(array &$r, array $a): void
    {
        for ($i = 0; $i < 16; $i++) {
            $r[$i] = $a[$i];
        }
    }

    /**
     * @param array<int, int> $o
     * @param string $n
     */
    private function unpack25519(array &$o, string $n): void
    {
        for ($i = 0; $i < 16; $i++) {
            $o[$i] = ord($n[2 * $i]) + (ord($n[2 * $i + 1]) << 8);
        }
        $o[15] &= 0x7fff;
    }

    /**
     * @param array<int, array<int, int>> $r
     * @param string $p
     * @return bool
     */
    private function unpackneg(array &$r, string $p): bool
    {
        $t = array_fill(0, 16, 0);
        $chk = array_fill(0, 16, 0);
        $num = array_fill(0, 16, 0);
        $den = array_fill(0, 16, 0);
        $den2 = array_fill(0, 16, 0);
        $den4 = array_fill(0, 16, 0);
        $den6 = array_fill(0, 16, 0);

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
