<?php

namespace App\Http\Controllers;

use App\Models\User as User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function all(Request $request)
    {
        $users = User::all();
        return response()->json($users);
    }

    public function userByID($user_id, Request $request)
    {
        $validator = Validator::make(["user_id" => $user_id], [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $user = User::where('id', '=', $user_id)->get()->first();
        //$user['fullname'] = "Gay Orgy";
        // $user->fill(['fullname' => "Gay Orgies",]);
        // $user->save();
        return response()->json($user);
    }

    public function AddUser(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|between:5,30|unique:users',
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
                ['password' => Hash::make($request->password)]
            ));
            $user->sendEmailVerificationNotification();

            return response()->json(['message' => 'User created successfully', 'user' => $user]);
        } else {
            return response()->json(["Error" => "Permission denied"], 403);
        }
    }

    public function UploadUserAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user_avatar = $validator->validated()['avatar'];

        $imageName = time() . '.' . $user_avatar->extension();

        $user_avatar->move(public_path('storage/users'), $imageName);

        $me = auth()->user();
        $user = User::where('id', '=', $me->id)->get()->first();

        $path = 'users/' . $imageName;
        $user->fill(['avatar' => $path]);

        $user->save();

        return response()->json(['message' => 'User avatar updated successfully']);

    }

    public function UpdateUserData($user_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|between:5,30|unique:users',
            'password' => 'string|between:8,30',
            'password_confirmation' => 'string|between:8,30|same:password',
            'email' => 'email|unique:users',
            'fullname' => 'string|between:8,30',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $me = auth()->user();
        if ($me->id != $user_id) {
            if (!$me . hasRole('admin')) {
                return response()->json(['Error' => 'Permission denied', 403]);
            }
        }
        //if ($me.hasRole('admin'))
        $user = User::where('id', '=', $user_id)->get()->first();
        //$user['fullname'] = "Gay Orgy";

        $newUserData = $validator->validated();

        if (array_key_exists('email', $validator->validated())) {
            $newUserData = array_merge($newUserData, ['email_verified_at' => null]);
            $user->email_verified_at = null;
        }

        $user->fill(array_merge($newUserData, ['password' => Hash::make($request->password)]));
        //$user->fill(['fullname' => "Gay Orgies",]);

        $user->save();

        if (array_key_exists('email', $validator->validated())) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json(['message' => 'User data updated successfully', 'user' => $user]);
    }

    public function DeleteUserData($user_id, Request $request)
    {
        $validator = Validator::make(["user_id" => $user_id], [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $me = auth()->user();
        if ($me->id != $user_id) {
            if (!$me . hasRole('admin')) {
                return response()->json(['Error' => 'Permission denied', 403]);
            }
        }

        $user = User::where('id', '=', $user_id)->get()->first();
        //$user['fullname'] = "Gay Orgy";
        $user->fill(['can_login' => false]);
        $user->save();

        // get current user
        ///$user = Auth::user();

        // logout user
        //$userToLogout = User::find($user_id);
        //$this->guard()->setUser($userToLogout);
        //$this->guard()->logout();
        //Auth::setUser($userToLogout);
        //Auth::logout();

        //$this->guard()->setUser($me);
        // if (!$me.hasRole('admin')) {
        //     Auth::logout(); // set again current user
        // }

        //DB::table('users')->delete($user_id);
        //DB::table('users')->delete($user_id);

    }

    protected function guard()
    {
        return Auth::guard('api');
    }
}
