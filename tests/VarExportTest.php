<?php

namespace Diskerror\Library\Tests;

use PHPUnit\Framework\TestCase;

use function Diskerror\Library\var_export;

class VarExportTest extends TestCase
{
    public function testSequentialZeroBasedArrayDropsKeys(): void
    {
        $out = var_export([0 => 'a', 1 => 'b'], true);
        $this->assertStringNotContainsString("'0' =>", $out);
        $this->assertStringNotContainsString("'1' =>", $out);
        $this->assertStringContainsString("'a'", $out);
        $this->assertStringContainsString("'b'", $out);
    }

    /**
     * Regression test: _var_export_repr() once computed $isList but never
     * used it, so ANY integer key was dropped -- including non-sequential
     * or non-zero-based ones. Keys must be preserved here.
     */
    public function testNonSequentialIntegerKeysAreKept(): void
    {
        $out = var_export([5 => 'a', 7 => 'b'], true);
        $this->assertStringContainsString("'5' => 'a'", $out);
        $this->assertStringContainsString("'7' => 'b'", $out);
    }

    public function testEmptyArrayIsTreatedAsList(): void
    {
        $out = var_export([], true);
        $this->assertSame('[' . PHP_EOL . ']', $out);
    }

    public function testAssociativeArrayKeepsStringKeys(): void
    {
        $out = var_export(['x' => 1, 'y' => 2], true);
        $this->assertStringContainsString("'x' => 1", $out);
        $this->assertStringContainsString("'y' => 2", $out);
    }

    public function testNestedArraysAreIndented(): void
    {
        $out = var_export(['a' => [1, 2]], true);
        $this->assertStringContainsString("'a' => [", $out);
        $this->assertStringContainsString('1,', $out);
        $this->assertStringContainsString('2,', $out);
    }

    public function testObjectIsExportedAsBraceBlock(): void
    {
        $out = var_export((object)['k' => 'v'], true);
        $this->assertStringContainsString('{', $out);
        $this->assertStringContainsString("'k' => 'v'", $out);
        $this->assertStringContainsString('}', $out);
    }

    public function testResourcesAreSkipped(): void
    {
        $res = fopen('php://memory', 'r');
        $out = var_export(['a' => 1, 'r' => $res, 'b' => 2], true);
        fclose($res);

        $this->assertStringNotContainsString("'r' =>", $out);
        $this->assertStringContainsString("'a' => 1", $out);
        $this->assertStringContainsString("'b' => 2", $out);
    }

    public function testScalarsDelegateToBuiltin(): void
    {
        $this->assertSame(\var_export(42, true), var_export(42, true));
        $this->assertSame(\var_export('str', true), var_export('str', true));
        $this->assertSame(\var_export(true, true), var_export(true, true));
        $this->assertSame(\var_export(null, true), var_export(null, true));
    }

    public function testEchoModeReturnsEmptyString(): void
    {
        ob_start();
        $result = var_export(['a'], false);
        $printed = ob_get_clean();

        $this->assertSame('', $result);
        $this->assertStringContainsString("'a'", $printed);
    }
}
