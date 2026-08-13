<?php

namespace App\Models;

use App\Services\ProductDisplayService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ad extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'category_id',
        'module',
        'profile_kind',
        'display_mode',
        'sku',
        'advertiser_type',
        'title',
        'slug',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
        'track_stock',
        'allow_backorders',
        'minimum_quantity',
        'technical_specs',
        'video_url',
        'price_type',
        'service_modes',
        'booking_enabled',
        'cnpj',
        'city',
        'state',
        'region',
        'public_address',
        'business_hours',
        'instagram',
        'facebook',
        'logo',
        'banner',
        'cover_position_y',
        'cover_change_count',
        'cover_change_window_started_at',
        'status',
        'views',
        'publication_ip',
        'is_claimed',
        'claiming_enabled',
        'claimed_at',
        'contact_phone',
        'contact_whatsapp',
        'contact_telegram',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'service_modes' => 'array',
        'booking_enabled' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'track_stock' => 'boolean',
        'allow_backorders' => 'boolean',
        'minimum_quantity' => 'integer',
        'technical_specs' => 'array',
        'is_claimed' => 'boolean',
        'claiming_enabled' => 'boolean',
        'claimed_at' => 'datetime',
        'cover_change_count' => 'integer',
        'cover_change_window_started_at' => 'datetime',
    ];

    public function getMonthlyCoverChangesAttribute(): int
    {
        $startedAt = $this->cover_change_window_started_at;
        if (! $startedAt || $startedAt->lt(now()->startOfMonth())) {
            return 0;
        }

        return (int) ($this->cover_change_count ?? 0);
    }

    public function recordCoverChange(): void
    {
        $startedAt = $this->cover_change_window_started_at;
        if (! $startedAt || $startedAt->lt(now()->startOfMonth())) {
            $this->cover_change_count = 1;
            $this->cover_change_window_started_at = now();
        } else {
            $this->cover_change_count = ($this->cover_change_count ?? 0) + 1;
        }

        $this->save();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(AdImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(AdImage::class)->where('is_main', true);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('id');
    }

    public function activeVariations()
    {
        return $this->hasMany(ProductVariation::class)->where('active', true)->orderBy('id');
    }

    public function addons()
    {
        return $this->hasMany(ProductAddon::class)->orderBy('id');
    }

    public function activeAddons()
    {
        return $this->hasMany(ProductAddon::class)->where('active', true)->orderBy('id');
    }

    public function questions()
    {
        return $this->hasMany(ProductQuestion::class)->latest();
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'ad_id', 'user_id')
            ->withPivot('created_at');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function providerClaims()
    {
        return $this->hasMany(ProviderClaim::class);
    }

    public function serviceProcedures() { return $this->hasMany(ServiceProcedure::class); }
    public function serviceStaff() { return $this->hasMany(ServiceStaff::class); }
    public function serviceAppointments() { return $this->hasMany(ServiceAppointment::class); }
    public function serviceFinancialEntries() { return $this->hasMany(ServiceFinancialEntry::class); }
    public function serviceSubscriptionPlans() { return $this->hasMany(ServiceSubscriptionPlan::class); }
    public function serviceClientSubscriptions() { return $this->hasMany(ServiceClientSubscription::class); }

    public function pendingProviderClaims()
    {
        return $this->hasMany(ProviderClaim::class)
            ->where('status', ProviderClaim::STATUS_PENDING);
    }

    public function publicPhone(): ?string
    {
        if (! $this->is_claimed) {
            return $this->contact_phone;
        }

        return $this->user?->phone ?: $this->contact_phone;
    }

    public function publicWhatsapp(): ?string
    {
        if (! $this->is_claimed) {
            return $this->contact_whatsapp ?: $this->contact_phone;
        }

        return $this->user?->whatsapp ?: $this->contact_whatsapp ?: $this->contact_phone;
    }

    public function getCardImageAttribute(): ?string
    {
        return $this->mainImage?->image_path ?: $this->logo ?: $this->banner;
    }

    public function effectiveDisplayMode(): string
    {
        return app(ProductDisplayService::class)->effectiveFor($this);
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price
            ? (float) $this->sale_price
            : (float) $this->price;
    }

    public function getFormattedPriceAttribute(): string
    {
        $val = (float) $this->effective_price;
        if (! $val || $val <= 0) {
            return 'Sob consulta';
        }

        return 'R$ ' . number_format($val, 2, ',', '.');
    }

    public function getIsOutOfStockAttribute(): bool
    {
        if ($this->allow_backorders) {
            return false;
        }

        $variations = $this->relationLoaded('activeVariations')
            ? $this->activeVariations
            : $this->activeVariations()->get();

        if ($variations->isNotEmpty()) {
            return $variations->every(
                fn (ProductVariation $variation) => $variation->track_stock && $variation->stock_quantity < 1
            );
        }

        return $this->track_stock && $this->stock_quantity < 1;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->track_stock
            && ! $this->is_out_of_stock
            && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function getDisplayCategoryAttribute(): string
    {
        $genericLabels = [
            'Profissional Autônomo',
            'Prestador de Serviço',
            'Prestador de Serviços',
        ];

        if ($this->advertiser_type && ! in_array($this->advertiser_type, $genericLabels, true)) {
            return $this->advertiser_type;
        }

        if ($this->module === 'services') {
            $searchableText = Str::lower(Str::ascii($this->title.' '.$this->description));
            $serviceKeywords = [
                'Eletricista' => ['eletric'],
                'Encanador' => ['encanador', 'hidraulic'],
                'Pintor' => ['pintor', 'pintura'],
                'Mecânico' => ['mecanic'],
                'Advogado' => ['advog'],
                'Faxineira / Diarista' => ['faxin', 'diarista'],
                'Marcenaria' => ['marcen', 'montador de moveis', 'montagem de moveis'],
                'TI / Informática' => ['informatica', 'computador', 'tecnologia da informacao'],
                'Frete e Mudanças' => ['frete', 'mudanca'],
                'Restaurante / Pizzaria' => ['restaurante', 'pizzaria'],
                'Pedreiro' => ['pedreiro', 'alvenaria'],
                'Jardineiro' => ['jardineiro', 'jardinagem'],
            ];

            foreach ($serviceKeywords as $category => $keywords) {
                if (collect($keywords)->contains(fn ($keyword) => str_contains($searchableText, $keyword))) {
                    return $category;
                }
            }
        }

        return $this->category?->name ?? match ($this->module) {
            'real_estate' => 'Imóveis',
            'vehicles' => 'Veículos',
            'products' => 'Produtos',
            'services' => 'Serviços',
            'jobs' => 'Empregos',
            'agro' => 'Agro',
            default => 'Anúncio',
        };
    }
}
