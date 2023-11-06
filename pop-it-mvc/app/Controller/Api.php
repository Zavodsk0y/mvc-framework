<?php

namespace Controller;

use Model\Post;
use Model\User;
use Src\Request;
use Src\Validator\Validator;
use Src\View;

class Api
{
    public function index(): void
    {
        $posts = Post::all()->toArray();

        (new View())->toJSON($posts);
    }

    public function echo(Request $request): void
    {
        (new View())->toJSON($request->all());
    }

    public function signup(Request $request): void
    {
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'name' => ['required'],
                'login' => ['required', 'unique:users,login'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if ($validator->fails()) {
                $response = [
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ];
                http_response_code(422);
                $view = new View();
                $view->toJSON($response);
                return;
            }

            // Создание пользователя
            $user = new User();
            $user->name = $request->all()['name'];
            $user->login = $request->all()['login'];
            $user->password = password_hash($request->all()['password'], PASSWORD_DEFAULT);

            if ($user->save()) {
                // Генерация и сохранение токена пользователя
                $userToken = md5(uniqid());
                $user->token = $userToken;
                $user->save();

                // Включение токена в JSON-ответ при успешной регистрации
                $response = ['user_token' => $userToken, 'message' => 'Registration successful', 'hash' => $user->password];
                http_response_code(201);
                $view = new View();
                $view->toJSON($response);
                return;
            }
        }

        $response = ['message' => 'Registration failed'];
        http_response_code(500);
        $view = new View();
        $view->toJSON($response);
    }

    public function login(Request $request): void
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            $user = User::where('login', $data['login'])->first();

            if (!$user || !password_verify($data['password'], $user->password)) {
                // Неправильные логин или пароль
                $response = ['message' => 'Неправильные логин или пароль', 'pass' => $data['password'], 'hash' => $user->password, 'login-hash' => $data['password']];
                http_response_code(401); // Unauthorized
                $view = new View();
                $view->toJSON($response);
                return;
            }

            // Генерация токена
            $userToken = md5(uniqid());
            $user->token = $userToken;
            $user->save();

            // Включение токена в JSON-ответ при успешной аутентификации
            $response = ['user_token' => $userToken];
            http_response_code(200); // OK
            $view = new View();
            $view->toJSON($response);
        }
    }

}
