<?php

use Illuminate\Http\Request;

header('Access-Control-Allow-Origin: *');
//Access-Control-Allow-Origin: *
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

$source = 'cr';
Route::post("$source/insert", 'CrController@insert');
Route::get("$source/updateStatus", 'CrController@updateStatus');
Route::get("$source/delete", 'CrController@delete');
Route::get("$source/getDataById", 'CrController@getDataById');
Route::get("$source/search", 'CrController@search');
Route::get("$source/terkait", 'CrController@terkait');
Route::get("$source/getDataByIdHistory", 'CrController@getDataByIdHistory');
Route::get("$source/searchHistory", 'CrController@searchHistory');

$source = 'cd';
Route::post("$source/insert", 'CdController@insert');
Route::get("$source/updateStatus", 'CdController@updateStatus');
Route::get("$source/delete", 'CdController@delete');
Route::get("$source/getDataById", 'CdController@getDataById');
Route::get("$source/search", 'CdController@search');
Route::get("$source/terkait", 'CdController@terkait');
Route::get("$source/getDataByIdHistory", 'CdController@getDataByIdHistory');
Route::get("$source/searchHistory", 'CdController@searchHistory');
