<?php

declare(strict_types=1);

namespace Tests\Block;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Block\BlockHeader;
use UmiTop\UmiCore\Key\PublicKey;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class BlockHeaderTest extends TestCase
{
    public function testFromBytes(): void
    {
        $expected = str_repeat("\x01", BlockHeader::LENGTH);
        $actual = BlockHeader::fromBytes($expected)->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testSetBytesException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $obj = new BlockHeader();
        $obj->setBytes('');
    }

    public function testGetters(): void
    {
        $base64 = 'Aaqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqu7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7vMzMzM3d3u7u7'
            . 'u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7v////////////////////////////////////////////////////////////'
            . '////////////////////////8=';
        $bytes = base64_decode($base64);

        $obj = new BlockHeader();
        $obj->setBytes($bytes);

        $this->assertEquals($bytes, $obj->getBytes());
        $this->assertEquals(0x01, $obj->getVersion());
        $this->assertEquals(str_repeat("\xaa", 32), $obj->getPreviousBlockHash());
        $this->assertEquals(0xcccccccc, $obj->getTimestamp());
        $this->assertEquals(0xdddd, $obj->getTransactionCount());
        $this->assertEquals(str_repeat("\xee", 32), $obj->getPublicKey()->getBytes());
        $this->assertEquals(str_repeat("\xff", 64), $obj->getSignature());
        $this->assertFalse($obj->verify());
    }

    public function testTransactionCount(): void
    {
        $expected = 0xffff;

        $obj = new BlockHeader();
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

    public function testGetHash(): void
    {
        $bytes = str_repeat("\x01", BlockHeader::LENGTH);
        $expected = hash('sha256', $bytes, true);

        $obj = new BlockHeader();
        $actual = $obj->setBytes($bytes)->getHash();

        $this->assertEquals($expected, $actual);
    }

    public function testVersion(): void
    {
        $expected = 0;

        $obj = new BlockHeader();
        $actual = $obj->setVersion($expected)->getVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testPreviousBlockHash(): void
    {
        $expected = str_repeat("\xab", 32);

        $obj = new BlockHeader();
        $actual = $obj->setPreviousBlockHash($expected)->getPreviousBlockHash();

        $this->assertEquals($expected, $actual);
    }

    public function testMerkleRootHash(): void
    {
        $expected = str_repeat("\xcd", 32);

        $obj = new BlockHeader();
        $actual = $obj->setMerkleRootHash($expected)->getMerkleRootHash();

        $this->assertEquals($expected, $actual);
    }

    public function testTimestamp(): void
    {
        $expected = 0x12345678;

        $obj = new BlockHeader();
        $actual = $obj->setTimestamp($expected)->getTimestamp();

        $this->assertEquals($expected, $actual);
    }

    public function testPublicKey(): void
    {
        $pubKey = new PublicKey(str_repeat("\x12", 32));
        $expected = $pubKey->getBytes();

        $obj = new BlockHeader();
        $actual = $obj->setPublicKey($pubKey)->getPublicKey()->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testSignature(): void
    {
        $expected = str_repeat("\xff", 64);

        $obj = new BlockHeader();
        $actual = $obj->setSignature($expected)->getSignature();

        $this->assertEquals($expected, $actual);
    }
}
