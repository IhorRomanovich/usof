<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\User as User;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function all(Request $request) {
        $users = User::all();
        return response()->json($users);
    }

    public function userByID($user_id, Request $request) {
        $validator = Validator::make(["user_id" => $user_id], [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $user = User::where('id', '=', $user_id)->get();
        return response()->json($user);
    }

    public function AddUser($user_id, Request $request) {

    }

    public function UploarUserAvatar(Request $request) {

    }

    public function UpdateUserData($user_id, Request $request) {

    }

    public function DeleteUserData($user_id, Request $request) {

    }

    protected function guard() {
        return Auth::guard('api');
    }
}
