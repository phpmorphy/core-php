<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key;

interface PublicKeyInterface extends KeyInterface
{
    public function verifySignature(string $message, string $signature): bool;
}
