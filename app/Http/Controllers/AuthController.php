<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        /*$this->middleware('confirmedMail', ['except' => ['register']]);*/
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

        $user_verified_email = User::select('email_verified_at')->where('email', $request->all()['email'])->first();

        if (is_null($user_verified_email->email_verified_at)) {
            return response()->json(
                ["Unauthorized. Email confirmation required!" => "Email is not verified. Please verify your email first!" ], 
                401);
        }

        $token_validity = 24 * 60;

        $this->guard()->factory()->setTTL($token_validity);

        $token = $this->guard()->attempt($validator->validated());
        if (!$token) {
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

        $user_data = $validator->validated();
        unset($user_data['password_confirmation']);

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => Hash::make($request->password), 'role' => 'user']
        ))->sendEmailVerificationNotification();

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
            'token_validity' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    protected function guard() {
        return Auth::guard();
    }
}
