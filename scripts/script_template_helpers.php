<?php

/**
 * Helper function to create header.
 *
 * @param string $header
 * @param string|null $link
 * @return string
 */
function create_header(string $header, string|null $link = null): string
{
    return "
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
}

/**
 * Creates an accordion.
 *
 * @return string
 */
function create_accordion(): string
{
    return "<script>
                Array.from(document.getElementsByClassName('accordion')).forEach((item) => {
                    item.addEventListener('click', function () {
                        item.classList.toggle('active');
                        const panel = item.nextElementSibling;
                        panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + 'px';
                    });
                })
            </script>";
}