<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Util\Ed25519;

/**
 * Class AbstractEd25519
 * Implementation derived from TweetNaCl version 20140427.
 * @see http://tweetnacl.cr.yp.to/
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
abstract class AbstractEd25519
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
     * @param array<int, int> $p
     * @param array<int, int> $q
     * @param int $b
     */
    protected function sel25519(array &$p, array &$q, int $b): void
    {
        $c = ~($b - 1);
        for ($i = 0; $i < 16; $i++) {
            $ttt = $c & ($p[$i] ^ $q[$i]);
            $p[$i] ^= $ttt;
            $q[$i] ^= $ttt;
        }
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
}
