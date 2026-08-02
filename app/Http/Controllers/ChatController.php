<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\ReportNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $allMessages = Message::with(['sender', 'receiver', 'ad'])
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $partnerIds = $allMessages
            ->map(fn ($message) => $message->sender_id === $userId ? $message->receiver_id : $message->sender_id)
            ->unique()
            ->values();
        $partners = User::whereIn('id', $partnerIds)->get()->keyBy('id');
        $conversations = $partnerIds->map(function ($partnerId) use ($allMessages, $partners, $userId) {
            $partner = $partners->get($partnerId);
            if (! $partner) {
                return null;
            }

            return [
                'user' => $partner,
                'latest' => $allMessages->first(fn ($message) => in_array($partnerId, [$message->sender_id, $message->receiver_id], true)),
                'unread' => $allMessages
                    ->where('sender_id', $partnerId)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count(),
            ];
        })->filter()->values();

        $requestedPartnerId = $request->integer('with');
        $activePartner = $requestedPartnerId
            ? User::whereKey($requestedPartnerId)->where('id', '!=', $userId)->first()
            : null;
        $activePartner ??= $conversations->first()['user'] ?? null;

        $messages = collect();
        if ($activePartner) {
            Message::where('sender_id', $activePartner->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = Message::with('ad')
                ->where(function ($query) use ($userId, $activePartner) {
                    $query->where('sender_id', $userId)->where('receiver_id', $activePartner->id);
                })
                ->orWhere(function ($query) use ($userId, $activePartner) {
                    $query->where('sender_id', $activePartner->id)->where('receiver_id', $userId);
                })
                ->orderBy('created_at')
                ->get();
        }

        return view('chat.index', compact('conversations', 'activePartner', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:2000',
            'ad_id' => 'nullable|exists:ads,id',
        ]);

        abort_if((int) $request->receiver_id === Auth::id(), 422, 'Você não pode enviar uma mensagem para si mesmo.');

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'ad_id' => $request->ad_id,
            'content' => $request->content,
            'is_read' => false,
            'created_at' => now(),
        ]);

        ReportNotification::sendTo($message->receiver_id, [
            'kind' => 'message_received',
            'message' => Auth::user()->name.' enviou uma nova mensagem para você.',
            'action_url' => route('chat.index', ['with' => Auth::id()], false),
        ]);

        return redirect()->route('chat.index', ['with' => $message->receiver_id])->with('success', 'Mensagem enviada!');
    }
}
