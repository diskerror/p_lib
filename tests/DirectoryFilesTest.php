<?php

namespace Diskerror\Library\Tests;

use Diskerror\Library\DirectoryFiles;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DirectoryFilesTest extends TestCase
{
    /**
     * Regression test: the exception used to report realpath()'s result
     * (false, coerced to "") instead of the path the caller actually passed.
     */
    public function testBadPathExceptionNamesTheActualPath(): void
    {
        $badPath = '/no/such/path/' . uniqid();

        try {
            new DirectoryFiles($badPath);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        }
        catch (InvalidArgumentException $e) {
            $this->assertStringContainsString($badPath, $e->getMessage());
        }
    }

    public function testListsFilesInDirectory(): void
    {
        $tmpDir = sys_get_temp_dir() . '/p_lib_test_' . uniqid();
        mkdir($tmpDir);
        touch($tmpDir . '/a.txt');
        touch($tmpDir . '/b.txt');

        $df = new DirectoryFiles($tmpDir);

        $this->assertCount(2, $df->files);

        unlink($tmpDir . '/a.txt');
        unlink($tmpDir . '/b.txt');
        rmdir($tmpDir);
    }

    public function testIgnoredPathsAreExcluded(): void
    {
        $tmpDir = sys_get_temp_dir() . '/p_lib_test_' . uniqid();
        mkdir($tmpDir);
        touch($tmpDir . '/keep.txt');
        touch($tmpDir . '/skip.log');

        $df = new DirectoryFiles($tmpDir, ['.log']);

        $this->assertCount(1, $df->files);
        $this->assertContains('keep.txt', $df->files);

        unlink($tmpDir . '/keep.txt');
        unlink($tmpDir . '/skip.log');
        rmdir($tmpDir);
    }
}
