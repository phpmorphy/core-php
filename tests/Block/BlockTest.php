<?php

declare(strict_types=1);

namespace Tests\Block;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Block\Block;
use UmiTop\UmiCore\Block\BlockHeader;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class BlockTest extends TestCase
{
    public function testFromBytes(): void
    {
        $expected = 'AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABfCTUPAAEAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAA=';
        $expected = base64_decode($expected);
        $actual = Block::fromBytes($expected)->getBytes();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider invalidBlockProvider
     */
    public function testSetBytesException(string $bytes): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $obj = new Block();
        $obj->setBytes($bytes);
    }

    /**
     * @return array<string, array<string, int|string>>
     */
    public function invalidBlockProvider(): array
    {
        return [
            'too short' => [
                'bytes' => str_repeat("\x0", BlockHeader::LENGTH - 1)
            ],
            'too long' => [
                'bytes' => str_repeat("\x0", BlockHeader::LENGTH + 1)
            ]
        ];
    }

    public function testHeader(): void
    {
        $hdr = new BlockHeader();
        $hdr->setBytes(str_repeat("\x01", BlockHeader::LENGTH));
        $expected = $hdr->getBytes();

        $obj = new Block();
        $actual = $obj->setHeader($hdr)->getHeader()->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testTransaction(): void
    {
        $trx = new Transaction();
        $trx->setBytes(str_repeat("\x01", Transaction::LENGTH));
        $expected = $trx->getBytes();

        $obj = new Block();
        $actual = $obj->appendTransaction($trx)->getTransaction(0)->getBytes();

        $this->assertEquals($expected, $actual);
    }

    public function testTransactionCount(): void
    {
        $obj = new Block();
        $actual = $obj->appendTransaction(new Transaction())->getHeader()->getTransactionCount();

        $this->assertEquals(1, $actual);
    }

    public function testGetTransactionException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $obj = new Block();
        $obj->getTransaction(0);
    }

    public function testSign(): void
    {
        $key = base64_decode(
            'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA7aie8zrakLWKjqNAqbw1zZTIVdx3iQ6Y6wEihi1naKQ=='
        );
        $sec = new SecretKey($key);
        $obj = new Block();
        $obj->sign($sec);
        $this->assertTrue($obj->verify());
    }

    /**
     * @dataProvider merkleProvider
     */
    public function testMerkle(string $merkleRoot, int $txCount): void
    {
        $obj = new Block();
        $trx = new Transaction();

        for ($i = 0; $i < $txCount; $i++) {
            $bytes = str_repeat(chr($i), Transaction::LENGTH);
            $trx->setBytes($bytes);
            $obj->appendTransaction($trx);
        }

        $expected = base64_decode($merkleRoot);
        $this->assertEquals($expected, $obj->calculateMerkleRoot());
    }

    /**
     * @return array<int|string, array<string, string|int>>
     */
    public function merkleProvider(): array
    {
        return [
            '1' => [
                'merkle' => 'HYNRi4l7FOKUOZDv9lWDgkbMAgenyVpfPfzMLjlfi78=',
                'count' => 1
            ],
            '2' => [
                'merkle' => '5nxQuCEhLBP+XztSaepJ28qcgp/7ETPADXrqX8uZ38U=',
                'count' => 2
            ],
            '3' => [
                'merkle' => '304f5WJnRpBWJc8OM/GXElcq+9r4WzZB2GU3tJXrZZE=',
                'count' => 3
            ],
            '4' => [
                'merkle' => '0k7lgourOBJjkhrHeGVELXZbzsOaiMnnIApptve5oFc=',
                'count' => 4
            ],
            '5' => [
                'merkle' => 'k24Xs6YvuR3cyoqKO+yBWeyaKbguywzkFTb7gG5mdGM=',
                'count' => 5
            ],
            '6' => [
                'merkle' => 'JZRKgpSQd5p+LSJDiGzuMQ4mL9yYtBWkbpVqdUbAdk8=',
                'count' => 6
            ],
            '7' => [
                'merkle' => 'gYekGUsQ3UdR171nY8OV8SLAf9dgNIe+yIBPErAwYnw=',
                'count' => 7
            ],
            '8' => [
                'merkle' => 'Zn+VUCmI+ir8qmHlS+zaz9glnuJg2K3ZstWtNXzxxE0=',
                'count' => 8
            ]
        ];
    }
}
