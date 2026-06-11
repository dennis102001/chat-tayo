<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ConversationUserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PasswordResetController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

Route::middleware(['auth:sanctum', 'token.sliding'])->group(function() {
    Route::controller(AuthController::class)->group(function(){
        Route::get('/user', 'getAuthUser');
        Route::get('/search', 'getSearchResults');
        Route::post('/change-password', 'changePassword');
        Route::post('/logout', 'destroy');
        Route::post('/update-account', 'update');
    });

    Route::controller(MessageController::class)->group(function(){
        Route::post('/send-message', 'sendMessage');
        Route::post('/mark-as-read', 'markAsRead');
        Route::delete('/delete-message/{id}', 'destroy');
    });

    Route::controller(ConversationController::class)->group(function(){
        Route::get('/conversations-list', 'getConversations');
        Route::get('/open-conversation', 'openConversation');
    });

    Route::controller(ConversationUserController::class)->group(function(){
        Route::delete('/delete-conversation/{id}', 'deleteConversation');
    });
});

Route::controller(AuthController::class)->group(function() {
    Route::post('/login', 'store');
    Route::post('/register', 'register');
});

Route::controller(PasswordResetController::class)->group(function(){
    Route::post('/forgot-password', 'forgot');
    Route::post('/reset-password', 'reset');
    Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
}); 

Route::get('/auth/google/redirect', function(Request $request){
    return Socialite::driver("google")->stateless()->redirect();
});

Route::get('/auth/google/callback', function(Request $request){
    $googleUser = Socialite::driver("google")->stateless()->user();

    $user = User::where('email', $googleUser->email)->first();

    if(!$user){
        $user = User::create(
            [
                'google_id' => $googleUser->id,
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => null,
            ]
        );
    }
    else if (!$user->google_id) {
        $user->update([
            'google_id' => $googleUser->id
        ]);
    }

    $user->tokens()->delete();
            
    $token = $user->createToken('spa-token', ['*'], now()->addHours(2))->plainTextToken;

    return redirect()->away(
        env('FRONTEND_URL') . '/auth/callback?token=' . $token
    );
});
