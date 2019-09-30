<?php

use Illuminate\Http\Request;

header('Access-Control-Allow-Origin: *');
//Access-Control-Allow-Origin: *
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');


// routePost('login', 'login');
Route::post('auth/login', 'AuthController@login');
Route::post('auth/register', 'AuthController@register');
Route::get('auth/logout', 'AuthController@logout');

Route::get('generator/transaction', 'GeneratorController@ApiCrudTransaction');
Route::get('generator/show', 'GeneratorController@show');
Route::get('generator/postman', 'GeneratorController@postman');
Route::get('generator/ui', 'GeneratorController@ui');
