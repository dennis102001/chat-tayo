<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\User;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function PHPSTORM_META\map;
use function PHPUnit\Framework\isNull;

class AuthController extends Controller
{
    // checked for responses
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

    // checked
    public function register(Request $request){
        $validate = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255' ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()]
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validate->errors(),
            ], 400);
        }

        User::create([
            'name' => $request->name, 
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
        ], 201);
    }

    // checked for responses
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

    // checked for responses
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

    // checked
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    // checked for responses
    public function update(Request $request){
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email,'. $user->id ],
            'avatar' => ['nullable', 'image', 'max:2048']
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

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
            'email' => $user->email,
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
}
