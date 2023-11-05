<?php

use Src\Auth\Auth;

return [
    //Класс аутентификации
    'auth' => Auth::class,
    //Клас пользователя
    'identity' => \Model\User::class,
    'routeMiddleware' => [
        'auth' => \Middlewares\AuthMiddleware::class,
    ],
    'routeAppMiddleware' => [
        'trim' => \Middlewares\TrimMiddleware::class,
        'specialChars' => \Middlewares\SpecialCharsMiddleware::class,
        'csrf' => \Middlewares\CSRFMiddleware::class,
        'json' => \Middlewares\JSONMiddleware::class,
    ],

    'validators' => [
        'required' => \Src\Validator\RequireValidator::class,
        'unique' => \Src\Validator\UniqueValidator::class
    ],

    'providers' => [
        'kernel' => \Providers\KernelProvider::class,
        'route' => \Providers\RouteProvider::class,
        'db' => \Providers\DBProvider::class,
        'auth' => \Providers\AuthProvider::class,
    ],
];
