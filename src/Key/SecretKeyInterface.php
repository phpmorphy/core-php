<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key;

interface SecretKeyInterface extends KeyInterface
{
    public function getPublicKey(): PublicKeyInterface;

    public function sign(string $message): string;
}
