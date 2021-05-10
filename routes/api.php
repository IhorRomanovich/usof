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

/*
// Route::middleware('auth')->get('/user', function (Request $request) {
//     return $request->user();
// });

// // // // // // // // // //create a user
// // // // // // // // // Route::post("/auth/register/", function () {//"/api/auth/register", function (Request $request) {
// // // // // // // // //     App\Models\User::create([
// // // // // // // // //         'login' => 'login1',
// // // // // // // // //         'password' => Hash::make('password1'),
// // // // // // // // //         'password_confirmation' => Hash::make('password1'),
// // // // // // // // //         'email' => 'lololol1@gmail.com',
// // // // // // // // //         'fullname' => 'test1 user1',
// // // // // // // // //         'profile_picture' => 'none',
// // // // // // // // //         'role' => 'user',
// // // // // // // // //     ]);
// // // // // // // // // });

// // // // // // // // // //login a user
// // // // // // // // // Route::post("/auth/login/", function () {

// // // // // // // // //     $credentials = request()->only(['login', 'password']);

// // // // // // // // //     $token = auth()->attempt($credentials);

// // // // // // // // //     return $token;
// // // // // // // // // });

// // // // // // // // // //get authenticated user
// // // // // // // // // Route::middleware('auth:api')->post('/me', function() {
// // // // // // // // //     return auth()->user();
// // // // // // // // // });


// // // // // // // // // //logout a user

//Auth::routes(['verify' => true]);
*/

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth',
], function($router) {
    Route::post('login', 'AuthController@login');//->middleware('verified');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
    Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
    Route::post('password-reset', 'Auth\ForgotPasswordController@sendResetLinkEmail');//->name('password.reset');
    Route::post('password-reset/{confirmation_token}', 'Auth\ResetPasswordController@apiReset');//->name('newpassword.set');
    //Route::post('refresh', 'AuthController@refresh');
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'users',
], function($router) {
    Route::get('', 'UserController@all');
    Route::get('{user_id}', 'UserController@userByID');
    Route::post('logout', 'UserController@logout');
    Route::post('refresh', 'UserController@refresh');
    Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
    Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
    Route::post('password-reset', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password-reset/{confirmation_token}', 'Auth\ResetPasswordController@apiReset');
    //Route::post('refresh', 'AuthController@refresh');
});
