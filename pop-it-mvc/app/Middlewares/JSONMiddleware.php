<?php

namespace Middlewares;

use Src\Request;
use function Collect\collection;

class JSONMiddleware
{
    public function handle(Request $request): Request
    {
        if ($request->method === 'GET') {
            return $request;
        }

        //Получаем неструктурированные json данные и преобразуем их в массив
        $data = json_decode(file_get_contents("php://input"), true) ?? [];

        //Массив сливаем в request
        collection($data)->each(function ($item, $key, $request) {
            $request->set($key, $item);
        }, $request);

        return $request;
    }
}
