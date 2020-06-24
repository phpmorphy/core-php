<?php

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Util\Bech32;

class Bech32Test extends TestCase
{
    /**
     * @dataProvider invalidAddressProvider
     */
    public function testDecodeException($address)
    {
        method_exists($this, 'expectException')
            ? $this->expectException('Exception')
            : $this->setExpectedException('Exception'); // PHPUnit 4

        $obj = new Bech32();
        $obj->decode($address);
    }

    public function invalidAddressProvider()
    {
        return array(
            'invalid checksum' => array(
                'adr' => 'umi1qq4kruxf'
            ),
            'short checksum' => array(
                'adr' => 'li1dgmt3'
            ),
            'empty HRP' => array(
                'adr' => '1qqpxkr44'
            ),
            'separator character' => array(
                'adr' => 'qqpxkr44'
            ),
            'mixed' => array(
                'adr' => 'Umi1qqnlgn4t'
            ),
            'character' => array(
                'adr' => "umi1\x0q4kruxd"
            ),
            'short' => array(
                'adr' => 'umi1'
            ),
            'non-zero padding' => array(
                'adr' => 'tb1qrp33g0q5c5txsp9arysrx4k6zdkfs4nce4xj0gdcccefvpysxf3pjxtptv'
            )
        );
    }
}
