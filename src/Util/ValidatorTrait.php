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
 * Trait ValidatorTrait
 * @package UmiTop\UmiCore\Util
 */
trait ValidatorTrait
{
    /**
     * @param int $val
     * @param int|null $min
     * @param int|null $max
     * @throws Exception
     */
    private function validateInt(int $val, int $min = null, int $max = null): void
    {
        if ($min !== null && $val < $min) {
            throw new Exception('invalid value');
        }

        if ($max !== null && $val > $max) {
            throw new Exception('invalid value');
        }
    }

    /**
     * @param string $val
     * @param int $length
     * @throws Exception
     */
    private function validateStr(string $val, int $length): void
    {
        if (strlen($val) !== $length) {
            throw new Exception('invalid length');
        }
    }
}
