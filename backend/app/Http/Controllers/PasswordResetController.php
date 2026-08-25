<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    private BrevoMailService $brevoMailService;
    
    public function __construct(BrevoMailService $brevoMailService)
    {
        $this->brevoMailService = $brevoMailService;
    }

    public function forgot(Request $request){
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $user = User::where('email', $request->email)->first();

        if($user && $user->password){

            $token = Password::createToken($user);

            $resetLink = config('app.frontend_url')
                . '/reset-password?token=' . urlencode($token)
                . '&email=' . urlencode($user->email);

            $this->brevoMailService->send(
                $user->email,
                'Password Reset',
                "
                    <div style='font-family: Arial, sans-serif; background-color: #f5f7fb; padding: 40px 20px;'>
                        <div style='max-width: 500px; margin: auto; background-color: #ffffff; padding: 32px; border-radius: 12px;'>

                            <h2 style='margin-top: 0; color: #1f2937;'>
                                Reset Your Password
                            </h2>

                            <p style='color: #4b5563; line-height: 1.6;'>
                                We received a request to reset your password for your ChatTayo account.
                            </p>

                            <p style='color: #4b5563; line-height: 1.6;'>
                                Click the button below to create a new password.
                            </p>

                            <div style='text-align: center; margin: 30px 0;'>
                                <a
                                    href='{$resetLink}'
                                    style='
                                        display: inline-block;
                                        background-color: #2563eb;
                                        color: #ffffff;
                                        padding: 12px 24px;
                                        border-radius: 8px;
                                        text-decoration: none;
                                        font-weight: bold;
                                    '
                                >
                                    Reset Password
                                </a>
                            </div>

                            <p style='color: #6b7280; font-size: 14px; line-height: 1.6;'>
                                If you did not request a password reset, you can safely ignore this email.
                            </p>

                            <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;'>

                            <p style='color: #9ca3af; font-size: 12px;'>
                                This is an automated message from ChatTayo.
                            </p>

                        </div>
                    </div>
                "
            );
        }

        return response()->json([
            'message' => 'Reset link sent',
        ]);
    }

    public function reset(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8'
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
