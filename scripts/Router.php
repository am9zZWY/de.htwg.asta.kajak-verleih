<?php

$routes = [
    '/api' => [
        'path' => 'api/page_api.php',
        'exitBeforePrint' => TRUE
    ],
    '/terms' => [
        'path' => 'user/page_user_agb.php',
    ],
    '/privacy' => [
        'path' => 'user/page_user_privacy.php',
    ],
    '/imprint' => [
        'path' => 'user/page_user_imprint.php',
    ],
    '/login' => [
        'path' => 'admin/page_admin_login.php',
    ],
    '/cancel' => [
        'path' => 'user/page_user_cancel.php',
    ],
    '/' => [
        'path' => 'user/page_user_reservation.php',
    ],
    '/admin' => [
        'path' => 'admin/page_admin.php',
        'auth' => TRUE
    ]
];

/**
 *
 * @param $route
 * @param $print
 *
 * @return void
 */
function route($route, $print)
{
    global $routes;

    /* when ONLINE = FALSE then don't show the system*/
    if (isset($_ENV['ONLINE']) && $_ENV['ONLINE'] === 'FALSE') {
        include __DIR__ . '/../pages/page_down.html';
        exit(0);
    }

    if (array_key_exists($route, $routes)) {
        /* is valid route */
        $route_in_dict = $routes[$route];
        if (array_key_exists('auth', $route_in_dict) && $route_in_dict['auth'] === TRUE && !is_logged_in()) {
            /* 403 */
            echo '<title>No!</title>';
            exit();
        }

        if (array_key_exists('exitBeforePrint', $route_in_dict) && $route_in_dict['exitBeforePrint'] === TRUE) {
            require __DIR__ . '/../pages/' . $route_in_dict['path'];
            exit();
        }

        if ($print === TRUE) {
            require __DIR__ . '/../pages/' . $route_in_dict['path'];
        }
    } else {
        /* 404 */
        exit();
    }
}