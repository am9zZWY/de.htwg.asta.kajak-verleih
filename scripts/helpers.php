<?php
/**
 * Escape all html characters.
 * @param string|null $string |null $string $string
 * @return string
 */
function clean_string(string|null $string): string
{
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES);
}

/**
 * Escape all html characters in array.
 * @param array|null $array
 * @return array
 */
function clean_array(array|null $array): array
{
    if ($array === null) {
        return [];
    }
    return array_map('clean_string', $array);
}