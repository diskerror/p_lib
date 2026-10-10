<?php

namespace Diskerror\Library\Json;

/**
 * Encode data as a JSON string; always throws on error.
 *
 * @param mixed $data
 * @param int $flags
 * @return string|false
 * @throws \JsonException
 */
function encode($data, int $flags = JSON_PRETTY_PRINT)
{
	return json_encode($data, $flags | JSON_THROW_ON_ERROR);    // always throw on error
}

/**
 * Decode a JSON string to an associative array; always throws on error.
 *
 * @param mixed $data
 * @return mixed
 * @throws \JsonException
 */
function decode($data)
{
	return json_decode((string)$data, true, 512, JSON_THROW_ON_ERROR);
}

/**
 * Validate that a string is well-formed JSON.
 *
 * @param mixed $data
 * @return bool
 */
function validate($data): bool
{
	static $impl = null;
	$impl ??= function_exists('json_validate')    // PHP >= 8.3
		? 'json_validate'
		: function (string $d): bool {
			try {
				json_decode($d, true, 512, JSON_THROW_ON_ERROR);
				return true;
			}
			catch (\JsonException $e) {
				return false;
			}
		};

	return $impl((string)$data);
}
