<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key;

abstract class AbstractKey implements KeyInterface
{
    protected string $bytes;

    public function __construct(string $bytes)
    {
        $this->bytes = $bytes;
    }

    public function toBytes(): string
    {
        return $this->bytes;
    }

    public function __toString(): string
    {
        return bin2hex($this->bytes);
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
