<?php

namespace Diskerror\Library\Tests;

use Diskerror\Library\DomDocParser;
use PHPUnit\Framework\TestCase;

class DomDocParserTest extends TestCase
{
    public function testToArrayCapturesTagAttributesAndText(): void
    {
        $dom = new DomDocParser();
        $dom->loadXML('<a b="1"><c>hi</c></a>');

        $arr = $dom->toArray();

        $this->assertSame('a', $arr['tag']);
        $this->assertSame('1', $arr['b']);
        $this->assertSame('c', $arr['childNodes'][0]['tag']);
        $this->assertSame('hi', $arr['childNodes'][0]['text']);
    }

    /**
     * Regression test: node2textArr() only checked XML_TEXT_NODE, so a
     * CDATA-only child was mishandled (treated as an element with no
     * tagName). CDATA must be treated the same as plain text.
     */
    public function testToTextArrayHandlesCdataLikePlainText(): void
    {
        $dom = new DomDocParser();
        $dom->loadXML('<root><![CDATA[hello world]]></root>');

        $result = $dom->toTextArray();

        $this->assertSame('hello world', $result);
    }
}
