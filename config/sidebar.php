<?php

return [
    [
        'title' => 'Principal',
        'items' => [
            [
                'name' => 'Panel General',
                'route' => 'dashboard',
                'icon' => 'gauge-high',
                'can' => 'dashboard.view',
            ],
        ],
    ],
    [
        'title' => 'Cobranzas',
        'items' => [
            [
                'name' => 'Cartera',
                'route' => 'cartera.index',
                'icon' => 'wallet',
                'can' => 'cartera.view',
            ],
        ],
    ],
    [
        'title' => 'Configuraciones',
        'items' => [
            [
                'name' => 'Usuarios y Roles',
                'icon' => 'users',
                'submenu' => [
                    ['name' => 'Listado de usuarios', 'route' => 'blank', 'can' => 'users.view'],
                    ['name' => 'Permisos y roles', 'route' => 'roles.index', 'can' => 'roles.view'],
                ],
            ],
            [
                'name' => 'Empresas mandantes',
                'route' => 'empresas-mandantes.index',
                'icon' => 'building',
                'can' => 'empresas.view',
            ],
            [
                'name' => 'Jefes de venta',
                'route' => 'jefes-venta.index',
                'icon' => 'user-tie',
                'can' => 'empresas.view',
            ],
        ],
    ],
];
