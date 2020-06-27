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
 * Class Converter
 */
class Converter
{
    /**
     * @param int $version
     * @return string
     * @throws Exception
     */
    public function versionToPrefix(int $version): string
    {
        if ($version === 0) {
            return 'genesis';
        }

        $ch1 = $version >> 10 & 0x1F;
        $ch2 = $version >> 5 & 0x1F;
        $ch3 = $version & 0x1F;

        if ($ch1 < 1 || $ch1 > 26) {
            throw new Exception('invalid version [1]');
        }

        if ($ch2 < 1 || $ch2 > 26) {
            throw new Exception('invalid version [2]');
        }

        if ($ch3 < 1 || $ch3 > 26) {
            throw new Exception('invalid version [3]');
        }

        return chr($ch1 + 96) . chr($ch2 + 96) . chr($ch3 + 96);
    }

    /**
     * @param string $prefix
     * @return int
     * @throws Exception
     */
    public function prefixToVersion(string $prefix): int
    {
        if ($prefix === 'genesis') {
            return 0;
        }

        if (strlen($prefix) !== 3) {
            throw new Exception('invalid prefix length');
        }

        $ch1 = ord($prefix[0]) - 96;
        $ch2 = ord($prefix[1]) - 96;
        $ch3 = ord($prefix[2]) - 96;

        if ($ch1 < 1 || $ch1 > 26) {
            throw new Exception('invalid prefix [1]');
        }

        if ($ch2 < 1 || $ch2 > 26) {
            throw new Exception('invalid prefix [2]');
        }

        if ($ch3 < 1 || $ch3 > 26) {
            throw new Exception('invalid prefix [3]');
        }

        return ($ch1 << 10) + ($ch2 << 5) + $ch3;
    }
}
