<?php

namespace Diskerror\Library\Tests;

use PHPUnit\Framework\TestCase;

use function Diskerror\Library\escapeshellarg;

class EscapeshellargTest extends TestCase
{
    public function testEscapesSpaces(): void
    {
        $this->assertSame('a\\ b', escapeshellarg('a b'));
    }

    public function testEscapesShellMetacharacters(): void
    {
        $this->assertSame("\\'", escapeshellarg("'"));
        $this->assertSame('\\"', escapeshellarg('"'));
        $this->assertSame('\\(x\\)', escapeshellarg('(x)'));
        $this->assertSame('\\*', escapeshellarg('*'));
        $this->assertSame('\\\\', escapeshellarg('\\'));
    }

    public function testPlainStringIsUnchanged(): void
    {
        $this->assertSame('plainstring123', escapeshellarg('plainstring123'));
    }

    public function testArrayInputIsMappedElementWise(): void
    {
        $this->assertSame(['a\\ b', 'c\\*d'], escapeshellarg(['a b', 'c*d']));
    }
}
