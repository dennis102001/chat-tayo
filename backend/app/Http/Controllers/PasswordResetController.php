<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function forgot(Request $request){
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $user = User::where('email', $request->email)->first();

        if($user && $user->password){
            Password::sendResetLink($request->only('email'));
        }

        return response()->json([
            'message' => 'Reset link sent',
        ]);
    }

    public function reset(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->password) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->tokens()->delete(); // logout all devices
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message'=>'Password updated'])
            : response()->json(['message'=>'Invalid token'],400);
    }

    public function showResetForm(string $token, Request $request){
        return redirect()->away(
            env('FRONTEND_URL') 
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($request->email)
        );
    }
}
