<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key;

interface KeyInterface
{
    public const VERSION_BASIC = 0;
    public const VERSION_HD = 1;

    public function toBytes(): string;
}
