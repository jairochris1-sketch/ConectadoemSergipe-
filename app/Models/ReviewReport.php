<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReport extends Model
{
    public const REASONS = [
        'offensive' => 'Conteúdo ofensivo',
        'false' => 'Informação falsa',
        'spam' => 'Spam ou propaganda',
        'personal_data' => 'Exposição de dados pessoais',
        'unrelated' => 'Não corresponde ao serviço ou anúncio',
        'other' => 'Outro',
    ];

    protected $fillable = [
        'review_id',
        'reporter_user_id',
        'reviewed_by',
        'reason',
        'details',
        'status',
        'ip_address',
        'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
