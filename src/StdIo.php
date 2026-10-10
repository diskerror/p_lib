<?php

namespace Diskerror\Library\StdIo;

use Diskerror\Library\Exceptions\RuntimeException;
use Diskerror\Library\Json;

/**
 * Wrap a string in an ANSI color code when STDOUT is a TTY.
 *
 * @param string $s
 * @param string $color One of: success, warn, fail, info.
 * @return string
 */
function colorize(string $s, string $color): string
{
	static $COLORS = [
		'success' => "\033[0;32m",
		'warn' => "\033[1;33m",
		'fail' => "\033[0;31m",
		'info' => "\033[0;36m",
		'reset' => "\033[0m",
	];

	static $isatty = null;
	$isatty ??= function_exists('posix_isatty')
		? 'posix_isatty'
		: fn($stream) => false;

	if ($isatty(STDOUT)) {
		return $COLORS[$color] . $s . $COLORS['reset'];
	}

	return $s;
}

/**
 * Print a success-colored line to STDOUT.
 *
 * @param string $s
 * @return void
 */
function success(string $s): void
{
	outln(colorize($s, 'success'));
}

/**
 * Print a warning-colored line to STDOUT.
 *
 * @param string $s
 * @return void
 */
function warn(string $s): void
{
	outln(colorize($s, 'warn'));
}

/**
 * Print a fail-colored line to STDERR.
 *
 * @param string $s
 * @return void
 */
function fail(string $s): void
{
	err(colorize($s, 'fail'));
}

/**
 * Print an info-colored line to STDOUT.
 *
 * @param string $s
 * @return void
 */
function info(string $s): void
{
	outln(colorize($s, 'info'));
}

/**
 * Read up to $length bytes from STDIN.
 *
 * @param int $length
 * @return string
 * @throws RuntimeException
 */
function in(int $length = 80)
{
	if (($in = fread(STDIN, $length)) === false) {
		throw new RuntimeException('fread from STDIN returned false');
	}

	return $in;
}

/**
 * Write a string to STDOUT without a newline.
 *
 * @param string $s
 * @return void
 * @throws RuntimeException
 */
function out($s)
{
	if (fwrite(STDOUT, $s) === false) {
		throw new RuntimeException('fwrite to STDOUT returned false');
	}
}

/**
 * Write a line to STDOUT.
 *
 * @param string $s
 * @return void
 */
function outln($s = '')
{
	out($s . PHP_EOL);
}

/**
 * Print an object/array as pretty-printed JSON to STDOUT.
 *
 * @param mixed $o
 * @return void
 */
function jsonOut($o)
{
	outln(Json\encode($o));
}

/**
 * Write a line to STDERR.
 *
 * @param string $s
 * @return void
 * @throws RuntimeException
 */
function err($s)
{
	if (fwrite(STDERR, $s . PHP_EOL) === false) {
		throw new RuntimeException('fwrite to STDERR returned false');
	}
}

/**
 * Print a modernized "var_export" style representation: just the data
 * structure, with "[]" notation, no numeric keys on zero-based lists,
 * objects reduced to their data, four spaces per indent level.
 *
 * @param mixed $o
 * @return void
 */
function phpOut($o)
{
	if (is_object($o)) {
		if (method_exists($o, '_toArray') || method_exists($o, 'toArray')) {
			$o = $o->toArray();
		}
		elseif (method_exists($o, '__toString')) {
			$o = (string)$o;
		}
		else {
			$o = (array)$o;
		}
	}

	outln(\Diskerror\Library\var_export($o, true, '    '));
}
