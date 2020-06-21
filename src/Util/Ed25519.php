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
use SplFixedArray;

/**
 * Class Ed25519
 * @package UmiTop\UmiCore\Util
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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

        return substr($secretKey, 32);
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

        if (function_exists('sodium_crypto_sign_seed_keypair') && !defined('PHPUNIT')) {
            return sodium_crypto_sign_secretkey(
                sodium_crypto_sign_seed_keypair($seed)
            );
        }

        $ppp = $this->gfN(4);

        $ddd = hash('sha512', $seed, true);
        $ddd[0] = chr(ord($ddd[0]) & 248);   // d[0] &= 248;
        $ddd[31] = chr(ord($ddd[31]) & 127); // d[31] &= 127;
        $ddd[31] = chr(ord($ddd[31]) | 64);  // d[31] |= 64;

        $pub = str_repeat("\x0", 32);

        $this->scalarbase($ppp, $ddd);
        $this->pack($pub, $ppp);

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

        if (function_exists('sodium_crypto_sign_detached') && !defined('PHPUNIT')) {
            return sodium_crypto_sign_detached($message, $secretKey);
        }

        $msgLen = strlen($message);
        $sigMsg = str_pad($message, 64 + $msgLen, "\x0", STR_PAD_LEFT);

        // хэшируем приватный ключик (32байта)
        $ddd = hash('sha512', substr($secretKey, 0, 32), true);
        $ddd[0] = chr(ord($ddd[0]) & 248); // d[0] &= 248
        $ddd[31] = chr(ord($ddd[31]) & 127); // d[31] &= 127
        $ddd[31] = chr(ord($ddd[31]) | 64); // d[31] |= 64

        // добавляем вторую половинку хэша к подписанному сообщению
        for ($i = 32; $i < 64; $i++) {
            $sigMsg[$i] = $ddd[$i];
        }

        $rrr = hash('sha512', substr($sigMsg, 32), true);
        $this->reduce($rrr);

        $ppp = $this->gfN(4);
        $this->scalarbase($ppp, $rrr);
        $this->pack($sigMsg, $ppp);

        // добавляем публичный ключик?
        for ($i = 32; $i < 64; $i++) {
            $sigMsg[$i] = $secretKey[$i];
        }

        $hhh = hash('sha512', $sigMsg, true);
        $this->reduce($hhh);

        $xxx = new SplFixedArray(64);
        for ($i = 0; $i < 64; $i++) {
            $xxx[$i] = 0;
        }
        for ($i = 0; $i < 32; $i++) {
            $xxx[$i] = ord($rrr[$i]);
        }
        for ($i = 0; $i < 32; $i++) {
            for ($j = 0; $j < 32; $j++) {
                $xxx[$i + $j] += ord($hhh[$i]) * ord($ddd[$j]);
            }
        }

        $sm2 = substr($sigMsg, 32);
        $this->modL($sm2, $xxx);

        return substr($sigMsg, 0, 32) . substr($sm2, 0, 32);
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

        if (function_exists('sodium_crypto_sign_verify_detached') && !defined('PHPUNIT')) {
            return sodium_crypto_sign_verify_detached($signature, $message, $publicKey);
        }

        $qqq = $this->gfN(4);
        if (!$this->unpackneg($qqq, $publicKey)) {
            return false;
        }

        $msg = $sigMsg = $signature . $message;

        for ($i = 0; $i < 32; $i++) {
            $msg[$i + 32] = $publicKey[$i];
        }

        $hhh = hash('sha512', $msg, true);
        $this->reduce($hhh);

        $ppp = $this->gfN(4);
        $this->scalarmult($ppp, $qqq, $hhh);
        $this->scalarbase($qqq, substr($sigMsg, 32));
        $this->add($ppp, $qqq);

        $ttt = str_repeat("\x0", 32);
        $this->pack($ttt, $ppp);

        return $this->cryptoVerify32($sigMsg, $ttt);
    }

    /**
     * @param SplFixedArray $ppp
     * @param SplFixedArray $qqq
     */
    private function add(SplFixedArray $ppp, SplFixedArray $qqq): void
    {
        $dD2 = SplFixedArray::fromArray(
            [
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
            ]
        );
        $aaa = new SplFixedArray(16);
        $bbb = new SplFixedArray(16);
        $ccc = new SplFixedArray(16);
        $ddd = new SplFixedArray(16);
        $ttt = new SplFixedArray(16);
        $eee = new SplFixedArray(16);
        $fff = new SplFixedArray(16);
        $ggg = new SplFixedArray(16);
        $hhh = new SplFixedArray(16);

        $this->fnZ($aaa, $ppp[1], $ppp[0]);
        $this->fnZ($ttt, $qqq[1], $qqq[0]);
        $this->fnM($aaa, $aaa, $ttt);
        $this->fnA($bbb, $ppp[0], $ppp[1]);
        $this->fnA($ttt, $qqq[0], $qqq[1]);
        $this->fnM($bbb, $bbb, $ttt);
        $this->fnM($ccc, $ppp[3], $qqq[3]);
        $this->fnM($ccc, $ccc, $dD2);
        $this->fnM($ddd, $ppp[2], $qqq[2]);
        $this->fnA($ddd, $ddd, $ddd);
        $this->fnZ($eee, $bbb, $aaa);
        $this->fnZ($fff, $ddd, $ccc);
        $this->fnA($ggg, $ddd, $ccc);
        $this->fnA($hhh, $bbb, $aaa);

        $this->fnM($ppp[0], $eee, $fff);
        $this->fnM($ppp[1], $hhh, $ggg);
        $this->fnM($ppp[2], $ggg, $fff);
        $this->fnM($ppp[3], $eee, $hhh);
    }

    /**
     * @param SplFixedArray $out
     */
    private function car25519(SplFixedArray $out): void
    {
        for ($i = 0; $i < 16; $i++) {
            $out[$i] += (1 << 16);
            $ccc = $out[$i] >> 16;
            $out[($i + 1) * ($i < 15)] += $ccc - 1 + 37 * ($ccc - 1) * ($i == 15);
            $out[$i] -= $ccc << 16;
        }
    }

    /**
     * @param string $xxx
     * @param string $yyy
     * @return bool
     */
    private function cryptoVerify32(string $xxx, string $yyy): bool
    {
        $ddd = 0;
        for ($i = 0; $i < 32; $i++) {
            $ddd |= ord($xxx[$i]) ^ ord($yyy[$i]);
        }

        return (1 & (($ddd - 1) >> 8)) === 1;
    }

    /**
     * @param SplFixedArray $ppp
     * @param SplFixedArray $qqq
     * @param int $bbb
     */
    private function cswap(SplFixedArray $ppp, SplFixedArray $qqq, int $bbb): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->sel25519($ppp[$i], $qqq[$i], $bbb);
        }
    }

    /**
     * @param SplFixedArray $out
     * @param SplFixedArray $in1
     * @param SplFixedArray $in2
     */
    private function fnA(SplFixedArray $out, SplFixedArray $in1, SplFixedArray $in2): void
    {
        for ($i = 0; $i < 16; $i++) {
            $out[$i] = $in1[$i] + $in2[$i];
        }
    }

    /**
     * @param SplFixedArray $out
     * @param SplFixedArray $aaa
     * @param SplFixedArray $bbb
     */
    private function fnM(SplFixedArray $out, SplFixedArray $aaa, SplFixedArray $bbb): void
    {
        $ttt = new SplFixedArray(31);

        // заполняем нулям
        for ($i = 0; $i < 31; $i++) {
            $ttt[$i] = 0;
        }

        for ($i = 0; $i < 16; $i++) {
            for ($j = 0; $j < 16; $j++) {
                $ttt[$i + $j] += $aaa[$i] * $bbb[$j];
            }
        }
        for ($i = 0; $i < 15; $i++) {
            $ttt[$i] += 38 * $ttt[$i + 16];
        }
        for ($i = 0; $i < 16; $i++) {
            $out[$i] = $ttt[$i];
        }

        $this->car25519($out);
        $this->car25519($out);
    }

    /**
     * @param SplFixedArray $out
     * @param SplFixedArray $in1
     * @param SplFixedArray $in2
     */
    private function fnZ(SplFixedArray $out, SplFixedArray $in1, SplFixedArray $in2): void
    {
        for ($i = 0; $i < 16; $i++) {
            $out[$i] = $in1[$i] - $in2[$i];
        }
    }

    /**
     * @param int $nnn
     * @return SplFixedArray
     */
    private function gfN(int $nnn): SplFixedArray
    {
        $gfn = new SplFixedArray($nnn);
        for ($i = 0; $i < $nnn; $i++) {
            $gfn[$i] = new SplFixedArray(16);
        }
        return $gfn;
    }

    /**
     * @param SplFixedArray $out
     * @param SplFixedArray $inp
     */
    private function inv25519(SplFixedArray $out, SplFixedArray $inp): void
    {
        $ccc = new SplFixedArray(16);

        for ($a = 0; $a < 16; $a++) {
            $ccc[$a] = $inp[$a];
        }

        for ($a = 253; $a >= 0; $a--) {
            $this->fnM($ccc, $ccc, $ccc);
            if ($a != 2 && $a != 4) {
                $this->fnM($ccc, $ccc, $inp);
            }
        }

        for ($a = 0; $a < 16; $a++) {
            $out[$a] = $ccc[$a];
        }
    }

    /**
     * @param string $rrr
     * @param SplFixedArray $xxx
     */
    private function modL(string &$rrr, SplFixedArray $xxx): void
    {
        $llL = SplFixedArray::fromArray(
            [
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
            ]
        );

        for ($i = 63; $i >= 32; --$i) {
            $carry = 0;
            for ($j = $i - 32; $j < $i - 12; ++$j) {
                $xxx[$j] += $carry - 16 * $xxx[$i] * $llL[$j - ($i - 32)];
                $carry = ($xxx[$j] + 128) >> 8;
                $xxx[$j] -= $carry << 8;
            }
            $xxx[$j] += $carry;
            $xxx[$i] = 0;
        }

        $carry = 0;
        for ($j = 0; $j < 32; $j++) {
            $xxx[$j] += $carry - ($xxx[31] >> 4) * $llL[$j];
            $carry = $xxx[$j] >> 8;
            $xxx[$j] &= 255;
        }

        for ($j = 0; $j < 32; $j++) {
            $xxx[$j] -= $carry * $llL[$j];
        }

        for ($i = 0; $i < 32; $i++) {
            $xxx[$i + 1] += $xxx[$i] >> 8;
            $rrr[$i] = chr($xxx[$i] & 255);
        }
    }

    /**
     * @param SplFixedArray $aaa
     * @param SplFixedArray $bbb
     * @return bool
     */
    private function neq25519(SplFixedArray $aaa, SplFixedArray $bbb): bool
    {
        $ccc = str_repeat("\x0", 32);
        $ddd = str_repeat("\x0", 32);

        $this->pack25519($ccc, $aaa);
        $this->pack25519($ddd, $bbb);

        return $this->cryptoVerify32($ccc, $ddd);
    }

    /**
     * @param string $rrr
     * @param SplFixedArray $ppp
     */
    private function pack(string &$rrr, SplFixedArray $ppp): void
    {
        $tx0 = new SplFixedArray(16);
        $ty0 = new SplFixedArray(16);
        $zi0 = new SplFixedArray(16);

        $this->inv25519($zi0, $ppp[2]);
        $this->fnM($tx0, $ppp[0], $zi0);
        $this->fnM($ty0, $ppp[1], $zi0);
        $this->pack25519($rrr, $ty0);

        $rrr[31] = chr(ord($rrr[31]) ^ $this->par25519($tx0) << 7); // r[31] ^= par25519(tx) << 7;
    }

    /**
     * @param string $out
     * @param SplFixedArray $nnn
     */
    private function pack25519(string &$out, SplFixedArray $nnn): void
    {
        $mmm = new SplFixedArray(16);
        $ttt = new SplFixedArray(16);

        for ($i = 0; $i < 16; $i++) {
            $ttt[$i] = $nnn[$i];
        }

        $this->car25519($ttt);
        $this->car25519($ttt);
        $this->car25519($ttt);

        for ($j = 0; $j < 2; $j++) {
            $mmm[0] = $ttt[0] - 0xffed;
            for ($i = 1; $i < 15; $i++) {
                $mmm[$i] = $ttt[$i] - 0xffff - (($mmm[$i - 1] >> 16) & 1);
                $mmm[$i - 1] &= 0xffff;
            }
            $mmm[15] = $ttt[15] - 0x7fff - (($mmm[14] >> 16) & 1);
            $bbb = ($mmm[15] >> 16) & 1;
            $mmm[14] &= 0xffff;
            $this->sel25519($ttt, $mmm, 1 - $bbb);
        }

        for ($i = 0; $i < 16; $i++) {
            $out[2 * $i] = chr($ttt[$i] & 0xff);
            $out[2 * $i + 1] = chr($ttt[$i] >> 8);
        }
    }

    /**
     * @param SplFixedArray $aaa
     * @return int
     */
    private function par25519(SplFixedArray $aaa): int
    {
        $ddd = str_repeat("\x0", 32);
        $this->pack25519($ddd, $aaa);

        return ord($ddd[0]) & 1;
    }

    /**
     * @param SplFixedArray $out
     * @param SplFixedArray $inp
     */
    private function pow2523(SplFixedArray $out, SplFixedArray $inp): void
    {
        $ccc = new SplFixedArray(16);

        for ($a = 0; $a < 16; $a++) {
            $ccc[$a] = $inp[$a];
        }

        for ($a = 250; $a >= 0; $a--) {
            $this->fnM($ccc, $ccc, $ccc);
            if ($a != 1) {
                $this->fnM($ccc, $ccc, $inp);
            }
        }

        for ($a = 0; $a < 16; $a++) {
            $out[$a] = $ccc[$a];
        }
    }

    /**
     * @param string $rrr
     */
    private function reduce(string &$rrr): void
    {
        $xxx = new SplFixedArray(64); // int64[64]

        for ($i = 0; $i < 64; $i++) {
            $xxx[$i] = ord($rrr[$i]);
        }

        for ($i = 0; $i < 64; $i++) {
            $rrr[$i] = chr(0);
        }

        $this->modL($rrr, $xxx);
    }

    /**
     * @param SplFixedArray $ppp
     * @param string $sss
     */
    private function scalarbase(SplFixedArray $ppp, string $sss): void
    {
        $qqq = $this->gfN(4);
        $gf1 = SplFixedArray::fromArray([1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
        $xxX = SplFixedArray::fromArray(
            [
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
            ]
        );
        $yyY = SplFixedArray::fromArray(
            [
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
            ]
        );

        $this->set25519($qqq[0], $xxX);
        $this->set25519($qqq[1], $yyY);
        $this->set25519($qqq[2], $gf1);
        $this->fnM($qqq[3], $xxX, $yyY);
        $this->scalarmult($ppp, $qqq, $sss);
    }

    /**
     * @param SplFixedArray $ppp
     * @param SplFixedArray $qqq
     * @param string $sss
     */
    private function scalarmult(SplFixedArray $ppp, SplFixedArray $qqq, string $sss): void
    {
        $gf0 = new SplFixedArray(16);
        $gf1 = SplFixedArray::fromArray([1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);

        $this->set25519($ppp[0], $gf0);
        $this->set25519($ppp[1], $gf1);
        $this->set25519($ppp[2], $gf1);
        $this->set25519($ppp[3], $gf0);

        for ($i = 255; $i >= 0; --$i) {
            $bbb = (ord($sss[($i / 8) | 0]) >> ($i & 7)) & 1;
            $this->cswap($ppp, $qqq, $bbb);
            $this->add($qqq, $ppp);
            $this->add($ppp, $ppp);
            $this->cswap($ppp, $qqq, $bbb);
        }
    }

    /**
     * @param SplFixedArray $ppp
     * @param SplFixedArray $qqq
     * @param int $bbb
     */
    private function sel25519(SplFixedArray $ppp, SplFixedArray $qqq, int $bbb): void
    {
        $ccc = ~($bbb - 1);
        for ($i = 0; $i < 16; $i++) {
            $ttt = $ccc & ($ppp[$i] ^ $qqq[$i]);
            $ppp[$i] ^= $ttt;
            $qqq[$i] ^= $ttt;
        }
    }

    /**
     * @param SplFixedArray $rrr
     * @param SplFixedArray $aaa
     */
    private function set25519(SplFixedArray $rrr, SplFixedArray $aaa): void
    {
        for ($i = 0; $i < 16; $i++) {
            $rrr[$i] = $aaa[$i]; // | 0; ??
        }
    }

    /**
     * @param SplFixedArray $out
     * @param string $nnn
     */
    private function unpack25519(SplFixedArray $out, string $nnn): void
    {
        for ($i = 0; $i < 16; $i++) {
            $out[$i] = ord($nnn[2 * $i]) + (ord($nnn[2 * $i + 1]) << 8);
        }
        $out[15] &= 0x7fff;
    }

    /**
     * @param SplFixedArray $rrr
     * @param string $ppp
     * @return bool
     */
    private function unpackneg(SplFixedArray $rrr, string $ppp): bool
    {
        $gf0 = new SplFixedArray(16);
        $gf1 = SplFixedArray::fromArray([1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
        $ddD = SplFixedArray::fromArray(
            [
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
            ]
        );
        $iiI = SplFixedArray::fromArray(
            [
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
            ]
        );
        $ttt = new SplFixedArray(16);
        $chk = new SplFixedArray(16);
        $num = new SplFixedArray(16);
        $den = new SplFixedArray(16);
        $den2 = new SplFixedArray(16);
        $den4 = new SplFixedArray(16);
        $den6 = new SplFixedArray(16);

        $this->set25519($rrr[2], $gf1);
        $this->unpack25519($rrr[1], $ppp);

        $this->fnM($num, $rrr[1], $rrr[1]);
        $this->fnM($den, $num, $ddD);
        $this->fnZ($num, $num, $rrr[2]);
        $this->fnA($den, $rrr[2], $den);

        $this->fnM($den2, $den, $den);
        $this->fnM($den4, $den2, $den2);
        $this->fnM($den6, $den4, $den2);
        $this->fnM($ttt, $den6, $num);
        $this->fnM($ttt, $ttt, $den);

        $this->pow2523($ttt, $ttt);
        $this->fnM($ttt, $ttt, $num);
        $this->fnM($ttt, $ttt, $den);
        $this->fnM($ttt, $ttt, $den);
        $this->fnM($rrr[0], $ttt, $den);

        $this->fnM($chk, $rrr[0], $rrr[0]);
        $this->fnM($chk, $chk, $den);

        if (!$this->neq25519($chk, $num)) {
            $this->fnM($rrr[0], $rrr[0], $iiI);
        }

        $this->fnM($chk, $rrr[0], $rrr[0]);
        $this->fnM($chk, $chk, $den);

        if (!$this->neq25519($chk, $num)) {
            return false;
        }

        if ($this->par25519($rrr[0]) === (ord($ppp[31]) >> 7)) {
            $this->fnZ($rrr[0], $gf0, $rrr[0]);
        }

        $this->fnM($rrr[3], $rrr[0], $rrr[1]);

        return true;
    }
}
