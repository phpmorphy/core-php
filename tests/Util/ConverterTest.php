<?php

declare(strict_types=1);

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Util\Converter;

class ConverterTest extends TestCase
{
    /**
     * @dataProvider versionProvider
     */
    public function testVersionToPrefix(int $version, string $expected): void
    {
        $cnv = new Converter();
        $actual = $cnv->versionToPrefix($version);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array<string, int|string>>
     */
    public function versionProvider(): array
    {
        return [
            'genesis' => [
                'ver' => 0,
                'pfx' => 'genesis'
            ],
            'aaa' => [
                'ver' => 1057,
                'pfx' => 'aaa'
            ],
            'abc' => [
                'ver' => 1091,
                'pfx' => 'abc'
            ],
            'umi' => [
                'ver' => 21929,
                'pfx' => 'umi'
            ],
            'zzz' => [
                'ver' => 27482,
                'pfx' => 'zzz'
            ]
        ];
    }

    /**
     * @dataProvider invalidVersionProvider
     */
    public function testVersionToPrefixException(int $version): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $cnv = new Converter();
        $cnv->versionToPrefix($version);
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function invalidVersionProvider(): array
    {
        return [
            'first chr (<1)' => [
                'ver' => (0 << 10) + (1 << 5) + 1
            ],
            'first chr (>26)' => [
                'ver' => (27 << 10) + (1 << 5) + 1
            ],
            'second chr (<1)' => [
                'ver' => (1 << 10) + (0 << 5) + 1
            ],
            'second chr (>26)' => [
                'ver' => (1 << 10) + (27 << 5) + 1
            ],
            'third chr (<1)' => [
                'ver' => (1 << 10) + (1 << 5),
            ],
            'third chr (>26)' => [
                'ver' => (1 << 10) + (1 << 5) + 27,
            ]
        ];
    }

    /**
     * @dataProvider versionProvider
     */
    public function testPrefixToVersion(int $expected, string $prefix): void
    {
        $cnv = new Converter();
        $actual = $cnv->prefixToVersion($prefix);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider invalidPrefixProvider
     */
    public function testPrefixToVersionException(string $prefix): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $cnv = new Converter();
        $cnv->prefixToVersion($prefix);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function invalidPrefixProvider(): array
    {
        return [
            'ab' => [
                'pfx' => 'ab'
            ],
            'abcd' => [
                'pfx' => 'abcd'
            ],
            'Abc' => [
                'pfx' => 'Abc'
            ],
            'aBc' => [
                'pfx' => 'aBc'
            ],
            'abC' => [
                'pfx' => 'abC'
            ]
        ];
    }
}
