<?php

declare(strict_types=1);


namespace UmiTop\UmiCore;


/**
 * Interface TransactionInterface
 * @package UmiTop\UmiCore
 */
interface TransactionInterface
{
    public const TYPE_GENESIS = 0x0;
    public const TYPE_BASE = 0x1;

    /**
     * @param string $hex
     * @return TransactionInterface
     */
    public static function fromHex(string $hex): TransactionInterface;

    /**
     * @return \GMP
     */
    public function getNonce(): \GMP;

    /**
     * @return AddressInterface
     */
    public function getRecipient(): AddressInterface;

    /**
     * @return AddressInterface
     */
    public function getSender(): AddressInterface;

    /**
     * @return string
     */
    public function getSignature(): string;

    /**
     * @return \GMP
     */
    public function getValue(): \GMP;

    /**
     * @param string $mnemonic
     * @return TransactionInterface
     */
    public function signWithMnemonic(string $mnemonic): TransactionInterface;

    /**
     * @return string
     */
    public function toHex(): string;

    /**
     * @return string
     */
    public function toRaw(): string;

    /**
     * @return bool
     */
    public function verify(): bool;

    /**
     * @param \GMP $nonce
     * @return TransactionInterface
     */
    public function withNonce(\GMP $nonce): TransactionInterface;

    /**
     * @param AddressInterface $recipient
     * @return TransactionInterface
     */
    public function withRecipient(AddressInterface $recipient): TransactionInterface;

    /**
     * @param AddressInterface $sender
     * @return TransactionInterface
     */
    public function withSender(AddressInterface $sender): TransactionInterface;

    /**
     * @param string $signature
     * @return TransactionInterface
     */
    public function withSignature(string $signature): TransactionInterface;

    /**
     * @param int $type
     * @return TransactionInterface
     */
    public function withType(int $type): TransactionInterface;

    /**
     * @param \GMP $value
     * @return TransactionInterface
     */
    public function withValue(\GMP $value): TransactionInterface;
}
