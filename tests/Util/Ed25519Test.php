<?php

declare(strict_types=1);

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use UmiTop\UmiCore\Util\Ed25519\Ed25519;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Ed25519Test extends TestCase
{
    public function testSecretKeyFromSeed(): void
    {
        $seed = base64_decode('xfg17XxfdmQGBaG81VhujlaXeBXohAA+PUGyAm7K6xc=');
        $expected = base64_decode(
            'xfg17XxfdmQGBaG81VhujlaXeBXohAA+PUGyAm7K6xen5Ga88vyJMHigVZqh/qInXZv3QI/4f8njshuyFH7OwA=='
        );

        $obj = new Ed25519();
        $actual = $obj->secretKeyFromSeed($seed);

        $this->assertEquals($expected, $actual);
    }

    public function testSign(): void
    {
        $secKey = base64_decode(
            'SABAmPEL+wRkg0s/ksFVNkNM5lyW7Od0Es0YL9AK1CEjPSbgoTSBCX0RTMtpvZK9oF0yMonrE2wvpQZqDojy4g=='
        );
        $message = base64_decode('YqEKfwykJak/lNoINH3k/D5gPXWF/r2jparyuv91SajANI6aeRumOw==');
        $expected = base64_decode(
            'BNSJBhLS6OjF8oHncE4fKN6lePFE99e56Z6/h22h9Kye0NRZvJ/AkpQfLrl3ZrH3cbMBHiGyA1SkhGQXWlGYDw=='
        );

        $obj = new Ed25519();
        $actual = $obj->sign($message, $secKey);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider signatureProvider
     */
    public function testVerify(string $key, string $message, string $signature, bool $expected): void
    {
        $obj = new Ed25519();
        $actual = $obj->verify($signature, $message, $key);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array<string, string|bool>>
     */
    public function signatureProvider(): array
    {
        return [
            'valid' => [
                'key' => base64_decode('rWFVo/97AI3lJIFGyp9SZt03SU5H5KP+iPp+qsc9pdE='),
                'msg' => base64_decode('8NotdiZroXjaKRjU1zI3jYp1lit3wUUn4HL0PsR6YiE6rykxmkfOgA=='),
                'sig' => base64_decode(
                    '3Bhcv0q6LKjOkL0fkjMyilc0Ul7MzHOh23DTZ6jpeUthlksImbSlnH2bUup4WDa6eEYXMMW3IWCLGJnCFjyjBw=='
                ),
                'exp' => true
            ],
            'invalid' => [
                'key' => base64_decode('9LqLUmP6mOIb/D4JS6tzw190YOOmQ+5HrjwQs9tsbgs='),
                'msg' => base64_decode('1/YEhyIBT8Xxn/deVuk9UwSHWtHJgVOlrFMI/gROaoVPJfklDlWVVg=='),
                'sig' => base64_decode(
                    'bwa7kuXNWTZJJUXUJyXSR08wlagl1XxA40DFnTI1oYsJGp/0vc50gVeTr6ma9Ozo9kNrnS/SGx6mGqUOZA1lDA=='
                ),
                'exp' => false
            ]
        ];
    }
}
