<?php

namespace App\Http\Controllers;

use App\Models\SupportCannedResponse;
use App\Models\SupportDepartment;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSupportController extends Controller
{
    public function index(Request $request)
    {
        $currentAgent = Auth::user();
        $departments = SupportDepartment::where('is_active', true)->orderBy('sort_order')->get();
        $agents = User::where('role', 'admin')->get(['id', 'name', 'email', 'avatar']);
        $cannedResponses = SupportCannedResponse::orderBy('shortcut')->get();

        $waitingTickets = SupportTicket::with(['department', 'user'])
            ->where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->get();

        $myActiveTickets = SupportTicket::with(['department', 'user'])
            ->where('status', 'in_progress')
            ->where('agent_id', $currentAgent->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $otherActiveTickets = SupportTicket::with(['department', 'agent', 'user'])
            ->where('status', 'in_progress')
            ->where('agent_id', '!=', $currentAgent->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $stats = [
            'waiting' => $waitingTickets->count(),
            'my_active' => $myActiveTickets->count(),
            'total_active' => $myActiveTickets->count() + $otherActiveTickets->count(),
            'closed_today' => SupportTicket::where('status', 'closed')
                ->whereDate('closed_at', today())
                ->count(),
            'avg_rating' => round(SupportTicket::whereNotNull('rating')->avg('rating') ?: 5.0, 1),
        ];

        return view('admin.support.index', compact(
            'waitingTickets',
            'myActiveTickets',
            'otherActiveTickets',
            'departments',
            'agents',
            'cannedResponses',
            'stats',
            'currentAgent'
        ));
    }

    public function getQueueData(Request $request)
    {
        $currentAgentId = Auth::id();

        $waiting = SupportTicket::with(['department'])
            ->where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'protocol' => $t->protocol,
                'client_name' => $t->guest_name,
                'department_name' => $t->department?->name ?? 'Geral',
                'wait_minutes' => $t->created_at->diffInMinutes(now()),
                'wait_time' => $t->created_at->diffForHumans(null, true),
                'initial_message' => $t->messages()->first()?->message ?? 'Sem mensagem inicial',
            ]);

        $myTickets = SupportTicket::with(['department'])
            ->where('status', 'in_progress')
            ->where('agent_id', $currentAgentId)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'protocol' => $t->protocol,
                'client_name' => $t->guest_name,
                'department_name' => $t->department?->name ?? 'Geral',
                'unread_count' => $t->messages()->where('sender_type', 'client')->where('is_read', false)->count(),
                'last_message' => $t->messages()->latest()->first()?->message ?? '',
                'last_activity' => $t->updated_at->format('H:i'),
            ]);

        $otherTickets = SupportTicket::with(['department', 'agent'])
            ->where('status', 'in_progress')
            ->where('agent_id', '!=', $currentAgentId)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'protocol' => $t->protocol,
                'client_name' => $t->guest_name,
                'agent_name' => $t->agent?->name ?? 'Outro Atendente',
                'department_name' => $t->department?->name ?? 'Geral',
            ]);

        return response()->json([
            'waiting' => $waiting,
            'my_tickets' => $myTickets,
            'other_tickets' => $otherTickets,
            'counts' => [
                'waiting' => $waiting->count(),
                'my_active' => $myTickets->count(),
            ],
        ]);
    }

    public function getTicketDetails(SupportTicket $ticket)
    {
        $ticket->load(['department', 'agent', 'user']);

        // Marcar mensagens do cliente como lidas pelo atendente
        $ticket->messages()->where('sender_type', 'client')->where('is_read', false)->update(['is_read' => true]);

        $messages = $ticket->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'sender_type' => $msg->sender_type,
                'sender_name' => $msg->sender_name ?: ($msg->sender_type === 'agent' ? 'Atendente' : 'Cliente'),
                'message' => $msg->message,
                'is_internal_note' => (bool) $msg->is_internal_note,
                'created_at' => $msg->created_at->format('d/m H:i'),
            ];
        });

        return response()->json([
            'ticket' => [
                'id' => $ticket->id,
                'protocol' => $ticket->protocol,
                'client_name' => $ticket->guest_name,
                'client_email' => $ticket->guest_email,
                'client_phone' => $ticket->guest_phone,
                'department_name' => $ticket->department?->name,
                'department_id' => $ticket->department_id,
                'agent_id' => $ticket->agent_id,
                'agent_name' => $ticket->agent?->name,
                'status' => $ticket->status,
                'current_page_url' => $ticket->current_page_url,
                'started_at' => $ticket->started_at?->format('d/m/Y H:i'),
                'rating' => $ticket->rating,
                'feedback' => $ticket->feedback,
            ],
            'messages' => $messages,
        ]);
    }

    public function claimTicket(SupportTicket $ticket)
    {
        $agent = Auth::user();

        $ticket->update([
            'status' => 'in_progress',
            'agent_id' => $agent->id,
            'started_at' => $ticket->started_at ?: now(),
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'message' => "O atendente {$agent->name} entrou no chat.",
            'is_internal_note' => false,
            'is_read' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Você assumiu o chamado {$ticket->protocol}.",
            'ticket_id' => $ticket->id,
        ]);
    }

    public function transferTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'target_agent_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:support_departments,id',
            'note' => 'nullable|string|max:500',
        ]);

        $updates = [];
        $targetAgent = null;

        if (!empty($validated['target_agent_id'])) {
            $targetAgent = User::find($validated['target_agent_id']);
            $updates['agent_id'] = $targetAgent->id;
            $updates['status'] = 'in_progress';
        }

        if (!empty($validated['department_id'])) {
            $updates['department_id'] = $validated['department_id'];
        }

        $ticket->update($updates);

        if (!empty($validated['note'])) {
            SupportTicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'agent',
                'sender_id' => Auth::id(),
                'sender_name' => Auth::user()->name,
                'message' => 'Nota de transferência: ' . $validated['note'],
                'is_internal_note' => true,
                'is_read' => true,
            ]);
        }

        $systemMsg = $targetAgent
            ? "O atendimento foi transferido para o atendente {$targetAgent->name}."
            : "O atendimento foi transferido para outro setor.";

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'message' => $systemMsg,
            'is_internal_note' => false,
            'is_read' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Chamado transferido com sucesso.']);
    }

    public function sendMessage(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'is_internal_note' => 'nullable|boolean',
        ]);

        $agent = Auth::user();
        $isInternal = (bool) ($validated['is_internal_note'] ?? false);

        // Se o ticket ainda estava esperando e o atendente enviou mensagem, assume o ticket
        if ($ticket->status === 'waiting') {
            $ticket->update([
                'status' => 'in_progress',
                'agent_id' => $agent->id,
            ]);
        }

        $message = SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'agent',
            'sender_id' => $agent->id,
            'sender_name' => $agent->name,
            'message' => $validated['message'],
            'is_internal_note' => $isInternal,
            'is_read' => false,
        ]);

        $ticket->touch();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_type' => 'agent',
                'sender_name' => $message->sender_name,
                'message' => $message->message,
                'is_internal_note' => $message->is_internal_note,
                'created_at' => $message->created_at->format('d/m H:i'),
            ],
        ]);
    }

    public function closeTicket(SupportTicket $ticket)
    {
        $agent = Auth::user();

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'message' => "Atendimento finalizado pelo atendente {$agent->name}.",
            'is_internal_note' => false,
            'is_read' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Atendimento finalizado com sucesso.']);
    }

    public function cannedResponses()
    {
        return response()->json(SupportCannedResponse::orderBy('shortcut')->get());
    }

    public function storeCannedResponse(Request $request)
    {
        $validated = $request->validate([
            'shortcut' => 'required|string|max:50|unique:support_canned_responses,shortcut',
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
        ]);

        $shortcut = str_starts_with($validated['shortcut'], '/') ? $validated['shortcut'] : '/' . $validated['shortcut'];

        $resp = SupportCannedResponse::create([
            'shortcut' => $shortcut,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return response()->json(['success' => true, 'canned' => $resp]);
    }
}
