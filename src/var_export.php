<?php

namespace Diskerror\Library;

/**
 * Behaves like var_export() but uses "[]" notation instead of "array()",
 * omits numeric keys on zero-based sequential arrays, and skips resources.
 *
 * @param mixed $in
 * @param bool $return Return the string instead of echoing it.
 * @param string $indentStr Indent string per level (default: three spaces).
 * @return string The exported representation (empty string when echoing).
 */
function var_export($in, bool $return = false, string $indentStr = '   '): string
{
	$out = _var_export_repr($in, 0, $indentStr);

	if ($return) {
		return $out;
	}

	echo $out, PHP_EOL;
	return '';
}

/**
 * Recursive representation helper for var_export().
 *
 * @param mixed $in
 * @param int $indent
 * @param string $indentStr
 * @return string
 * @internal
 */
function _var_export_repr($in, int $indent, string $indentStr): string
{
	static $isList = null;
	$isList ??= function_exists('array_is_list')    // PHP >= 8.1
		? 'array_is_list'
		: fn(array $a): bool => $a === [] || array_keys($a) === range(0, count($a) - 1);

	switch (gettype($in)) {
		case 'array':
			$returnStr = "[" . PHP_EOL;
			$listArr   = $isList($in);
			foreach ($in as $k => $v) {
				if (is_resource($v)) {
					continue;
				}
				$prefix = str_repeat($indentStr, $indent + 1);
				if ($listArr) {
					//  Drop numeric keys on zero-based sequential arrays.
					$returnStr .= $prefix . _var_export_repr($v, $indent + 1, $indentStr) . "," . PHP_EOL;
				}
				else {
					$returnStr .= $prefix . "'$k' => " . _var_export_repr($v, $indent + 1, $indentStr) . "," . PHP_EOL;
				}
			}
			$returnStr .= str_repeat($indentStr, $indent) . ']';
			return $returnStr;

		case 'object':
			$returnStr = "{" . PHP_EOL;
			foreach ($in as $k => $v) {
				if (is_resource($v)) {
					continue;
				}
				$returnStr .= str_repeat($indentStr, $indent + 1) . "'$k' => "
					. _var_export_repr($v, $indent + 1, $indentStr) . "," . PHP_EOL;
			}
			$returnStr .= str_repeat($indentStr, $indent) . '}';
			return $returnStr;

		case 'resource':
			return '';

		default:
			return \var_export($in, true);    //  Uses built-in function.
	}
}
