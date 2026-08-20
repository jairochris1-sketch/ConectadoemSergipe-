<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public const AD_REASONS = [
        'sold' => 'Já foi vendido',
        'suspicious_price' => 'Preço suspeito',
        'wrong_location' => 'Localização incorreta',
        'wrong_category' => 'Categoria errada',
        'spam' => 'Spam ou propaganda',
        'prohibited' => 'Conteúdo proibido',
        'scam' => 'Possível golpe',
        'false_information' => 'Informações falsas',
        'misleading_photos' => 'Fotos enganosas',
        'other' => 'Outro',
    ];

    public const SERVICE_REASONS = [
        'unavailable_service' => 'Serviço não está mais disponível',
        'service_contact' => 'Problema no atendimento ou contato',
        'wrong_location' => 'Localização ou região incorreta',
        'wrong_category' => 'Categoria do serviço errada',
        'spam' => 'Spam ou propaganda',
        'prohibited' => 'Conteúdo proibido',
        'scam' => 'Possível golpe',
        'false_information' => 'Informações falsas sobre o serviço',
        'misleading_photos' => 'Fotos enganosas',
        'other' => 'Outro',
    ];

    public const STORE_REASONS = [
        'closed_store' => 'Loja não existe ou encerrou as atividades',
        'service_contact' => 'Problema no atendimento ou contato',
        'wrong_location' => 'Localização incorreta',
        'wrong_category' => 'Categoria da loja incorreta',
        'spam' => 'Spam ou propaganda',
        'prohibited' => 'Conteúdo proibido',
        'scam' => 'Possível golpe',
        'false_information' => 'Informações falsas sobre a loja',
        'misleading_photos' => 'Fotos enganosas',
        'other' => 'Outro',
    ];

    public const CHAT_REASONS = [
        'scam' => 'Possível golpe ou fraude',
        'offensive' => 'Ofensa, ameaça ou assédio',
        'spam' => 'Spam ou mensagens invasivas',
        'prohibited_negotiation' => 'Tentativa de negociação proibida',
        'inappropriate_content' => 'Conteúdo impróprio',
        'other' => 'Outro motivo',
    ];

    protected $fillable = [
        'public_id',
        'ad_id',
        'store_id',
        'advertiser_id',
        'reporter_user_id',
        'reviewed_by',
        'subject_type',
        'ad_title_snapshot',
        'ad_module_snapshot',
        'reason',
        'severity',
        'details',
        'evidence_paths',
        'wants_notification',
        'status',
        'admin_action',
        'resolution_note',
        'reviewed_at',
    ];

    protected $casts = [
        'evidence_paths' => 'array',
        'wants_notification' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function notifications()
    {
        return $this->hasMany(ReportNotification::class);
    }

    public function getReferenceAttribute(): string
    {
        return '#'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getReasonLabelAttribute(): string
    {
        $reasons = match ($this->subject_type) {
            'service' => self::SERVICE_REASONS,
            'store' => self::STORE_REASONS,
            default => self::AD_REASONS,
        };

        return $reasons[$this->reason] ?? 'Outro';
    }

    public function getSubjectLabelAttribute(): string
    {
        return match ($this->subject_type) {
            'service' => 'Serviço profissional',
            'store' => 'Loja',
            default => 'Anúncio',
        };
    }

    public function getSubjectKeyAttribute(): string
    {
        $subjectId = $this->subject_type === 'store' ? $this->store_id : $this->ad_id;

        return $this->subject_type.':'.($subjectId ?? 'deleted-'.$this->id);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'Alta prioridade',
            'misleading' => 'Média',
            default => 'Baixa',
        };
    }
}
