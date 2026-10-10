<?php

namespace Diskerror\Library\Tests;

use PHPUnit\Framework\TestCase;

use function Diskerror\Library\Json\decode;
use function Diskerror\Library\Json\encode;
use function Diskerror\Library\Json\validate;

class JsonTest extends TestCase
{
    public function testEncodeDecodeRoundTrip(): void
    {
        $data = ['a' => 1, 'b' => ['c' => 2]];
        $this->assertSame($data, decode(encode($data)));
    }

    public function testEncodeThrowsOnInvalidData(): void
    {
        $this->expectException(\JsonException::class);
        encode(fopen('php://memory', 'r'));
    }

    public function testDecodeThrowsOnInvalidJson(): void
    {
        $this->expectException(\JsonException::class);
        decode('{not valid json');
    }

    public function testValidateTrueForWellFormedJson(): void
    {
        $this->assertTrue(validate('{"a":1}'));
        $this->assertTrue(validate('[1,2,3]'));
    }

    public function testValidateFalseForMalformedJson(): void
    {
        $this->assertFalse(validate('{not valid json'));
        $this->assertFalse(validate(''));
    }
}
