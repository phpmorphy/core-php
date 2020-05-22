<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Transaction;

use GMP;
use UmiTop\UmiCore\Address\AddressInterface;
use UmiTop\UmiCore\Key\SecretKeyInterface;

interface TransactionInterface
{
    public const TRANSACTION_LENGTH = 150;

    public const GENESIS = 0;
    public const BASIC = 1;
    public const CREATE_STRUCTURE = 2;
    public const UPDATE_STRUCTURE = 3;
    public const UPDATE_PROFIT_ADDRESS = 4;
    public const UPDATE_FEE_ADDRESS = 5;
    public const CREATE_TRANSIT_ADDRESS = 6;
    public const DELETE_TRANSIT_ADDRESS = 7;


    public function toBytes(): string;

    public function getHash(): string;

    public function getVersion(): int;

    public function setVersion(int $version): TransactionInterface;

    public function getSender(): AddressInterface;

    public function setSender(AddressInterface $address): TransactionInterface;

    public function getRecipient(): AddressInterface;

    public function setRecipient(AddressInterface $address): TransactionInterface;

    public function getValue(): GMP;

    public function setValue(GMP $value): TransactionInterface;

    public function getNonce(): GMP;

    public function setNonce(GMP $value): TransactionInterface;

    public function getPrefix(): string;

    public function setPrefix(string $prefix): TransactionInterface;

    public function getName(): string;

    public function setName(string $name): TransactionInterface;

    public function getProfitPercent(): int;

    public function setProfitPercent(int $percent): TransactionInterface;

    public function getFeePercent(): int;

    public function setFeePercent(int $percent): TransactionInterface;

    public function getSignature(): string;

    public function setSignature(string $signature): TransactionInterface;

    public function sign(SecretKeyInterface $secretKey): TransactionInterface;

    public function verify(): bool;
}
