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
 * Class Bech32
 * @package UmiTop\UmiCore\Util
 */
class Bech32
{
    use ConverterTrait;

    /** @var string */
    private $alphabet = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

    /** @var array<int, int> */
    private $generator = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];

    /**
     * @param string $bech32
     * @return string
     * @throws Exception
     */
    public function decode(string $bech32): string
    {
        if (strlen($bech32) !== 62 && strlen($bech32) !== 66) {
            throw new Exception('bech32: invalid length');
        }

        $bech32 = strtolower($bech32);
        $sepPos = strpos($bech32, '1');

        if ($sepPos === false) {
            throw new Exception('bech32: missing separator');
        }

        $pfx = substr($bech32, 0, $sepPos);
        $data = substr($bech32, ($sepPos + 1));
        $this->checkAlphabet($data);
        $this->verifyChecksum($pfx, $data);

        return $this->prefixToBytes($pfx) . $this->convert5to8(substr($data, 0, -6));
    }

    /**
     * @param string $bytes
     * @return string
     * @throws Exception
     */
    public function encode(string $bytes): string
    {
        $prefix = $this->bytesToPrefix(substr($bytes, 0, 2));
        $data = $this->convert8to5(substr($bytes, 2, 32));
        $checksum = $this->createChecksum($prefix, $data);

        return "{$prefix}1{$data}{$checksum}";
    }

    /**
     * @param string $str
     * @throws Exception
     */
    private function checkAlphabet(string $str): void
    {
        for ($i = 0, $l = strlen($str); $i < $l; $i++) {
            if (strpos($this->alphabet, $str[$i]) === false) {
                throw new Exception('bech32: invalid character');
            }
        }
    }

    /**
     * @param string $data
     * @return string
     * @throws Exception
     */
    private function convert5to8(string $data): string
    {
        $acc = 0;
        $bits = 0;
        $bytes = '';

        for ($i = 0, $l = strlen($data); $i < $l; $i++) {
            $acc = ($acc << 5) | (int)strpos($this->alphabet, $data[$i]);
            $bits += 5;

            while ($bits >= 8) {
                $bits -= 8;
                $bytes .= chr(($acc >> $bits) & 0xff);
            }
        }

        if ($bits >= 5 || ((($acc << (8 - $bits))) & 0xff)) {
            throw new Exception('bech32: invalid data');
        }

        return $bytes;
    }

    /**
     * @param string $bytes
     * @return string
     */
    private function convert8to5(string $bytes): string
    {
        $acc = 0;
        $bits = 0;
        $res = '';

        for ($i = 0, $l = strlen($bytes); $i < $l; $i++) {
            $acc = ($acc << 8) | ord($bytes[$i]);
            $bits += 8;

            while ($bits >= 5) {
                $bits -= 5;
                $res .= $this->alphabet[(($acc >> $bits) & 0x1f)];
            }
        }

        if ($bits) {
            $res .= $this->alphabet[($acc << 5 - $bits) & 0x1f];
        }

        return $res;
    }

    /**
     * @param string $prefix
     * @param string $data
     * @return string
     */
    private function createChecksum(string $prefix, string $data): string
    {
        $values = array_merge(
            $this->prefixExpand($prefix),
            $this->strToBytes($data),
            array_fill(0, 6, 0)
        );
        $polyMod = $this->polyMod($values) ^ 1;

        $checksum = '';
        for ($i = 0; $i < 6; $i++) {
            $checksum .= $this->alphabet[($polyMod >> 5 * (5 - $i)) & 31];
        }

        return $checksum;
    }

    /**
     * @param array<int, int> $values
     * @return int
     */
    private function polyMod(array $values): int
    {
        $chk = 1;
        for ($i = 0, $l = count($values); $i < $l; $i++) {
            $top = $chk >> 25;
            $chk = ($chk & 0x1ffffff) << 5 ^ $values[$i];

            for ($j = 0; $j < 5; $j++) {
                $value = (($top >> $j) & 1)
                    ? $this->generator[$j]
                    : 0;
                $chk ^= $value;
            }
        }

        return $chk;
    }

    /**
     * @param string $prefix
     * @return array<int, int>
     */
    private function prefixExpand(string $prefix): array
    {
        $len = strlen($prefix);
        $res = array_fill(0, (($len * 2) + 1), 0);
        for ($i = 0; $i < $len; $i++) {
            $ord = ord($prefix[$i]);
            $res[$i] = $ord >> 5;
            $res[$i + $len + 1] = $ord & 31;
        }

        return $res;
    }

    /**
     * @param string $data
     * @return array<int, int>
     */
    private function strToBytes(string $data): array
    {
        return array_map(
            function (string $chr) {
                return (int)strpos($this->alphabet, $chr);
            },
            str_split($data)
        );
    }

    /**
     * @param string $prefix
     * @param string $data
     * @throws Exception
     */
    private function verifyChecksum(string $prefix, string $data): void
    {
        $poly = $this->polyMod(array_merge($this->prefixExpand($prefix), $this->strToBytes($data)));

        if ($poly !== 1) {
            throw new Exception('bech32: invalid checksum');
        }
    }
}
