<?php
/**
 * Utility functions
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Check whether string starts with substring
 *
 * @param string $haystack - string to search within
 * @param string $needle - string to search for
 * @return boolean
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}

/**
 * Returns the first argument that evaluates as true. If passed a single non-empty Array,
 * will return the first value that evaluates as true.
 *
 * @param mixed ...$values - a series or array of values
 * @return mixed
 */
function resolve(...$values)
{
    if ($values[0] && count($values) == 1 && is_array($values[0])) {
        $values = array_values($values[0]);
    }
    $i = 0;
    $c = count($values);
    do {
        $result = $values[$i];
        $i++;
    } while (!$result && $i < $c);
    return $result;
}
