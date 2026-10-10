<?php
/** @noinspection SpellCheckingInspection */

namespace Diskerror\Library;

use Diskerror\Library\Exceptions\RuntimeException;

class Curl
{
	protected $_curl = null;

	public function __construct(?string $url = null, array $opt = [])
	{
		$this->_curl = curl_init($url);
		$this->_errorCheck();
		if (count($opt)) {
			$this->setopt_array($opt);
		}
	}

	public function __destruct()
	{
		if (isset($this->_curl)) {
			curl_close($this->_curl);
			unset($this->_curl);
		}
	}

	public function setopt($opt, $val = true): bool
	{
		$res = curl_setopt($this->_curl, $opt, $val);
		$this->_errorCheck();
		return $res;
	}

	public function setopt_array(array $opta): bool
	{
		$res = curl_setopt_array($this->_curl, $opta);
		$this->_errorCheck();
		return $res;
	}

	public function exec()
	{
		$r = curl_exec($this->_curl);
		$this->_errorCheck();
		return $r;
	}

	protected static function strerror(int $error_code): ?string
	{
		return curl_strerror($error_code);
	}

	protected function _errorCheck(): void
	{
		if (isset($this->_curl) && ($err_num = $this->errno()) > 0) {
			throw new RuntimeException($this->error(), $err_num);
		}
	}

	public function errno(): int
	{
		return curl_errno($this->_curl);
	}

	public function error(): string
	{
		return curl_error($this->_curl);
	}

	public static function version()
	{
		return curl_version();
	}
}
