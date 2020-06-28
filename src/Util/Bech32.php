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
 */
class Bech32
{
    /**
     * @param string $bytes
     * @return string
     * @throws Exception
     */
    public function encode(string $bytes): string
    {
        $cnv = new Converter();
        $version = (ord($bytes[0]) << 8) + ord($bytes[1]);
        $prefix = $cnv->versionToPrefix($version);

        $data = $this->convert8to5(substr($bytes, 2, 32));
        $checksum = $this->createChecksum($prefix, $data);

        return "{$prefix}1{$data}{$checksum}";
    }

    /**
     * @param string $bech32
     * @return string
     * @throws Exception
     */
    public function decode(string $bech32): string
    {
        if (strlen($bech32) !== 62) {
            throw new Exception('invalid length');
        }

        $bech32 = strtolower($bech32);
        $sepPos = strpos($bech32, '1');

        if ($sepPos === false) {
            throw new Exception('missing separator');
        }

        $pfx = substr($bech32, 0, $sepPos);
        $cnv = new Converter();
        $ver = $cnv->prefixToVersion($pfx);

        $data = substr($bech32, ($sepPos + 1));
        $this->checkAbc($data);
        $this->verifyChecksum($pfx, $data);

        $bytes = $this->convert5to8(substr($data, 0, -6));

        return chr($ver >> 8 & 0xff) . chr($ver & 0xff) . $bytes;
    }

    /** @var array<int, int> */
    private $generator = [
        0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3
    ];

    /** @var string */
    private $alphabeb = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

    /**
     * @param array<int, int> $values
     * @param int $numValues
     * @return int
     */
    private function polyMod(array $values, int $numValues): int
    {
        $chk = 1;
        for ($i = 0; $i < $numValues; $i++) {
            $top = $chk >> 25;
            $chk = ($chk & 0x1ffffff) << 5 ^ $values[$i];

            for ($j = 0; $j < 5; $j++) {
                $value = (($top >> $j) & 1) ? $this->generator[$j] : 0;
                $chk ^= $value;
            }
        }

        return $chk;
    }

    /**
     * Expands the human readable part into a character array for checksumming.
     * @param string $hrp
     * @return array<int, int>
     */
    private function hrpExpand(string $hrp): array
    {
        $expand1 = [];
        $expand2 = [];
        for ($i = 0, $l = strlen($hrp); $i < $l; $i++) {
            $ord = ord($hrp[$i]);
            $expand1[] = $ord >> 5;
            $expand2[] = $ord & 31;
        }

        return array_merge($expand1, [0], $expand2);
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
            $value = (int)strpos($this->alphabeb, $data[$i]);
            $acc = (($acc << 5) | $value) & 0xfff;
            $bits += 5;

            while ($bits >= 8) {
                $bits -= 8;
                $bytes .= chr(($acc >> $bits) & 0x1f);
            }
        }

        if ($bits >= 5 || ((($acc << (8 - $bits))) & 0x1f)) {
            throw new Exception('invalid data');
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
            $value = ord($bytes[$i]);
            $acc = (($acc << 8) | $value) & 0xfff;
            $bits += 8;

            while ($bits >= 5) {
                $bits -= 5;
                $res .= $this->alphabeb[(($acc >> $bits) & 0x1f)];
            }
        }

        if ($bits) {
            $res .= $this->alphabeb[($acc << 5 - $bits) & 0x1f];
        }

        return $res;
    }

    /**
     * @param string $hrp
     * @param string $convertedDataChars
     * @return string
     */
    protected function createChecksum(string $hrp, string $convertedDataChars): string
    {
        $values = array_merge($this->hrpExpand($hrp), $this->strToBytes($convertedDataChars));
        $polyMod = $this->polyMod(array_merge($values, [0, 0, 0, 0, 0, 0]), count($values) + 6) ^ 1;
        $res = '';

        for ($i = 0; $i < 6; $i++) {
            $res .= $this->alphabeb[($polyMod >> 5 * (5 - $i)) & 31];
        }

        return $res;
    }

    /**
     * Verifies the checksum given $hrp and $convertedDataChars.
     *
     * @param string $hrp
     * @param string $convertedDataChars
     * @throws Exception
     */
    private function verifyChecksum(string $hrp, string $convertedDataChars): void
    {
        $expandHrp = $this->hrpExpand($hrp);
        $arr = array_merge($expandHrp, $this->strToBytes($convertedDataChars));
        $poly = $this->polyMod($arr, count($arr));

        if ($poly !== 1) {
            throw new Exception('invalid checksum');
        }
    }

    /**
     * @param string $str
     * @throws Exception
     */
    private function checkAbc(string $str): void
    {
        for ($i = 0, $l = strlen($str); $i < $l; $i++) {
            if (strpos($this->alphabeb, $str[$i]) === false) {
                throw new Exception('invalid character');
            }
        }
    }

    /**
     * @param string $data
     * @return array<int, int>
     */
    private function strToBytes(string $data): array
    {
        return array_map(
            function (string $chr) {
                return (int)strpos($this->alphabeb, $chr);
            },
            str_split($data)
        );
    }
}
