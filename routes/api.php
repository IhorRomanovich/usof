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

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

//create user route
Route::get("/user-create", function (Request $request) {//"/api/auth/register", function (Request $request) {
    App\Models\User::create([
        'login' => 'login',
        'password' => Hash::make('password'),
        'password_confirmation' => Hash::make('password'),
        'email' => 'lololol@gmail.com',
        'fullname' => 'test user',
        'profile_picture' => 'none',
        'role' => 'user',
    ]);
});

//login a user

//logout a user



