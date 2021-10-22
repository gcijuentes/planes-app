<?php

use App\Http\Controllers\PlanesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

*/

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class,'login']);
    Route::post('signup',[AuthController::class,'signUp'] );

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', [AuthController::class,'logout']);
        Route::get('user',  [AuthController::class,'user']);
    });
});
/*
Route::get('/login', function () {
    return '401';
})->name('login');
*/

Route::get('/cards',[PlanesController::class,'getCardPlanes']);

Route::resource('/planes', 'App\Http\Controllers\PlanesController');
Route::resource('/usuarios', 'App\Http\Controllers\UsuariosController');
Route::resource('/isapres', 'App\Http\Controllers\IsapresController');
Route::resource('/import', 'App\Http\Controllers\ImportController');

Route::fallback(function () {
    return response()->json(['error' => 'Not Found!'], 404);
});


Route::post('oauth/token', 'Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');
