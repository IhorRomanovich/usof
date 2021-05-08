<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'login' => 'required|string|between:6,30',
            'password' => 'required|string|between:8,30',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $token_validity = 24 * 60;

        $this->guard()->factory()-setTTL($token_validity);

        if (!$token = $this->guard()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|between:6,30|unique:users',
            'password' => 'required|string|between:8,30',
            'password_confirmation' => 'required|string|between:8,30|same:password',
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function logout() {
        $this->guard()->logout();
        return response()->json(['message' => 'User logged out successfully']);
    }

    public function refresh() {
        return $this->respondWithToken($this->guard()->refresh());
    }

    protected function respondWithToken($token) {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'token' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    protected function guard() {
        return Auth::guard();
    }
}
