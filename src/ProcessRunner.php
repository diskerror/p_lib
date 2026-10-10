<?php

namespace Diskerror\Library;

class ProcessRunner
{
	const MAX_PROCS = 4;

	/** @var int */
	public $maxProcs;

	/** @var array */
	private $commands;

	/** @var string|null */
	private $cwd;

	/** @var CmdBuffer[] */
	private $processes = [];


	/**
	 * ProcessRunner constructor.
	 *
	 * @param iterable $commands
	 * @param string|null $cwd
	 */
	public function __construct(iterable $commands, ?string $cwd = null)
	{
		// Normalize to an array: this class needs count() and index access,
		// which a bare Iterator/Generator doesn't support.
		$this->commands = is_array($commands) ? array_values($commands) : iterator_to_array($commands, false);

		// Get the maximum number of level-zero (performance) processors available.
		$maxProcs = 0;
		if (stripos(PHP_OS, 'Darwin') !== false) {
			$maxProcs = (int)('0' . exec('sysctl -n hw.perflevel0.physicalcpu 2>/dev/null'));
		}
		elseif (is_readable('/proc/cpuinfo')) {
			$maxProcs = (int)('0' . exec('nproc 2>/dev/null'));
		}
		$this->maxProcs = $maxProcs === 0 ? self::MAX_PROCS : $maxProcs;

		if ($cwd !== null && !is_dir($cwd)) {
			throw new \InvalidArgumentException('The path "' . $cwd . '" is not a directory.');
		}
		$this->cwd = $cwd;
	}

	public function run(): void
	{
		$numCommands = count($this->commands);

		// Start the maximum number of processes
		for ($pNum = 0, $cmdNum = 0; $pNum < $this->maxProcs && $cmdNum < $numCommands; $pNum++, $cmdNum++) {
			$this->processes[$pNum] = new CmdBuffer($this->commands[$cmdNum], $this->cwd);
		}

		// Start remaining processes as earlier processes finish
		while ($cmdNum < $numCommands) {
			usleep(100000);
			for ($pNum = 0; $pNum < $this->maxProcs && $cmdNum < $numCommands; $pNum++) {
				if (!$this->processes[$pNum]->isRunning()) {
					$this->processes[$pNum] = new CmdBuffer($this->commands[$cmdNum], $this->cwd);
					$cmdNum++;
				}
			}
		}
	}

	public function wait(): void
	{
		// Wait for all processes to finish
		do {
			usleep(100000);
			$running = false;
			foreach ($this->processes as $process) {
				if ($process->isRunning()) {
					$running = true;
					break;
				}
			}
		} while ($running);
	}

	protected function runningString(): string
	{
		$str = '';
		foreach ($this->processes as $pNum => $process) {
			$str .= $process->isRunning() ? '1' : '0';
		}
		return $str;
	}
}
