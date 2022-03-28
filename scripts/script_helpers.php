<?php
/**
 * Escape all html characters.
 *
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
 *
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

/**
 * Checks if Server is using HTTPS.
 *
 * @return bool
 */
function is_secure(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443;
}

$SERVER_ADDRESS = $_SESSION['SERVER_ADDRESS'] ?? ((is_secure() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);

/**
 * Creates link with SERVER_ADDRESS as base.
 *
 * @param string $link
 * @return string
 */
function create_internal_link(string $link = ''): string
{
    global $SERVER_ADDRESS;
    return $SERVER_ADDRESS . $link;
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