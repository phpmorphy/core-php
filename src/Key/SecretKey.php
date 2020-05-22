<?php

declare(strict_types=1);

namespace UmiTop\UmiCore\Key;

use UmiTop\UmiCore\Key\Ed25519\SecretKey as SecretKeyEd25519;

class SecretKey extends SecretKeyEd25519 implements SecretKeyInterface
{
}
