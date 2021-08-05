<?php
use Illuminate\Support\Facades\Route ;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
////    return $request->user();
////});

// User
Route::post('register','AuthController@register');
Route::post('login','AuthController@login');
Route::delete('user/{user}','AuthController@delete');
Route::get('me','AuthController@me');

// Posts
Route::get('post','PostController@index');
Route::post('store/post','PostController@store');
Route::get('show/post/{post}','PostController@show')->where('post','[0-9]+');
Route::post('update/post/{post}','PostController@update')->where('post','[0-9]+');
Route::delete('delete/post/{post}','PostController@destroy')->where('post','[0-9]+');


Route::post('laratrust','LaratrustController@create');