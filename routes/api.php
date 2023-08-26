<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('orders', 'App\Http\Controllers\Api\OrderController@getOrders');
Route::post('make-order', 'App\Http\Controllers\Api\OrderController@makeOrder');
Route::post('delete-order', 'App\Http\Controllers\Api\OrderController@deleteOrder');
Route::post('orders/{id}/add', 'App\Http\Controllers\Api\OrderController@addProductToOrder');
Route::post('orders/{id}/pay', 'App\Http\Controllers\Api\OrderController@pay');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
   return $request->user();
});
