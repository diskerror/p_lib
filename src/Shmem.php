<?php
/** @noinspection ALL */

namespace Diskerror\Library;

use Diskerror\Library\Exceptions\BadMethodCallException;
use Diskerror\Library\Exceptions\RuntimeException;
use Diskerror\Library\Exceptions\ShmemOpenException;
use Diskerror\Library\Exceptions\UnexpectedValueException;

/**
 * class Shmem
 *
 * This class will NOT remove the shared memory value when going out of scope.
 */
class Shmem
{
	protected $_shmop;

	/**
	 * @param string $id
	 * @param string $mode
	 * @param int $permissions
	 * @param int $size
	 */
	public function __construct(string $id, string $mode = 'a', int $permissions = 0666, int $size = 0)
	{
		// ftok()'s project-id argument is only ever ONE byte; passing a
		// multi-character $id directly would silently truncate to its first
		// character, letting unrelated ids (e.g. "cache" and "config")
		// collide on the same shared-memory segment. Instead, derive a
		// unique, stable key file per $id and ftok() that.
		$keyFile = sys_get_temp_dir() . '/diskerror-library-shmem-' . md5($id);
		if (!file_exists($keyFile)) {
			touch($keyFile);
		}

		$this->_shmop = @shmop_open(ftok($keyFile, 'D'), $mode, $permissions, $size);

		if ($this->_shmop === false) {
			throw new ShmemOpenException('could not open or create shared memory');
		}
	}

	/**
	 *
	 */
	public function __destruct()
	{
		// shmop_close() is required on PHP 7.4 but deprecated since PHP 8.0,
		// where shmop resources/objects are freed automatically.
		if (PHP_MAJOR_VERSION < 8) {
			shmop_close($this->_shmop);
		}
	}

	/**
	 * @return string
	 */
	public function __invoke(...$args): string
	{
		if (count($args) > 2) {
			throw new BadMethodCallException('too many arguments');
		}

		return $this->read(...$args);
	}

	/**
	 * @param int $offset
	 * @param int $size
	 *
	 * @return string
	 */
	public function read(int $offset = 0, int $size = 0): string
	{
		if ($size < 0) {
			throw new UnexpectedValueException('offset cannot be less than zero');
		}
		elseif ($size === 0) {
			$size = $this->size();
		}

		return shmop_read($this->_shmop, $offset, $size);
	}

	public function size(): int
	{
		return shmop_size($this->_shmop);
	}

	public function delete(): void
	{
		if (shmop_delete($this->_shmop) === false) {
			throw new RuntimeException('problem deleting shared memory object');
		}
	}

	/**
	 * @param string $data
	 * @param int $offset
	 *
	 * @return void
	 */
	public function write(string $data, int $offset = 0): void
	{
		shmop_write($this->_shmop, $data, $offset);
	}

}
