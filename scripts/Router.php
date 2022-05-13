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
 * @return void
 */
function route($route, $print)
{
    global $routes;

    if (array_key_exists($route, $routes)) {
        /* is valid route */
        $route_in_dict = $routes[$route];
        if ($route_in_dict['auth'] === TRUE && !is_logged_in()) {
            /* 403 */
            header('403 Restricted', TRUE, 403);
            /* hehe */
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
        header('404 Not found', TRUE, 404);
        exit();
    }
}