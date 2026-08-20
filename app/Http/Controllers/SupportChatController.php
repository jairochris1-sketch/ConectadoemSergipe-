<?php

namespace App\Http\Controllers;

use App\Models\SupportDepartment;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportChatController extends Controller
{
    public function getDepartments()
    {
        $departments = SupportDepartment::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'description', 'icon']);

        $waitingCount = SupportTicket::where('status', 'waiting')->count();

        return response()->json([
            'departments' => $departments,
            'waiting_count' => $waitingCount,
            'user' => Auth::check() ? [
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'phone' => Auth::user()->whatsapp ?: Auth::user()->phone,
            ] : null,
        ]);
    }

    public function startTicket(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'department_id' => 'required|exists:support_departments,id',
            'subject' => 'nullable|string|max:150',
            'initial_message' => 'required|string|max:2000',
            'current_page_url' => 'nullable|string|max:255',
        ]);

        $ticket = SupportTicket::create([
            'protocol' => SupportTicket::generateProtocol(),
            'user_id' => Auth::id(),
            'guest_name' => $validated['name'],
            'guest_email' => $validated['email'],
            'guest_phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'subject' => $validated['subject'] ?? 'Atendimento Online',
            'status' => 'waiting',
            'current_page_url' => $validated['current_page_url'] ?? null,
            'started_at' => now(),
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'client',
            'sender_id' => Auth::id(),
            'sender_name' => $validated['name'],
            'message' => $validated['initial_message'],
            'is_internal_note' => false,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'protocol' => $ticket->protocol,
                'status' => $ticket->status,
                'queue_position' => $ticket->queue_position,
            ],
            'message' => 'Você entrou na fila de atendimento com sucesso!',
        ]);
    }

    public function getTicketStatus(SupportTicket $ticket)
    {
        $ticket->load(['department', 'agent']);

        $messages = $ticket->publicMessages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_name ?: ($msg->sender_type === 'agent' ? 'Atendente' : 'Você'),
                    'message' => $msg->message,
                    'created_at' => $msg->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'ticket' => [
                'id' => $ticket->id,
                'protocol' => $ticket->protocol,
                'status' => $ticket->status,
                'queue_position' => $ticket->queue_position,
                'department_name' => $ticket->department?->name,
                'agent' => $ticket->agent ? [
                    'name' => $ticket->agent->name,
                    'avatar' => $ticket->agent->avatar ? asset($ticket->agent->avatar) : null,
                ] : null,
                'rating' => $ticket->rating,
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, SupportTicket $ticket)
    {
        if ($ticket->status === 'closed') {
            return response()->json(['error' => 'Este atendimento já foi encerrado.'], 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'client',
            'sender_id' => Auth::id(),
            'sender_name' => $ticket->guest_name,
            'message' => $validated['message'],
            'is_internal_note' => false,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_type' => 'client',
                'sender_name' => $message->sender_name,
                'message' => $message->message,
                'created_at' => $message->created_at->format('H:i'),
            ],
        ]);
    }

    public function closeTicket(SupportTicket $ticket)
    {
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'message' => 'O atendimento foi finalizado pelo cliente.',
            'is_internal_note' => false,
            'is_read' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Atendimento encerrado.']);
    }

    public function rateTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500',
        ]);

        $ticket->update([
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Obrigado pela sua avaliação!']);
    }
}
