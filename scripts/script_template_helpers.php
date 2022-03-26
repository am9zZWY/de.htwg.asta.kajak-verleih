<?php

/**
 * Helper function to create header.
 *
 * @param string $header
 * @param string $link
 * @param bool $echo
 * @return string
 */
function create_header(string $header, string $link = '', bool $echo = true): string
{
    $created_header = "
    <div class='header-wrapper'>
        <a href='{$link}' class='primary text-decoration-none'>
            <h1>
                {$header}
            </h1>
        </a>
    </div>";

    if ($echo) {
        echo $created_header;
    }
    return $created_header;
}