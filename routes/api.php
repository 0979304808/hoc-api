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
Route::post('register','AuthController@register');
Route::post('login','AuthController@login');

Route::delete('user/{user}','AuthController@delete');

//
//Route::group(['middleware'=>['CheckUser']],function (){
//
//});

//   Route::apiResource('post','PostController');


Route::get('me','AuthController@me');
Route::apiResource('post','PostController')->except('update');
Route::post('post/{post}','PostController@update');
Route::post('laratrust','LaratrustController@create');