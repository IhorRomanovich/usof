<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

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

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth',
], function($router) {
    Route::post('login', 'AuthController@login')->name('api.auth.login');//->middleware('verified');
    Route::post('register', 'AuthController@register')->name('api.auth.register');
    Route::post('logout', 'AuthController@logout')->name('api.auth.logout');
    Route::post('refresh', 'AuthController@refresh')->name('api.auth.refresh');
    Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
    Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
    Route::post('password-reset', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('api.auth.emailresetpassword');//->name('password.reset');
    Route::post('password-reset/{confirmation_token}', 'Auth\ResetPasswordController@apiReset')->name('api.auth.resetpassword');//->name('newpassword.set');
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'users',
], function($router) {
    Route::get('/', 'UserController@all')->name('user.all');
    Route::get('/{user_id}', 'UserController@userByID')->name('user.byID');
    Route::post('/', 'UserController@AddUser')->name('user.add');
    Route::post('avatar', 'UserController@UploadAvatar')->name('user.avatar');
    Route::patch('/{user_id}', 'UserController@UpdateUserData')->name('user.update');
    Route::delete('/{user_id}', 'UserController@DeleteUserData')->name('user.delete');
});
