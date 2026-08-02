<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'category',
        'product_display_mode',
        'pickup_available',
        'delivery_available',
        'delivery_cities',
        'delivery_neighborhoods',
        'delivery_fee',
        'delivery_region_fees',
        'free_delivery_threshold',
        'delivery_min_minutes',
        'delivery_max_minutes',
        'minimum_order',
        'pickup_address',
        'city',
        'state',
        'phone',
        'whatsapp',
        'instagram',
        'website',
        'logo',
        'banner',
        'active',
        'moderation_status',
        'moderation_note',
        'moderated_by',
        'moderated_at',
        'featured',
        'featured_until',
    ];

    protected $casts = [
        'active' => 'boolean',
        'moderated_at' => 'datetime',
        'featured' => 'boolean',
        'featured_until' => 'datetime',
        'pickup_available' => 'boolean',
        'delivery_available' => 'boolean',
        'delivery_cities' => 'array',
        'delivery_neighborhoods' => 'array',
        'delivery_region_fees' => 'array',
        'delivery_fee' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'delivery_min_minutes' => 'integer',
        'delivery_max_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function products()
    {
        return $this->hasMany(Ad::class)->where('module', 'products');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function media()
    {
        return $this->hasMany(StoreMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function additionalBanners()
    {
        return $this->hasMany(StoreMedia::class)
            ->where('type', 'banner')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function galleryImages()
    {
        return $this->hasMany(StoreMedia::class)
            ->where('type', 'gallery')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function events()
    {
        return $this->hasMany(StoreEvent::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'store_follows', 'store_id', 'user_id')
            ->withPivot('created_at');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function promotions()
    {
        return $this->hasMany(StorePromotion::class)->latest('starts_at')->latest('id');
    }

    public function businessHours()
    {
        return $this->hasMany(StoreBusinessHour::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function businessStatus(?CarbonInterface $moment = null): array
    {
        $hours = $this->relationLoaded('businessHours')
            ? $this->businessHours
            : $this->businessHours()->get();

        if ($hours->isEmpty()) {
            return [
                'state' => 'unknown',
                'is_open' => null,
                'label' => 'Horário não informado',
                'detail' => 'Consulte a loja antes de visitar.',
            ];
        }

        $now = $moment
            ? $moment->copy()->setTimezone('America/Fortaleza')
            : now('America/Fortaleza');
        $day = (int) $now->dayOfWeek;
        $minutes = ($now->hour * 60) + $now->minute;
        $byDay = $hours->keyBy('day_of_week');
        $today = $byDay->get($day);
        $previous = $byDay->get(($day + 6) % 7);

        if ($today?->is_24_hours && ! $today->is_closed) {
            return $this->openStatus('Aberto 24 horas');
        }

        if ($today && ! $today->is_closed && ! $today->is_24_hours) {
            $opens = $today->openingMinutes();
            $closes = $today->closingMinutes();

            if ($opens !== null && $closes !== null) {
                if ($opens < $closes && $minutes >= $opens && $minutes < $closes) {
                    return $this->openStatus('Fecha às '.substr($today->closes_at, 0, 5));
                }
                if ($opens > $closes && $minutes >= $opens) {
                    return $this->openStatus('Fecha amanhã às '.substr($today->closes_at, 0, 5));
                }
            }
        }

        if ($previous && ! $previous->is_closed && ! $previous->is_24_hours) {
            $opens = $previous->openingMinutes();
            $closes = $previous->closingMinutes();
            if ($opens !== null && $closes !== null && $opens > $closes && $minutes < $closes) {
                return $this->openStatus('Fecha às '.substr($previous->closes_at, 0, 5));
            }
        }

        return [
            'state' => 'closed',
            'is_open' => false,
            'label' => 'Fechado agora',
            'detail' => $this->nextOpeningDetail($now, $byDay),
        ];
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopePubliclyVisible($query)
    {
        return $query
            ->where('active', true)
            ->where('moderation_status', 'approved');
    }

    public function isModerationApproved(): bool
    {
        return $this->moderation_status === 'approved';
    }

    public function isCurrentlyFeatured(): bool
    {
        return $this->featured
            && (! $this->featured_until || $this->featured_until->isFuture())
            && $this->active
            && $this->isModerationApproved()
            && (bool) $this->user?->canHaveFeaturedStore();
    }

    private function openStatus(string $detail): array
    {
        return [
            'state' => 'open',
            'is_open' => true,
            'label' => 'Aberto agora',
            'detail' => $detail,
        ];
    }

    private function nextOpeningDetail(CarbonInterface $now, $hoursByDay): string
    {
        for ($offset = 0; $offset <= 7; $offset++) {
            $date = $now->copy()->addDays($offset);
            $schedule = $hoursByDay->get((int) $date->dayOfWeek);

            if (! $schedule || $schedule->is_closed) {
                continue;
            }

            $opening = $schedule->is_24_hours ? '00:00' : substr((string) $schedule->opens_at, 0, 5);
            if (! $opening) {
                continue;
            }

            $candidate = $date->copy()->startOfDay()->setTimeFromTimeString($opening);
            if ($candidate->greaterThan($now)) {
                if ($offset === 0) {
                    return 'Abre hoje às '.$opening;
                }
                if ($offset === 1) {
                    return 'Abre amanhã às '.$opening;
                }

                return 'Abre '.mb_strtolower(StoreBusinessHour::WEEKDAYS[$date->dayOfWeek]).' às '.$opening;
            }
        }

        return 'Consulte a loja para confirmar o atendimento.';
    }
}
