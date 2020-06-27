<?php

declare(strict_types=1);

namespace Tests\Block;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Block\Block;
use UmiTop\UmiCore\Block\BlockHeader;
use UmiTop\UmiCore\Key\PublicKey;
use UmiTop\UmiCore\Key\SecretKey;
use UmiTop\UmiCore\Transaction\Transaction;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class BlockTest extends TestCase
{
    public function testConstructor(): void
    {
        $expected = str_repeat("\x0", BlockHeader::LENGTH);
        $obj = new Block();
        $actual = $obj->toBytes();
        $this->assertEquals($expected, $actual);
    }

    public function testConstructorException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        new Block('');
    }

    public function testConstructorException2(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        new Block(str_repeat("\x0", BlockHeader::LENGTH + 1));
    }


    public function testGetHash(): void
    {
        $expected = hash('sha256', str_repeat("\x0", BlockHeader::LENGTH), true);
        $obj = new Block();
        $actual = $obj->getHash();
        $this->assertEquals($expected, $actual);
    }

    public function testGetHeader(): void
    {
        $expected = str_repeat("\x0", BlockHeader::LENGTH);
        $obj = new Block();
        $actual = $obj->getHeader()->toBytes();
        $this->assertEquals($expected, $actual);
    }

    public function testVersion(): void
    {
        $obj = new Block();
        $expected = 1;
        $actual = $obj->setVersion($expected)->getVersion();
        $this->assertEquals($expected, $actual);
    }

    public function testPreviousBlockHash(): void
    {
        $obj = new Block();
        $expected = str_repeat("\xab", 32);
        $actual = $obj->setPreviousBlockHash($expected)->getPreviousBlockHash();
        $this->assertEquals($expected, $actual);
    }

    public function testMerkleRootHash(): void
    {
        $obj = new Block();
        $expected = str_repeat("\xcd", 32);
        $actual = $obj->setMerkleRootHash($expected)->getMerkleRootHash();
        $this->assertEquals($expected, $actual);
    }

    public function testTimestamp(): void
    {
        $obj = new Block();
        $expected = 0x12345678;
        $actual = $obj->setTimestamp($expected)->getTimestamp();
        $this->assertEquals($expected, $actual);
    }

    public function testPublicKey(): void
    {
        $obj = new Block();
        $expected = new PublicKey(str_repeat("\x12", 32));
        $actual = $obj->setPublicKey($expected)->getPublicKey();
        $this->assertEquals($expected->toBytes(), $actual->toBytes());
    }

    public function testSignature(): void
    {
        $obj = new Block();
        $expected = str_repeat("\xff", 64);
        $actual = $obj->setSignature($expected)->getSignature();
        $this->assertEquals($expected, $actual);
    }

    public function testTransaction(): void
    {
        $obj = new Block();

        $expected = str_repeat("\xff", Transaction::LENGTH);
        $actual = $obj->appendTransaction(new Transaction($expected))->getTransaction(0)->toBytes();

        $this->assertEquals($expected, $actual);
        $this->assertEquals(1, $obj->getTransactionCount());
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

    public function testMerkleException(): void
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Exception');
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('Exception'); // PHPUnit 4
        }

        $obj = new Block();
        $obj->calculateMerkleRoot();
    }

    /**
     * @dataProvider merkleProvider
     */
    public function testMerkle(string $merkleRoot, int $txCount): void
    {
        $obj = new Block();

        for ($i = 0; $i < $txCount; $i++) {
            $bytes = str_repeat(chr($i), Transaction::LENGTH);
            $trx = new Transaction($bytes);
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
