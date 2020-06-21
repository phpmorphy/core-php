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

        $data = array_map(
            function (string $value): int {
                return ord($value);
            },
            str_split(substr($bytes, 2, 32))
        );

        return $this->encoder($prefix, $this->convertBits($data, count($data), 8, 5, true));
    }

    /**
     * @param string $bech32
     * @return string
     * @throws Exception
     */
    public function decode(string $bech32): string
    {
        $decoded = $this->decodeRaw($bech32);

        /** @var string $prefix */
        $prefix = $decoded[0];

        /** @var array<array-key, int> $words */
        $words = $decoded[1];

        $pubKey = array_reduce(
            $this->convertBits($words, count($words), 5, 8, false),
            function (string $carry, int $item): string {
                $carry .= chr($item);
                return $carry;
            },
            ''
        );

        if (strlen($pubKey) !== 32) {
            throw new Exception('data length should be 32 bytes');
        }

        $cnv = new Converter();
        $version = $cnv->prefixToVersion($prefix);

        return chr($version >> 8 & 0xff) . chr($version & 0xff) . $pubKey;
    }

    private const GENERATOR = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
    private const CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
    private const CHARKEY_KEY = [
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        15, -1, 10, 17, 21, 20, 26, 30,  7,  5, -1, -1, -1, -1, -1, -1,
        -1, 29, -1, 24, 13, 25,  9,  8, 23, -1, 18, 22, 31, 27, 19, -1,
        1,  0,  3, 16, 11, 28, 12, 14,  6,  4,  2, -1, -1, -1, -1, -1,
        -1, 29, -1, 24, 13, 25,  9,  8, 23, -1, 18, 22, 31, 27, 19, -1,
        1,  0,  3, 16, 11, 28, 12, 14,  6,  4,  2, -1, -1, -1, -1, -1
    ];

    /**
     * @param int[] $values
     * @param int $numValues
     * @return int
     */
    private function polyMod(array $values, $numValues)
    {
        $chk = 1;
        for ($i = 0; $i < $numValues; $i++) {
            $top = $chk >> 25;
            $chk = ($chk & 0x1ffffff) << 5 ^ $values[$i];

            for ($j = 0; $j < 5; $j++) {
                $value = (($top >> $j) & 1) ? self::GENERATOR[$j] : 0;
                $chk ^= $value;
            }
        }

        return $chk;
    }

    /**
     * Expands the human readable part into a character array for checksumming.
     * @param string $hrp
     * @param int $hrpLen
     * @return int[]
     */
    private function hrpExpand($hrp, $hrpLen)
    {
        $expand1 = [];
        $expand2 = [];
        for ($i = 0; $i < $hrpLen; $i++) {
            $ord = ord($hrp[$i]);
            $expand1[] = $ord >> 5;
            $expand2[] = $ord & 31;
        }

        return array_merge($expand1, [0], $expand2);
    }

    /**
     * Converts words of $fromBits bits to $toBits bits in size.
     *
     * @param int[] $data - character array of data to convert
     * @param int $inLen - number of elements in array
     * @param int $fromBits - word (bit count) size of provided data
     * @param int $toBits - requested word size (bit count)
     * @param bool $pad - whether to pad (only when encoding)
     * @return int[]
     * @throws Exception
     */
    private function convertBits(array $data, $inLen, $fromBits, $toBits, $pad = true)
    {
        $acc = 0;
        $bits = 0;
        $ret = [];
        $maxv = (1 << $toBits) - 1;
        $maxacc = (1 << ($fromBits + $toBits - 1)) - 1;

        for ($i = 0; $i < $inLen; $i++) {
            $value = $data[$i];
            if ($value < 0 || $value >> $fromBits) {
                throw new Exception('Invalid value for convert bits');
            }

            $acc = (($acc << $fromBits) | $value) & $maxacc;
            $bits += $fromBits;

            while ($bits >= $toBits) {
                $bits -= $toBits;
                $ret[] = (($acc >> $bits) & $maxv);
            }
        }

        if ($pad) {
            if ($bits) {
                $ret[] = ($acc << $toBits - $bits) & $maxv;
            }
        } else if ($bits >= $fromBits || ((($acc << ($toBits - $bits))) & $maxv)) {
            throw new Exception('Invalid data');
        }

        return $ret;
    }

    /**
     * @param string $hrp
     * @param int[] $convertedDataChars
     * @return int[]
     */
    private function createChecksum($hrp, array $convertedDataChars)
    {
        $values = array_merge($this->hrpExpand($hrp, strlen($hrp)), $convertedDataChars);
        $polyMod = $this->polyMod(array_merge($values, [0, 0, 0, 0, 0, 0]), count($values) + 6) ^ 1;
        $results = [];
        for ($i = 0; $i < 6; $i++) {
            $results[$i] = ($polyMod >> 5 * (5 - $i)) & 31;
        }

        return $results;
    }

    /**
     * Verifies the checksum given $hrp and $convertedDataChars.
     *
     * @param string $hrp
     * @param int[] $convertedDataChars
     * @return bool
     */
    private function verifyChecksum($hrp, array $convertedDataChars)
    {
        $expandHrp = $this->hrpExpand($hrp, strlen($hrp));
        $arr = array_merge($expandHrp, $convertedDataChars);
        $poly = $this->polyMod($arr, count($arr));
        return $poly === 1;
    }

    /**
     * @param string $hrp
     * @param array $combinedDataChars
     * @return string
     */
    private function encoder($hrp, array $combinedDataChars)
    {
        $checksum = $this->createChecksum($hrp, $combinedDataChars);
        $characters = array_merge($combinedDataChars, $checksum);

        $encoded = [];
        for ($i = 0, $n = count($characters); $i < $n; $i++) {
            $encoded[$i] = self::CHARSET[$characters[$i]];
        }

        return "{$hrp}1" . implode('', $encoded);
    }

    /**
     * @param $sBech - the bech32 encoded string
     * @return array - returns [$hrp, $dataChars]
     * @throws Exception
     */
    private function decodeRaw($sBech)
    {
        $length = strlen($sBech);
        if ($length < 8) {
            throw new Exception("Bech32 string is too short");
        }

        $chars = array_values(unpack('C*', $sBech));

        $haveUpper = false;
        $haveLower = false;
        $positionOne = -1;

        for ($i = 0; $i < $length; $i++) {
            $chr = $chars[$i];
            if ($chr < 33 || $chr > 126) {
                throw new Exception('Out of range character in bech32 string');
            }

            if ($chr >= 0x61 && $chr <= 0x7a) {
                $haveLower = true;
            }

            if ($chr >= 0x41 && $chr <= 0x5a) {
                $haveUpper = true;
                $chr = $chars[$i] = $chr + 0x20;
            }

            // find location of last '1' character
            if ($chr === 0x31) {
                $positionOne = $i;
            }
        }

        if ($haveUpper && $haveLower) {
            throw new Exception('Data contains mixture of higher/lower case characters');
        }

        if ($positionOne === -1) {
            throw new Exception("Missing separator character");
        }

        if ($positionOne < 1) {
            throw new Exception("Empty HRP");
        }

        if (($positionOne + 7) > $length) {
            throw new Exception('Too short checksum');
        }

        $hrp = pack("C*", ...array_slice($chars, 0, $positionOne));

        $data = [];
        for ($i = $positionOne + 1; $i < $length; $i++) {
            $data[] = ($chars[$i] & 0x80) ? -1 : self::CHARKEY_KEY[$chars[$i]];
        }

        if (!$this->verifyChecksum($hrp, $data)) {
            throw new Exception('Invalid bech32 checksum');
        }

        return [$hrp, array_slice($data, 0, -6)];
    }
}
