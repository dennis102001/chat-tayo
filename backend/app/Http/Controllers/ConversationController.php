<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function getConversations(Request $request){
        $conversationsList = Conversation::query()
            ->join('conversation_users', function ($join){
                    $join->on(
                        'conversations.id', '=', 'conversation_users.conversation_id'
                    )->where(
                        'conversation_users.user_id',
                        auth()->id()
                    );
                }

            )
            ->with(['users:id,name,avatar', 'lastMessage:messages.id,messages.conversation_id,user_id,message,created_at'])
            ->where(function ($q) {
                $q->whereNull('conversation_users.cleared_at')
                    ->orWhereColumn(
                        'conversations.last_message_at',
                        '>',
                        'conversation_users.cleared_at'
                    );
            })
            ->select('conversations.id', 'conversations.last_message_at')
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('user_id', '!=', auth()->id())
                    ->where('is_read', false);
                }])
            ->whereNotNull('last_message_at')
            ->orderByDesc('last_message_at')
            ->get();

        return response()->json([
            'success' => true,
            'conversations_list' => $conversationsList
        ], 200);
    }

    public function openConversation(Request $request) {
        $otherUser = User::findOrFail($request->other_user_id);

        $conversation = Conversation::whereHas('users', function ($q) use ($request) {
            $q->where('user_id', auth()->id());
        })->whereHas('users', function ($q) use ($otherUser) {
            $q->where('user_id', $otherUser->id);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create();

            ConversationUser::insert([
                ['conversation_id' => $conversation->id, 'user_id' => auth()->id()],
                ['conversation_id' => $conversation->id, 'user_id' => $otherUser->id],
            ]);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('user_id', $otherUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversationClearedAt = ConversationUser::where('conversation_id', $conversation->id)
            ->where('user_id', auth()->id())
            ->value('cleared_at');

        $messages = $conversation->messages()
            ->when(
                $conversationClearedAt,
                fn ($q) => $q->where('messages.created_at', '>', $conversationClearedAt)
            )
            ->select('id', 'user_id', 'message', 'is_read', 'created_at')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,

            'conversation_id' => $conversation->id,

            'messages' => [
                'last_page' => $messages->lastPage(),
                'data' => $messages->items(),
            ],

            'otherUser' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'avatar_url' => $otherUser->avatar_url
            ],
            
        ], 200);
    }
}