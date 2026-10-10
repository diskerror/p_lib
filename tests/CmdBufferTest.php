<?php

namespace Diskerror\Library\Tests;

use Diskerror\Library\CmdBuffer;
use PHPUnit\Framework\TestCase;

class CmdBufferTest extends TestCase
{
    public function testReadsOutputFromEcho(): void
    {
        $cb = new CmdBuffer('echo hello');
        usleep(200000);

        $this->assertStringContainsString('hello', $cb->read());
    }

    /**
     * Regression test: proc_get_status() only reports an accurate exitcode
     * on the FIRST call after the process exits; getStatus() must cache the
     * terminal status so repeated calls (isRunning() then exitCode(), etc.)
     * don't silently get -1 on the second and later calls.
     */
    public function testExitCodeIsConsistentAcrossMultipleCalls(): void
    {
        $cb = new CmdBuffer('exit 0');
        usleep(200000);

        $this->assertFalse($cb->isRunning());
        $first  = $cb->exitCode();
        $second = $cb->exitCode();
        $third  = $cb->exitCode();

        $this->assertSame(0, $first);
        $this->assertSame($first, $second);
        $this->assertSame($first, $third);
    }

    public function testConstructorThrowsOnBadCwd(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CmdBuffer('echo hello', '/no/such/directory/xyz');
    }
}
