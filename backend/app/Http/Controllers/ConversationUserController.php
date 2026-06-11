<?php

namespace App\Http\Controllers;

use App\Models\ConversationUser;
use Illuminate\Http\Request;

class ConversationUserController extends Controller
{
    public function deleteConversation($conversation_id){
        ConversationUser::where('user_id', auth()->id())
            ->where('conversation_id', $conversation_id)
            ->update([
                'cleared_at' => now()
            ]);

        return response()->json([
            'message' => 'Conversation deleted successfully'
        ], 200);
    }
}
