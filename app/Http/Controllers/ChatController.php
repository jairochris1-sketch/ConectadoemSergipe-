<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Report;
use App\Models\ReportNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $isBlocking = false;
        $isBlockedBy = false;

        if ($activePartner) {
            $isBlocking = Auth::user()->isBlocking($activePartner->id);
            $isBlockedBy = Auth::user()->isBlockedBy($activePartner->id);

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

        return view('chat.index', compact('conversations', 'activePartner', 'messages', 'isBlocking', 'isBlockedBy'));
    }

    public static function detectProhibitedChatContent(string $content): ?string
    {
        // Detecção de links e sites externos
        $linkPatterns = [
            '/\b(?:https?:\/\/|ftp:\/\/|www\.)\S+/i',
            '/\b[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.(?:com|br|net|org|io|app|site|online|me|link|top|xyz|gov|edu|info|biz|co|us|uk|tech|store|shop|tv|cc|to|gg|page|club|live|dev)(?:\/[^\s]*)?/i',
            '/[a-z0-9_\-\.]+\s*(?:\.|\(dot\)|\bponto\b)\s*(?:com|br|net|org|site|io|app)\b/i',
        ];

        foreach ($linkPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return 'Não é permitido o envio de links ou sites externos pelo chat.';
            }
        }

        // Detecção de contatos telefônicos e WhatsApp
        if (preg_match('/(?:whats(?:app)?|zap|fone|tel(?:efone)?|cel(?:ular)?|contato|chama|liga)\D{0,8}\d{4,}/i', $content)) {
            return 'Não é permitido o envio de números de telefone ou contatos pelo chat.';
        }

        $phonePatterns = [
            '/(?:\+?55\s*)?(?:\(?0?[1-9]{2}\)?\s*)?9\s*\d{4}[-\s\.]?\d{4}/',
            '/(?:\(?0?[1-9]{2}\)?\s*)?[2-8]\d{3}[-\s\.]?\d{4}/',
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return 'Não é permitido o envio de números de telefone ou contatos pelo chat.';
            }
        }

        if (preg_match('/(?:\d[\s\.\-_,\(\)\/]*){8,}/', $content)) {
            return 'Não é permitido o envio de números de telefone ou contatos pelo chat.';
        }

        return null;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:2000',
            'ad_id' => 'nullable|exists:ads,id',
        ]);

        abort_if((int) $request->receiver_id === Auth::id(), 422, 'Você não pode enviar uma mensagem para si mesmo.');

        if (Auth::user()->isBlocking($request->receiver_id)) {
            return back()->withErrors(['content' => 'Você bloqueou este usuário. Desbloqueie-o para enviar mensagens.'])->withInput();
        }

        if (Auth::user()->isBlockedBy($request->receiver_id)) {
            return back()->withErrors(['content' => 'Não é possível enviar mensagens para este usuário no momento.'])->withInput();
        }

        if ($prohibitedReason = self::detectProhibitedChatContent($request->content)) {
            return back()->withErrors(['content' => $prohibitedReason])->withInput();
        }

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

    public function blockUser(Request $request, User $user)
    {
        abort_if($user->id === Auth::id(), 422, 'Você não pode bloquear a si mesmo.');

        Auth::user()->blockedUsers()->syncWithoutDetaching([$user->id]);

        return redirect()->route('chat.index', ['with' => $user->id])->with('success', 'Usuário bloqueado com sucesso.');
    }

    public function unblockUser(Request $request, User $user)
    {
        Auth::user()->blockedUsers()->detach($user->id);

        return redirect()->route('chat.index', ['with' => $user->id])->with('success', 'Usuário desbloqueado com sucesso.');
    }

    public function reportUser(Request $request, User $user)
    {
        abort_if($user->id === Auth::id(), 422, 'Você não pode denunciar a si mesmo.');

        $validated = $request->validate([
            'reason' => 'required|string|max:50',
            'details' => 'nullable|string|max:1000',
            'block_too' => 'nullable|boolean',
        ]);

        Report::create([
            'public_id' => (string) Str::uuid(),
            'advertiser_id' => $user->id,
            'reporter_user_id' => Auth::id(),
            'subject_type' => 'user_chat',
            'ad_title_snapshot' => 'Chat com ' . $user->name,
            'ad_module_snapshot' => 'chat',
            'reason' => $validated['reason'],
            'severity' => in_array($validated['reason'], ['scam', 'offensive'], true) ? 'high' : 'medium',
            'details' => $validated['details'] ?? null,
            'wants_notification' => true,
            'status' => 'open',
        ]);

        if ($request->boolean('block_too')) {
            Auth::user()->blockedUsers()->syncWithoutDetaching([$user->id]);
        }

        return redirect()->route('chat.index', ['with' => $user->id])->with('success', 'Denúncia enviada com sucesso para análise da moderação.');
    }
}
