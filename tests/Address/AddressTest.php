<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Tests\Address;

use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Address\AddressInterface;

class AddressTest extends TestCase
{
    public function testCanBeCreatedEmptyAddress(): void
    {
        $this->assertInstanceOf(AddressInterface::class, new Address());
    }

    public function testEmptyAddressMustCorrectLength(): void
    {
        $this->assertEquals(
            Address::ADDRESS_LENGTH,
            strlen(
                (new Address())->toBytes()
            )
        );
    }

    public function testEmptyAddressMustBeWithUmiPrefix(): void
    {
        $adr = new Address();

        $this->assertEquals('umi', $adr->getPrefix());
        $this->assertEquals(Address::VERSION_UMI_BASIC, $adr->getVersion());
    }

    public function testCanBeCreatedFromValidString(): void
    {
        $this->assertInstanceOf(
            AddressInterface::class,
            new Address(
                str_repeat("\x0", Address::ADDRESS_LENGTH)
            )
        );
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(Exception::class);
        new Address(
            str_repeat("\x0", Address::ADDRESS_LENGTH + 1)
        );
    }

    /**
     * @dataProvider validPrefixesProvider
     */
    public function testValidPrefixCanBeSet(string $prefix): void
    {
        $adr = new Address();
        $adr->setPrefix($prefix);

        $this->assertEquals($prefix, $adr->getPrefix());
    }

    /**
     * @return Generator<array<int, string>>
     */
    public function validPrefixesProvider(): Generator
    {
        for ($a = ord('a'); $a <= ord('z'); $a++) {
            for ($b = ord('a'); $b <= ord('z'); $b++) {
                for ($c = ord('a'); $c <= ord('z'); $c++) {
                    yield [chr($a) . chr($b) . chr($c)];
                    return;
                }
            }
        }
    }

    /**
     * @dataProvider invalidPrefixesProvider
     */
    public function testInvalidPrefixCannotBeSet(string $prefix): void
    {
        $this->expectException(Exception::class);

        (new Address())->setPrefix($prefix);
    }

    /**
     * @return Generator<array<int, string>>
     */
    public function invalidPrefixesProvider(): Generator
    {
        $i = 0;
        do {
            $s = random_bytes(3);
            if (ord($s[0]) >= ord('a') && ord($s[0]) <= ord('z')) {
                continue;
            }
            if (ord($s[1]) >= ord('a') && ord($s[1]) <= ord('z')) {
                continue;
            }
            if (ord($s[2]) >= ord('a') && ord($s[2]) <= ord('z')) {
                continue;
            }
            $i++;
            yield [$s];
            return;
        } while ($i < 100000);
    }

    /**
     * @dataProvider validBech32Provider
     */
    public function testMustCreateFromValidBech32(string $bech32, string $prefix, string $pubKey): void
    {
        $adr = (new Address())->fromBech32($bech32);

        $this->assertEquals($prefix, $adr->getPrefix());
        $this->assertEquals($pubKey, $adr->getPublicKey()->toBytes());
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function validBech32Provider(): array
    {
        return [
            [
                'bech32' => 'umi1sxg55ql9rqsj6cqrsj0dd9l9xs8l94x3ynnaqx7x7p7739ddrllsydjasp',
                'prefix' => 'umi',
                'pubKey' => (string)hex2bin('81914a03e518212d6003849ed697e5340ff2d4d124e7d01bc6f07de895ad1fff')
            ],
            [
                'bech32' => 'aaa1z3ucy37djrhp2wqughtedcm7fv0yxrarrttzeqpzs4xpensq4utsuymc4y',
                'prefix' => 'aaa',
                'pubKey' => (string)hex2bin('14798247cd90ee15381c45d796e37e4b1e430fa31ad62c8022854c1cce00af17')
            ],
            [
                'bech32' => 'zzz1f24hf00u09dp2yxe6x3qutv6gaqkj3g4243qcshfqudtxl4uft9s06qt3y',
                'prefix' => 'zzz',
                'pubKey' => (string)hex2bin('4aab74bdfc795a1510d9d1a20e2d9a474169451555620c42e9071ab37ebc4acb')
            ],
            [
                'bech32' => 'genesis17gn68lpuymvflhn97f86pru7ex4dky7w9z4w33q9tsffqcwzwlvq6hhf6w',
                'prefix' => 'genesis',
                'pubKey' => (string)hex2bin('f227a3fc3c26d89fde65f24fa08f9ec9aadb13ce28aae8c4055c129061c277d8')
            ],
        ];
    }

    /**
     * @dataProvider invalidBech32Provider
     */
    public function testMustNotCreateFromInvalidBech32(string $bech32): void
    {
        $this->expectException(Exception::class);

        $adr = (new Address())->fromBech32($bech32);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function invalidBech32Provider(): array
    {
        return [
            'too short 2' => ['umi1f24sls083w'],
            'too long 34' => ['umi1f24jssl6x65yvx68sxvyqkzehhk0cyk5kh0z8gguqmk9fvv8gmgjvksztra4v'],
            'too long 35' => ['umi19ppl5d4ggcd50qvcgpv9n00vlsfdfdw7yws3cphv2jccw3k3yedp9l7vjsmk5y'],
            'invalid checksum' => ['umi1ejdtp93v7n5a555gjgft8llwfjlvxqf8j2lcenc05nzqx80rassscrqm4c'],
            'short prefix 1' => ['a1yjua05u04qwrn0v3mgwu0dy9dzzy4je7cdcg0dwt97rxcsq865kqdrgha7'],
            'long prefix 4' => ['abcd1gexh3fgy4zdz4zuw7wudm7ecuz22lmwa6dnzyryqftvpw3y4mzjs9kmrqv'],
            'invalid prefix' => ['Umi17udjg8srp3e5huc0gs9teqss5vfy3qkvym05rz3rqm0gk48m5zdql79jwj'],
        ];
    }

    /**
     * @dataProvider validStringProvider
     */
    public function testMustCreateValidBech32FromValidString(string $bytes, string $bech32): void
    {
        $this->assertEquals(
            $bech32,
            (new Address($bytes))->toBech32()
        );
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function validStringProvider(): array
    {
        return [
            [
                'bytes' => (string)hex2bin('55a954b1e8a606f8852f410284a779547c36f084e737d7dbca89a862d27304984941'),
                'bech32' => 'umi12jc73fsxlzzj7sgzsjnhj4ruxmcgfeeh6ldu4zdgvtf8xpycf9qsagm0qt'
            ],
            [
                'bytes' => (string)hex2bin('0000d7053ced226800d779a6e0e6902f281e1913c72d579ec143f8500e32b3e08cb3'),
                'bech32' => 'genesis16uznemfzdqqdw7dxurnfqtegrcv383ed270vzslc2q8r9vlq3jesj4q8rt'
            ],
        ];
    }
}
