<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Transaction;

use GMP;
use Exception;
use UmiTop\UmiCore\Address\Address;
use UmiTop\UmiCore\Address\AddressInterface;
use UmiTop\UmiCore\Key\SecretKeyInterface;
use UmiTop\UmiCore\Util\Converter;

class Transaction implements TransactionInterface
{
    private const VERSION_OFFSET = 0;
    private const SENDER_OFFSET = 1;
    private const RECIPIENT_OFFSET = 35;
    private const VALUE_OFFSET = 69;
    private const VALUE_LENGTH = 8;
    private const NONCE_OFFSET = 77;
    private const NONCE_LENGTH = 8;
    private const PREFIX_OFFSET = 35;
    private const PREFIX_LENGTH = 2;
    private const PROFIT_OFFSET = 37;
    private const PROFIT_LENGTH = 2;
    private const FEE_OFFSET = 39;
    private const FEE_LENGTH = 2;
    private const NAME_OFFSET = 41;
    private const NAME_LENGTH = 36;

    private const UNSIGNED_OFFSET = 0;
    private const UNSIGNED_LENGTH = 85;
    private const SIGNATURE_OFFSET = 85;
    private const SIGNATURE_LENGTH = 64;

    private string $bytes;

    public function __construct(string $bytes = null)
    {
        if ($bytes === null) {
            $this->bytes = str_repeat("\x0", self::TRANSACTION_LENGTH);
            $this->setVersion(self::BASIC);
        } elseif (strlen($bytes) !== self::TRANSACTION_LENGTH) {
            throw new Exception(
                sprintf('transaction size should be %d bytes', self::TRANSACTION_LENGTH)
            );
        } else {
            $this->bytes = $bytes;
        }
    }

    public function toBytes(): string
    {
        return $this->bytes;
    }

    public function getHash(): string
    {
        return hash('sha256', $this->bytes, true);
    }

    public function getVersion(): int
    {
        return ord($this->bytes[self::VERSION_OFFSET]);
    }

    public function setVersion(int $version): self
    {
        $this->bytes[self::VERSION_OFFSET] = chr($version);

        return $this;
    }

    public function getSender(): AddressInterface
    {
        return new Address(
            substr($this->bytes, self::SENDER_OFFSET, Address::ADDRESS_LENGTH)
        );
    }

    public function setSender(AddressInterface $address): self
    {
        $this->bytes = substr_replace(
            $this->bytes,
            $address->toBytes(),
            self::SENDER_OFFSET,
            Address::ADDRESS_LENGTH
        );

        return $this;
    }

    public function getRecipient(): AddressInterface
    {
        return new Address(
            substr($this->bytes, self::RECIPIENT_OFFSET, Address::ADDRESS_LENGTH)
        );
    }

    public function setRecipient(AddressInterface $address): self
    {
        $this->bytes = substr_replace(
            $this->bytes,
            $address->toBytes(),
            self::RECIPIENT_OFFSET,
            Address::ADDRESS_LENGTH
        );

        return $this;
    }

    public function getValue(): GMP
    {
        return gmp_import(
            substr($this->bytes, self::VALUE_OFFSET, self::VALUE_LENGTH),
            self::VALUE_LENGTH,
            GMP_MSW_FIRST | GMP_BIG_ENDIAN
        );
    }

    public function setValue(GMP $value): TransactionInterface
    {
        $this->bytes = substr_replace(
            $this->bytes,
            gmp_export($value, self::VALUE_LENGTH, GMP_MSW_FIRST | GMP_BIG_ENDIAN),
            self::VALUE_OFFSET,
            self::VALUE_LENGTH
        );

        return $this;
    }

    public function getNonce(): GMP
    {
        return gmp_import(
            substr($this->bytes, self::NONCE_OFFSET, self::NONCE_LENGTH),
            self::NONCE_LENGTH,
            GMP_MSW_FIRST | GMP_BIG_ENDIAN
        );
    }

    public function setNonce(GMP $value): TransactionInterface
    {
        $this->bytes = substr_replace(
            $this->bytes,
            gmp_export($value, self::NONCE_LENGTH, GMP_MSW_FIRST | GMP_BIG_ENDIAN),
            self::NONCE_OFFSET,
            self::NONCE_LENGTH
        );

        return $this;
    }

    public function getPrefix(): string
    {
        return Converter::versionToPrefix(
            intval(unpack('n', substr($this->bytes, self::PREFIX_OFFSET, self::PREFIX_LENGTH))[1])
        );
    }

    public function setPrefix(string $prefix): self
    {
        $this->bytes = substr_replace(
            $this->bytes,
            pack('n', Converter::prefixToVersion($prefix)), // unsigned short, big endian
            self::PREFIX_OFFSET,
            self::PREFIX_LENGTH
        );

        return $this;
    }

    public function getName(): string
    {
        return substr($this->bytes, self::NAME_OFFSET + 1, ord($this->bytes[self::NAME_OFFSET]));
    }

    public function setName(string $name): self
    {
        if (strlen($name) >= self::NAME_LENGTH) {
            throw new Exception('name too long');
        }

        $this->bytes[self::NAME_OFFSET] = chr(strlen($name));
        $this->bytes = substr_replace($this->bytes, $name, (self::NAME_OFFSET + 1), strlen($name));

        return $this;
    }

    public function getProfitPercent(): int
    {
        return intval(unpack('n', substr($this->bytes, self::PROFIT_OFFSET, self::PROFIT_LENGTH))[1]);
    }

    public function setProfitPercent(int $percent): self
    {
        $this->bytes = substr_replace(
            $this->bytes,
            pack('n', $percent), // unsigned short, big endian
            self::PROFIT_OFFSET,
            self::PROFIT_LENGTH
        );

        return $this;
    }

    public function getFeePercent(): int
    {
        return intval(unpack('n', substr($this->bytes, self::FEE_OFFSET, self::FEE_LENGTH))[1]);
    }

    public function setFeePercent(int $percent): self
    {
        $this->bytes = substr_replace(
            $this->bytes,
            pack('n', $percent), // unsigned short, big endian
            self::FEE_OFFSET,
            self::FEE_LENGTH
        );

        return $this;
    }

    public function getSignature(): string
    {
        return substr($this->bytes, self::SIGNATURE_OFFSET, self::SIGNATURE_LENGTH);
    }

    public function setSignature(string $signature): self
    {
        $this->bytes = substr_replace(
            $this->bytes,
            $signature,
            self::SIGNATURE_OFFSET,
            strlen($signature)
        );

        return $this;
    }

    public function sign(SecretKeyInterface $secretKey): self
    {
        return $this->setSignature(
            $secretKey->sign(
                substr($this->bytes, self::UNSIGNED_OFFSET, self::UNSIGNED_LENGTH)
            )
        );
    }

    public function verify(): bool
    {
        return $this->getSender()
            ->getPublicKey()
            ->verifySignature(
                substr($this->bytes, self::UNSIGNED_OFFSET, self::UNSIGNED_LENGTH),
                $this->getSignature()
            );
    }

    public function __toString(): string
    {
        return $this->bytes;
    }

    /**
     * @return array{hex: string}
     */
    public function __debugInfo(): array
    {
        return [
            'hex' => bin2hex($this->bytes)
        ];
    }
}
