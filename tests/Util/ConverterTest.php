<?php

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Util\Converter;

class ConverterTest extends TestCase
{
    /**
     * @dataProvider versionProvider
     */
    public function testVersionToPrefix($version, $expected)
    {
        $cnv = new Converter();
        $actual = $cnv->versionToPrefix($version);

        $this->assertEquals($expected, $actual);
    }

    public function versionProvider()
    {
        return array(
            'genesis' => array(
                'ver' => 0,
                'pfx' => 'genesis'
            ),
            'aaa' => array(
                'ver' => 1057,
                'pfx' => 'aaa'
            ),
            'abc' => array(
                'ver' => 1091,
                'pfx' => 'abc'
            ),
            'umi' => array(
                'ver' => 21929,
                'pfx' => 'umi'
            ),
            'zzz' => array(
                'ver' => 27482,
                'pfx' => 'zzz'
            )
        );
    }

    /**
     * @dataProvider invalidVersionProvider
     */
    public function testVersionToPrefixException($version)
    {
        method_exists($this, 'expectException')
            ? $this->expectException('Exception')
            : $this->setExpectedException('Exception'); // PHPUnit 4

        $cnv = new Converter();
        $cnv->versionToPrefix($version);
    }

    public function invalidVersionProvider()
    {
        return array(
            'first chr (<1)' => array(
                'ver' => (0 << 10) + (1 << 5) + 1
            ),
            'first chr (>26)' => array(
                'ver' => (27 << 10) + (1 << 5) + 1
            ),
            'second chr (<1)' => array(
                'ver' => (1 << 10) + (0 << 5) + 1
            ),
            'second chr (>26)' => array(
                'ver' => (1 << 10) + (27 << 5) + 1
            ),
            'third chr (<1)' => array(
                'ver' => (1 << 10) + (1 << 5),
            ),
            'third chr (>26)' => array(
                'ver' => (1 << 10) + (1 << 5) + 27,
            )
        );
    }

    /**
     * @dataProvider versionProvider
     */
    public function testPrefixToVersion($expected, $prefix)
    {
        $cnv = new Converter();
        $actual = $cnv->prefixToVersion($prefix);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider invalidPrefixProvider
     */
    public function testPrefixToVersionException($prefix)
    {
        method_exists($this, 'expectException')
            ? $this->expectException('Exception')
            : $this->setExpectedException('Exception'); // PHPUnit 4

        $cnv = new Converter();
        $cnv->prefixToVersion($prefix);
    }

    public function invalidPrefixProvider()
    {
        return array(
            'ab' => array(
                'pfx' => 'ab'
            ),
            'abcd' => array(
                'pfx' => 'abcd'
            ),
            'Abc' => array(
                'pfx' => 'Abc'
            ),
            'aBc' => array(
                'pfx' => 'aBc'
            ),
            'abC' => array(
                'pfx' => 'abC'
            )
        );
    }
}
