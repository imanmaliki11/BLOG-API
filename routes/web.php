<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function (Request $r) use ($router) {
    return response([
        "status" => true,
        "message" => "Blog API By IMANxxx",
        "ip" => $r->ip()
    ])
    ->withHeaders([
                'Content-Type' => 'text/html; charset=UTF-8',
                'Access-Control-Allow-Origin' => '*'
            ]);
});

$router->get("/artisan", function(Request $req) {
    Artisan::call($req->code);
    return "php artisan:" . $req->code;
});

$router->get('/keys', function() {
    return Str::random(32);
});

$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');

$router->group(["prefix" => "user"], function() use ($router) {
    $router->get('/{id}', "UserController@getUserByID");
    $router->group(["middleware" => 'auth'], function() use ($router) {
        $router->get('/', "UserController@getUser");
        $router->patch("/", "UserController@updateUser");
        $router->post("/logo", "UserController@uploadLogo");
    });
});

$router->group(["prefix" => "category"], function() use ($router) {
    $router->get('/', "CategoryController@getCategory");
    $router->get('/{id}', "CategoryController@getCategoryByID");
    $router->group(["middleware" => 'auth'], function() use ($router) {
        $router->post('/', "CategoryController@createCategory");
        $router->delete('/{id}', "CategoryController@deleteCategory");
        $router->patch('/{id}', "CategoryController@updateCategory");
    });
});

$router->group(["prefix" => "tags"], function() use ($router) {
    $router->get('/', "TagsController@getTags");
    $router->get('/{id}', "TagsController@getTagsByID");
    $router->group(["middleware" => 'auth'], function() use ($router) {
        $router->post('/', "TagsController@createTags");
        $router->delete('/{id}', "TagsController@deleteTags");
        $router->patch('/{id}', "TagsController@updateTags");
    });
});

$router->group(["prefix" => "post"], function() use ($router) {
    $router->get('/', "PostController@getPost");
    $router->get('/{id}', "PostController@getPostByID");
    $router->group(["middleware" => 'auth'], function() use ($router) {
        $router->post('/', "PostController@createPost");
        $router->delete('/{id}', "PostController@deletePost");
        $router->post('/{id}', "PostController@updatePost");
    });
});


