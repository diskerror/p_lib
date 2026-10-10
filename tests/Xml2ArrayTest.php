<?php

namespace Diskerror\Library\Tests;

use PHPUnit\Framework\TestCase;

use function Diskerror\Library\Xml2Array\process;

class Xml2ArrayTest extends TestCase
{
    public function testBasicElementsBecomeArrayValues(): void
    {
        $arr = process('<root><name>Reid</name></root>');
        $this->assertSame('Reid', $arr['name']);
    }

    public function testRepeatedSiblingSingularizesAndNumbersKeys(): void
    {
        $arr = process('<root><items><item>one</item><item>two</item></items></root>');
        $this->assertArrayHasKey('item', $arr);
        // Note: existing _fixArray() behavior wraps each repeated leaf in
        // its own single-element array -- this documents that as-is.
        $this->assertSame(['one'], $arr['item'][0]);
        $this->assertSame(['two'], $arr['item'][1]);
    }

    public function testNamespaceRemoveStripsPrefixes(): void
    {
        $xml = '<ns:root xmlns:ns="urn:x"><ns:name>Reid</ns:name></ns:root>';
        $arr = process($xml, 'remove');
        $this->assertArrayHasKey('name', $arr);
        $this->assertSame('Reid', $arr['name']);
    }
}
