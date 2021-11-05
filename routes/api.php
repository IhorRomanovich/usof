<?php

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

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth',
], function ($router) {
    Route::post('login', 'AuthController@login')->name('api.auth.login'); //->middleware('verified');
    Route::post('register', 'AuthController@register')->name('api.auth.register');
    Route::post('logout', 'AuthController@logout')->name('api.auth.logout');
    Route::post('refresh', 'AuthController@refresh')->name('api.auth.refresh');
    Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
    Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
    Route::post('password-reset', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('api.auth.emailresetpassword'); //->name('password.reset');
    Route::post('password-reset/{confirmation_token}', 'Auth\ResetPasswordController@apiReset')->name('api.auth.resetpassword'); //->name('newpassword.set');
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'users',
], function ($router) {
    Route::get('/', 'UserController@all')->name('user.all');
    Route::get('/{user_id}', 'UserController@userByID')->name('user.byID');
    Route::post('/', 'UserController@AddUser')->name('user.add');
    Route::post('avatar', 'UserController@UploadUserAvatar')->name('user.avatar');
    Route::patch('/{user_id}', 'UserController@UpdateUserData')->name('user.update');
    Route::delete('/{user_id}', 'UserController@DeleteUserData')->name('user.delete');
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'posts',
], function ($router) {
    Route::get('', 'PostController@all')->name('post.all');
    Route::get('/search', 'PostController@search')->name('post.search');
    Route::get('/my', 'PostController@postsByUser')->name('post.byUser');
    Route::get('/{post_id}', 'PostController@postByID')->name('post.byID');
    Route::get('/{post_id}/comments', 'PostController@commentsByPostID')->name('comments.byPostID');

    Route::post('/{post_id}/comments', 'PostController@AddComment')->name('post.comment.add');

    Route::get('/{post_id}/categories', 'PostController@categoryByPostID')->name('categories.byPostID');
    Route::get('/{post_id}/like', 'PostController@likesByPostID')->name('likes.byPostID');

    Route::post('/', 'PostController@AddPost')->name('post.add');
    Route::post('/{post_id}/like', 'PostController@AddLikeToPost')->name('like.add');
    //New func
    Route::post('/{post_id}/dislike', 'PostController@disikePost')->name('post.dislike.add');

    Route::patch('/{post_id}', 'PostController@UpdatePostData')->name('post.update');
    //New func
    Route::patch('/{post_id}/inactive', 'PostController@makePostInactive')->name('post.inactive');

    Route::delete('/{post_id}', 'PostController@DeletePostData')->name('post.delete');
    Route::delete('/{post_id}/like', 'PostController@DeletePostLike')->name('post.like.delete');

});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'categories',
], function ($router) {
    Route::get('/', 'CategoriesController@all')->name('category.all');
    Route::get('/{category_id}', 'CategoriesController@categoryByID')->name('category.byID');
    Route::get('/{category_id}/posts', 'CategoriesController@getAllPostsByCategory')->name('post.byCategory');

    Route::post('/', 'CategoriesController@addCategory')->name('category.add');

    Route::patch('/{category_id}', 'CategoriesController@updateCategoryData')->name('category.update');

    Route::delete('/{category_id}', 'CategoriesController@deleteCategoryData')->name('category.delete');

});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'comments',
], function ($router) {
    Route::get('/{comment_id}', 'CommentsController@getCommentByID')->name('comment.byID');
    Route::get('/{comment_id}/like', 'CommentsController@getLikesByCommentID')->name('comment.all');

    Route::post('/{comment_id}/like', 'CommentsController@likeComment')->name('comment.like.add');
    //New func
    Route::post('/{comment_id}/dislike', 'PostController@disikeComment')->name('comment.dislike.add');

    Route::patch('/{comment_id}', 'CommentsController@updateCommentData')->name('comment.update');

    Route::delete('/{comment_id}', 'CommentsController@deleteCommentData')->name('comment.delete');
    Route::delete('/{comment_id}/like', 'CommentsController@deleteLikeUnderCommentary')->name('comment.like.delete');

});
