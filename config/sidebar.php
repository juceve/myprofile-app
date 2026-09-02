<?php

return [
    [
        'title' => 'Principal',
        'items' => [
            [
                'name' => 'Panel General',
                'route' => 'dashboard',
                'icon' => 'gauge-high',
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
        ],
    ],
    // [
    //     'title' => 'Gestión institucional',
    //     'items' => [
    //         [
    //             'name' => 'Usuarios y Roles',
    //             'icon' => 'users',
    //             'submenu' => [
    //                 ['name' => 'Listado de usuarios', 'route' => 'blank', 'can' => 'users.view'],
    //                 ['name' => 'Permisos y roles', 'route' => 'roles.index', 'can' => 'roles.view'],
    //             ],
    //         ],
    //         [
    //             'name' => 'Expedientes y Trámites',
    //             'icon' => 'folder-open',
    //             'can' => 'procedures.view',
    //             'submenu' => [
    //                 ['name' => 'Todos los expedientes', 'route' => 'blank', 'can' => 'procedures.view'],
    //                 ['name' => 'Nuevo trámite', 'route' => 'blank', 'can' => 'procedures.create'],
    //             ],
    //         ],
    //     ],
    // ],
    // [
    //     'title' => 'UI & Componentes',
    //     'items' => [
    //         ['name' => 'Formularios y Controles', 'route' => 'forms', 'icon' => 'list-check'],
    //         ['name' => 'Paleta y Componentes', 'route' => 'blank', 'icon' => 'palette'],
    //     ],
    // ],
    // [
    //     'title' => 'Integración Laravel',
    //     'items' => [
    //         ['name' => 'Laravel 13 & Blade', 'route' => 'blank', 'icon' => 'code'],
    //         ['name' => 'Ajustes del Sistema', 'route' => 'blank', 'icon' => 'gear', 'can' => 'settings.view'],
    //     ],
    // ],
    // [
    //     'title' => 'Cuenta',
    //     'items' => [
    //         ['name' => 'Mi perfil', 'route' => 'profile', 'icon' => 'user-circle'],
    //     ],
    // ],
];
