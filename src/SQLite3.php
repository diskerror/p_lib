<?php

namespace Diskerror\Library;

class SQLite3 extends \SQLite3
{
	public function __construct(
		string $filename,
		int    $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
		string $encryptionKey = "")
	{
		parent::__construct($filename, $flags, $encryptionKey);
		$this->exec('PRAGMA foreign_keys = ON;');
	}

	public function __destruct()
	{
		if (!$this->close()) {
			trigger_error('SQLite3::close() returned false');
		}
	}
}
