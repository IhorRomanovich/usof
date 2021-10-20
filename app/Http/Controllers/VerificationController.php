<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class VerificationController extends Controller
{
    public function verify($user_id, Request $request) {

        if (!$request->hasValidSignature()) {
            return response()->json(['error' => 'Request has invalid signature!'], 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json(['success' => 'Email verified!'], 200);
    }

    public function resend() {
        if (auth()->user()->hasVerifiedEmail()) {
            return response()->json(['error' => 'Email already verified!'], 401);
        }

        auth()->user()->sendEmailVerificationNotification();

        return response()->json(['Email verification link sent on your email id!']);

    }

    protected function guard() {
        return Auth::guard();
    }

}
