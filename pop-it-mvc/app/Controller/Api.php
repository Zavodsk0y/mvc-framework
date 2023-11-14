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
                'password' => ['required', 'length']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально',
                'length' => 'Поле :field должно содержать больше 6 символов'
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

            $user = new User();
            $user->name = $request->all()['name'];
            $user->login = $request->all()['login'];
            $user->password = password_hash($request->all()['password'], PASSWORD_DEFAULT);

            if ($user->save()) {
                $userToken = md5(uniqid());
                $user->token = $userToken;
                $user->save();

                $response = ['user_token' => $userToken];
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
            $hash =  password_hash("password", PASSWORD_DEFAULT);

            if (!password_verify('password', $hash)) {
                $response = ['message' => 'Auth failed'];
                http_response_code(401);
                $view = new View();
                $view->toJSON($response);
                return;
            }

            $userToken = md5(uniqid());
            $user->token = $userToken;
            $user->save();

            $response = ['user_token' => $userToken];
            http_response_code(200);
            $view = new View();
            $view->toJSON($response);
        }
    }

}
