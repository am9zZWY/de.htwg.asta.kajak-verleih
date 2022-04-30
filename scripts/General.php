<?php


/**
 * Escape all html characters.
 *
 * @param string|null $string |null $string $string
 * @return string
 */
function clean_string(?string $string): string
{
    if ($string === null) {
        return '';
    }
    /* convert special chars to prevent sql injection and trim also all whitespaces to prevent database errors */
    return trim(htmlspecialchars($string, ENT_QUOTES));
}

/**
 * Escape all html characters in array.
 *
 * @param array|null $array
 * @return array
 */
function clean_array(?array $array): array
{
    if ($array === null) {
        return [];
    }
    return array_map('clean_string', $array);
}

/**
 * Get env variable.
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function get_env(string $key, string $default = ''): string
{
    return $_ENV[$key] ?? $default;
}

/**
 * Get field from POST request.
 *
 * @param string $field
 * @param mixed $default
 * @return string
 */
function get_post_field(string $field, string $default = ''): string
{
    return clean_string($_POST[$field] ?? $default);
}

/**
 * Get random token.
 *
 * @return string
 */
function get_random_token(): string
{
    return md5(uniqid(mt_rand(), true));
}