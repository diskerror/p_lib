<?php

namespace Diskerror\Library\Tests;

use Diskerror\Library\PorterStemmer;
use PHPUnit\Framework\TestCase;

class PorterStemmerTest extends TestCase
{
    /**
     * @dataProvider wordProvider
     */
    public function testStem(string $word, string $expectedStem): void
    {
        $this->assertSame($expectedStem, PorterStemmer::Stem($word));
    }

    public static function wordProvider(): array
    {
        return [
            ['running', 'run'],
            ['runs', 'run'],
            ['horses', 'hors'],
            ['happiness', 'happi'],
            ['relational', 'relat'],
            ['agreed', 'agre'],
            ['feed', 'feed'],
            ['plastered', 'plaster'],
        ];
    }

    /**
     * StemString() intentionally leaves a trailing space after the last
     * word -- documented in its own source comment.
     */
    public function testStemStringStemsEachWord(): void
    {
        $this->assertSame('run hors ', PorterStemmer::StemString('running horses'));
    }
}
