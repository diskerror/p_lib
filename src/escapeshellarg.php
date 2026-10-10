<?php

namespace Diskerror\Library;

/**
 * Perform shell argument escaping on string characters without adding quotes.
 *
 * Unlike the built-in escapeshellarg(), no surrounding quotes are added.
 * Accepts a string or an iterable of values; arrays are mapped element-wise.
 *
 * @param string|iterable $in
 * @return string|array
 */
function escapeshellarg($in)
{
	static $escPairs = [
		' ' => '\\ ',
		"'" => "\\'",
		'"' => '\\"',
		'(' => '\\(',
		')' => '\\)',
		'[' => '\\[',
		']' => '\\]',
		'{' => '\\{',
		'}' => '\\}',
		'*' => '\\*',
		'?' => '\\?',
		'\\' => '\\\\',
	];

	if (is_string($in)) {
		return strtr($in, $escPairs);
	}

	return array_map(
		function ($v) use ($escPairs) {
			return strtr((string)$v, $escPairs);
		},
		(array)$in);
}
