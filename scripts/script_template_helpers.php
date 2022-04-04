<?php

/**
 * Helper function to create header.
 *
 * @param string $header
 * @param string|null $link
 * @param bool $echo
 * @return string
 */
function create_header(string $header, string|null $link = null, bool $echo = true): string
{
    $created_header = "
    <div class='header-wrapper'>" .
        ($link === null ?
            "<h1 class='text-light'>
            $header
        </h1>" :
            "<a href = '$link' class='text-light text-decoration-none'>
            <h1>
                $header
            </h1>
        </a>
") . "</div> ";

    if ($echo) {
        echo $created_header;
    }
    return $created_header;
}

/**
 * Creates an accordion.
 *
 * @param bool $echo
 * @return string
 */
function create_accordion(bool $echo = true): string
{
    $created_accordion = "<script>
                Array.from(document.getElementsByClassName('accordion')).forEach((item) => {
                    item.addEventListener('click', function () {
                        item.classList.toggle('active');
                        const panel = item.nextElementSibling;
                        panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + 'px';
                    });
                })
            </script>";

    if ($echo) {
        echo $created_accordion;
    }
    return $created_accordion;
}