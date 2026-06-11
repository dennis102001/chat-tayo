<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\ConversationUser;
use GrahamCampbell\ResultType\Success;

class MessageController extends Controller
{
    // checked
    public function markAsRead(Request $request){
        Message::where('conversation_id', $request->conversation_id)
            ->where('user_id', '!=', auth()->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json([
            'success' => true,
        ], 200);
    }

    // checked for responses
    public function sendMessage(Request $request){
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string'
        ]);

        $isUserInConvo = ConversationUser::where('conversation_id', $request->conversation_id)
            ->where('user_id', auth()->id())
            ->exists();

        if(!$isUserInConvo){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        $message = Message::create([
            'user_id' => auth()->id(),
            'conversation_id' => $request->conversation_id,
            'message' => $request->message
        ]);

        $message->refresh();

        Conversation::where('id', $request->conversation_id)->update([
            'last_message_at' => $message->created_at
        ]);

        $receiver = Conversation::findOrFail($request->conversation_id)
            ->users()
            ->where('user_id', '!=', auth()->id())
            ->first();

        $message->sender = auth()->user();
        $message->receiver = $receiver;

        broadcast(new MessageSent($message));
        
        return response()->json([
            'success' => true,
            'message_sent' => $message,
        ], 201);
    }

    // checked for responses
    public function destroy($id){
        $message = Message::findOrFail($id);
        $conversationId = $message->conversation_id;

        $receiver = Conversation::findOrFail($conversationId)
            ->users()
            ->where('user_id', '!=', auth()->id())
            ->first();

        $deletedMsg = [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'receiver_id' => $receiver->id
        ];
        $message->delete();
        
        $newLastMessage = Message::where('conversation_id', $conversationId)
            ->latest()
            ->first();

        $deletedMsg['new_last_message'] = $newLastMessage
            ? [
                'message' => $newLastMessage->message,
                'created_at' => $newLastMessage->created_at
            ]
            : null; 

        Conversation::where('id', $conversationId)->update([
            'last_message_at' => $newLastMessage->created_at ?? null
        ]);

        broadcast(new MessageDeleted($deletedMsg));

        return response()->json([
            'success' => true,
            'deleted_message' => $deletedMsg
        ], 200);
    }
}
