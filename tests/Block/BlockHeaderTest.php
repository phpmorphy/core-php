<?php

declare(strict_types=1);

namespace Tests\Block;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Block\BlockHeader;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class BlockHeaderTest extends TestCase
{
    public function testConstructor(): void
    {
        $obj = new BlockHeader();
        $this->assertEquals(str_repeat("\x0", BlockHeader::LENGTH), $obj->toBytes());
    }

    public function testConstructorException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        new BlockHeader('');
    }

    public function testGetters(): void
    {
        $bytes = base64_decode(
            'Aaqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqu7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7vMzMzM3d3u7u7' .
            'u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7v////////////////////////////////////////////////////////////' .
            '////////////////////////8='
        );

        $obj = new BlockHeader($bytes);

        $this->assertEquals($bytes, $obj->toBytes());
        $this->assertEquals(0x01, $obj->getVersion());
        $this->assertEquals(str_repeat("\xaa", 32), $obj->getPreviousBlockHash());
        $this->assertEquals(0xcccccccc, $obj->getTimestamp());
        $this->assertEquals(0xdddd, $obj->getTransactionCount());
        $this->assertEquals(str_repeat("\xee", 32), $obj->getPublicKey()->toBytes());
        $this->assertEquals(str_repeat("\xff", 64), $obj->getSignature());
        $this->assertFalse($obj->verify());
    }

    public function testTransactionCount(): void
    {
        $obj = new BlockHeader();
        $expected = 0xffff;
        $actual = $obj->setTransactionCount($expected)->getTransactionCount();
        $this->assertEquals($expected, $actual);
    }

    public function testTransactionCountException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $obj = new BlockHeader();
        $obj->setTransactionCount(-1);
    }
}
