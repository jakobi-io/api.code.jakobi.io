<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{

    public function index() {
        return response()->json([
            "name" => env("APP_NAME"),
            "baseURL" => env("APP_URL"),
            "version" => env("APP_VERSION"),
            "company" => env("APP_COMPANY")
        ]);
    }
}
