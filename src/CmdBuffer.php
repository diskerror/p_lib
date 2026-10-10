<?php
/** @noinspection SpellCheckingInspection */

namespace Diskerror\Library;

/**
 * Buffered process I/O via proc_open; errors go straight to STDERR.
 *
 * @package Diskerror\\Library
 */
class CmdBuffer
{
	private const MAX_READ = 65536;

	/** @var array Pipes from proc_open. */
	private $pipes;

	/** @var resource|false The process resource. */
	private $procRes;

	/** @var array|null Cached terminal status once the process has exited. */
	private $finalStatus = null;

	public function __construct(string $cmd, ?string $cwd = null)
	{
		if ($cwd !== null && !is_dir($cwd)) {
			throw new \InvalidArgumentException('The path "' . $cwd . '" is not a directory.');
		}

		//  Errors are sent immediately to STDERR
		$pipes         = [];
		$this->procRes = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w'], STDERR], $pipes, $cwd);

		if ($this->procRes === false) {
			throw new \RuntimeException('proc_open() failed for command: ' . $cmd);
		}

		$this->pipes = $pipes;
	}

	public function __destruct()
	{
		if (isset($this->procRes)) {
			if ($this->isRunning()) {
				proc_terminate($this->procRes);
			}

			foreach ($this->pipes as $pipe) {
				fclose($pipe);
			}

			proc_close($this->procRes);
		}
	}

	public function isEOF(): bool
	{
		return feof($this->pipes[1]);
	}

	public function read($length = self::MAX_READ): string
	{
		return fread($this->pipes[1], $length);
	}

	public function readLn($length = self::MAX_READ): string
	{
		return fgets($this->pipes[1], $length);
	}

	public function write($data): bool
	{
		return fwrite($this->pipes[0], $data) !== false;
	}

	public function getStatus(): array
	{
		if ($this->finalStatus !== null) {
			return $this->finalStatus;
		}

		$status = proc_get_status($this->procRes);

		if (!$status['running']) {
			// proc_get_status() only reports an accurate exitcode on the
			// first call after the process exits; cache it here so later
			// calls (isRunning(), exitCode(), etc.) stay consistent.
			$this->finalStatus = $status;
		}

		return $status;
	}

	public function getPID(): int
	{
		return $this->getStatus()['pid'];
	}

	public function isRunning(): bool
	{
		return $this->getStatus()['running'];
	}

	public function wasSignaled(): bool
	{
		return $this->getStatus()['signaled'];
	}

	public function wasStopped(): bool
	{
		return $this->getStatus()['stopped'];
	}

	public function exitCode(): int
	{
		return $this->getStatus()['exitcode'];
	}
}
