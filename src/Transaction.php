<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


/**
 * Class Transaction
 * @package UmiTop\UmiCore
 */
class Transaction implements TransactionInterface
{
    /** @var \GMP */
    private $nonce;

    /** @var AddressInterface */
    private $recipient;

    /** @var AddressInterface */
    private $sender;

    /** @var string */
    private $signature;

    /** @var int */
    private $type;

    /** @var \GMP */
    private $value;

    /**
     * Transaction constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->type = self::TYPE_BASE;
        $this->nonce = gmp_import(random_bytes(8));
    }

    /**
     * @param string $hex
     * @return TransactionInterface
     * @throws \Exception
     */
    public static function fromHex(string $hex): TransactionInterface
    {
        $raw = hex2bin($hex);

        $tx = new Transaction();
        $tx->type = ord($raw[0]);
        $tx->sender = Address::fromRaw(substr($raw, 1, 34));
        $tx->recipient = Address::fromRaw(substr($raw, 35, 34));
        $tx->value = gmp_import(substr($raw, 69, 8), 8, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $tx->nonce = gmp_import(substr($raw, 77, 8), 8, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $tx->signature = substr($raw, 85, 64);

        return $tx;
    }

    /**
     * @return \GMP
     */
    public function getNonce(): \GMP
    {
        return $this->nonce;
    }

    /**
     * @return AddressInterface
     */
    public function getRecipient(): AddressInterface
    {
        return $this->recipient ?? new Address();
    }

    /**
     * @return AddressInterface
     */
    public function getSender(): AddressInterface
    {
        return $this->sender ?? new Address();
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return str_pad($this->signature ?? "", 64);
    }

    /**
     * @return \GMP
     */
    public function getValue(): \GMP
    {
        return $this->value ?? gmp_init(0);
    }

    /**
     * @param string $mnemonic
     * @return TransactionInterface
     * @throws \Exception
     */
    public function signWithMnemonic(string $mnemonic): TransactionInterface
    {
        return $this->withSignature(
            sodium_crypto_sign_detached(
                substr($this->toRaw(), 0, 85),
                Mnemonic::toSecretKey($mnemonic)
            )
        );
    }

    /**
     * @return string
     */
    public function toHex(): string
    {
        return bin2hex($this->toRaw());
    }

    /**
     * @return string
     */
    public function toRaw(): string
    {
        return chr($this->type)
            . $this->getSender()->toRaw()
            . $this->getRecipient()->toRaw()
            . gmp_export($this->getValue(), 8, GMP_MSW_FIRST | GMP_BIG_ENDIAN)
            . gmp_export($this->getNonce(), 8, GMP_MSW_FIRST | GMP_BIG_ENDIAN)
            . $this->getSignature();
    }

    /**
     * @return bool
     */
    public function verify(): bool
    {
        return sodium_crypto_sign_verify_detached(
            $this->getSignature(),
            substr($this->toRaw(), 0, 85),
            $this->getSender()->getPublicKey()
        );
    }

    /**
     * @param \GMP $nonce
     * @return TransactionInterface
     */
    public function withNonce(\GMP $nonce): TransactionInterface
    {
        $new = clone $this;
        $new->nonce = $nonce;
        return $new;
    }

    /**
     * @param AddressInterface $recipient
     * @return TransactionInterface
     */
    public function withRecipient(AddressInterface $recipient): TransactionInterface
    {
        $new = clone $this;
        $new->recipient = $recipient;
        return $new;
    }

    /**
     * @param AddressInterface $sender
     * @return TransactionInterface
     */
    public function withSender(AddressInterface $sender): TransactionInterface
    {
        $new = clone $this;
        $new->sender = $sender;
        return $new;
    }

    /**
     * @param string $signature
     * @return TransactionInterface
     */
    public function withSignature(string $signature): TransactionInterface
    {
        $new = clone $this;
        $new->signature = $signature;
        return $new;
    }

    /**
     * @param int $type
     * @return TransactionInterface
     */
    public function withType(int $type): TransactionInterface
    {
        $new = clone $this;
        $new->type = $type;
        return $new;
    }

    /**
     * @param \GMP $value
     * @return TransactionInterface
     */
    public function withValue(\GMP $value): TransactionInterface
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }
}
