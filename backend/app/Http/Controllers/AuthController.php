<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Nette\Utils\Json;

use function PHPSTORM_META\map;
use function PHPUnit\Framework\isNull;

class AuthController extends Controller
{
    private BrevoMailService $brevoMailService;

    public function __construct(BrevoMailService $brevoMailService)
    {
        $this->brevoMailService = $brevoMailService;
    }

    public function store(Request $request){
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if(!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        if(!$user->email_verified_at){
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email before logging in'
            ], 403);
        }

        $user->tokens()->delete();

        $expiresAt = $request->boolean('remember')
            ? now()->addDays(30)
            : now()->addHours(2);
                
        $token = $user->createToken('spa-token', ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url
            ],
            'token' => $token
        ]);
    }

    public function register(Request $request){
        $validate = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255' ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()]
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validate->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (!$user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered but has not been verified.',
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'The email has already been registered.',
            ], 409);
        }

        $user = User::create([
            'name' => $request->name, 
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // send an email verification
        $this->sendEmailVerification($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
        ], 201);
    }

    public function getAuthUser(Request $request){
        $user = auth()->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar_url,
                'has_password' => !is_null($user->password)
            ]
        ], 200);
    }

    public function getSearchResults(Request $request){
        $name = trim($request->name);

        if(!$name){
            return response()->json([
                'success' => true,
                'matched_users' => []
            ]);
        }

        $matchedUsers = User::select('id','name','avatar')
            ->whereRaw('name LIKE ?', ['%'.$name.'%'])
            ->where('id', '!=', auth()->id())
            ->get();

        return response()->json([
            'success' => true,
            'matched_users' => $matchedUsers
        ]);
    }

    public function destroy(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function update(Request $request){
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048']
        ]);

        $user->name = $data['name'];

        if ($request->hasFile('avatar')) {
            if($user->avatar){
                FacadesStorage::disk('public')->delete($user->avatar);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        if($request->has('remove_avatar') && $request->remove_avatar){
            if($user->avatar){
                FacadesStorage::disk('public')->delete($user->avatar);
            }
            
            $user->avatar = null;
        }

        $user->save();

        return response()->json([
            'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'name' => $user->name,
        ]);
    }

    public function changePassword(Request $request){
        $user = auth()->user();

        if($user->password){
            $request->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed']
            ]);
        }
        else{
            $request->validate([
                'new_password' => ['required', 'string', 'min:8', 'confirmed']
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        $user->tokens()
            ->where('id', '!=', $user->currentAccessToken()->id)
            ->delete();

        return response()->json([
            'message' => 'Password updated'
        ], 200);
    }

    public function delete(Request $request) {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->name = 'Deleted User';
            $user->email = $user->email . '_deleted_' . $user->id;
            $user->save();

            $user->tokens()->delete();

            $user->delete();
        });

        return response()->json([
            'message' => 'Account deleted successfully'
        ], 200);
    }

    public function verifyEmail(Request $request){
        $validate = Validator::make($request->all(), [
            'token' => ['required'], 
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validate->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.'
            ], 200);
        }

        $verificationToken = DB::table('email_verification_tokens')->where('user_id', $user->id)->first();

        if( !$verificationToken || 
            now()->greaterThan($verificationToken->expires_at) || 
            !hash_equals($verificationToken->token, hash('sha256', $request->token)) 
        ){
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification link.'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->save();

        DB::table('email_verification_tokens')->where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.'
        ], 200);
    }

    public function resendVerification(Request $request){
        $validate = Validator::make($request->all(), [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validate->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'If an unverified account exists with this email, a verification email has been sent.'
            ], 200);
        }
        
        $this->sendEmailVerification($user);

        return response()->json([
            'success' => true, 
            'message' => 'If an unverified account exists with this email, a verification email has been sent.'
        ], 200);
    }

    private function sendEmailVerification($user){
        $token = Str::random(64);

        //delete existing token for the user
        DB::table('email_verification_tokens')->where('user_id', $user->id)->delete();

        //save token in db
        DB::table('email_verification_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $verificationLink = config('app.frontend_url')
            . '/verify-email?token=' . urlencode($token)
            . '&email=' . urlencode($user->email);

        $this->brevoMailService->send(
            $user->email, 
            'Verify Your ChatTayo Account',
            "
                <div style='font-family: Arial, sans-serif; background-color: #f5f7fb; padding: 40px 20px;'>
                    <div style='max-width: 500px; margin: auto; background-color: #ffffff; padding: 32px; border-radius: 12px;'>

                        <h2 style='margin-top: 0; color: #1f2937;'>
                            Verify Your Email
                        </h2>

                        <p style='color: #4b5563; line-height: 1.6;'>
                            Hi {$user->name},
                        </p>

                        <p style='color: #4b5563; line-height: 1.6;'>
                            Thank you for creating a ChatTayo account. Please verify your email address to complete your registration.
                        </p>

                        <div style='text-align: center; margin: 30px 0;'>
                            <a
                                href='{$verificationLink}'
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
                                Verify Email
                            </a>
                        </div>

                        <p style='color: #6b7280; font-size: 14px; line-height: 1.6;'>
                            This verification link is required to activate your account.
                            If you did not create a ChatTayo account, you can safely ignore this email.
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
}
