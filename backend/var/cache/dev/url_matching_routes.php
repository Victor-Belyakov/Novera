<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/auth/logout' => [[['_route' => 'app_auth_logout__invoke', '_controller' => 'App\\Auth\\Controller\\LogoutController'], null, ['POST' => 0], null, false, false, null]],
        '/api/auth/refresh' => [[['_route' => 'app_auth_refreshtoken__invoke', '_controller' => 'App\\Auth\\Controller\\RefreshTokenController'], null, ['POST' => 0], null, false, false, null]],
        '/api/auth/register' => [[['_route' => 'app_auth_register__invoke', '_controller' => 'App\\Auth\\Controller\\RegisterController'], null, ['POST' => 0], null, false, false, null]],
        '/api/auth/login' => [[['_route' => 'login_check'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [
            [['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
