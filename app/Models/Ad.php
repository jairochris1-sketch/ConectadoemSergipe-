<?php

namespace App\Models;

use App\Services\ProductDisplayService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ad extends Model
{
    public const PROFILE_KINDS = [
        'professional' => [
            'label' => 'Prestador de serviços',
            'subtitle' => 'Autônomo, diarista, eletricista, manicure, pedreiro, motorista etc.',
            'icon' => 'fa-solid fa-user-gear',
            'badge_icon' => 'fa-solid fa-user-gear',
        ],
        'service_company' => [
            'label' => 'Empresa de serviços',
            'subtitle' => 'Assistência técnica, construtora, clínica, oficina, empresa de limpeza etc.',
            'icon' => 'fa-solid fa-building',
            'badge_icon' => 'fa-solid fa-building',
        ],
        'store_commerce' => [
            'label' => 'Loja ou comércio',
            'subtitle' => 'Loja física, virtual, mercado, material de construção, roupas, celulares etc.',
            'icon' => 'fa-solid fa-store',
            'badge_icon' => 'fa-solid fa-store',
        ],
        'liberal_professional' => [
            'label' => 'Profissional liberal',
            'subtitle' => 'Advogado, dentista, corretor, professor, técnico, contador etc.',
            'icon' => 'fa-solid fa-user-tie',
            'badge_icon' => 'fa-solid fa-user-tie',
        ],
        'agro_producer' => [
            'label' => 'Produtor rural / Agro',
            'subtitle' => 'Produtor, criador, agricultor, comércio rural etc.',
            'icon' => 'fa-solid fa-tractor',
            'badge_icon' => 'fa-solid fa-tractor',
        ],
        'cultural_artist' => [
            'label' => 'Artista / Profissional da cultura',
            'subtitle' => 'Músicos, cordelistas, fotógrafos, artesãos, produtores culturais etc.',
            'icon' => 'fa-solid fa-palette',
            'badge_icon' => 'fa-solid fa-palette',
        ],
        'real_estate_agency' => [
            'label' => 'Imobiliária',
            'subtitle' => 'Imobiliárias, corretoras e administradoras de imóveis',
            'icon' => 'fa-solid fa-house-chimney',
            'badge_icon' => 'fa-solid fa-house-chimney',
        ],
        'hiring_company' => [
            'label' => 'Empresa contratante',
            'subtitle' => 'Empresas e negócios publicando oportunidades e vagas',
            'icon' => 'fa-solid fa-briefcase',
            'badge_icon' => 'fa-solid fa-briefcase',
        ],
    ];

    public static function getProfileKinds(): array
    {
        return self::PROFILE_KINDS;
    }

    public function getProfileKindLabelAttribute(): ?string
    {
        if (! $this->profile_kind) {
            return null;
        }

        return self::PROFILE_KINDS[$this->profile_kind]['label'] ?? null;
    }

    public function getProfileKindIconAttribute(): ?string
    {
        if (! $this->profile_kind) {
            return null;
        }

        return self::PROFILE_KINDS[$this->profile_kind]['icon'] ?? null;
    }

    public function getProfileKindInfoAttribute(): ?array
    {
        if (! $this->profile_kind) {
            return null;
        }

        return self::PROFILE_KINDS[$this->profile_kind] ?? null;
    }

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
            ->withPivot(['folder_id', 'created_at']);
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
    public function serviceScheduleBlocks() { return $this->hasMany(ServiceScheduleBlock::class); }
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

    public function getCategoryIconAttribute(): string
    {
        $categoryName = Str::lower(Str::ascii($this->display_category ?? $this->advertiser_type ?? ''));
        $titleDesc = Str::lower(Str::ascii($this->title . ' ' . $this->description));

        if (str_contains($categoryName, 'advog') || str_contains($titleDesc, 'advog') || str_contains($categoryName, 'juridic')) {
            return 'fa-solid fa-scale-balanced';
        }
        if (str_contains($categoryName, 'pintor') || str_contains($categoryName, 'pintura') || str_contains($titleDesc, 'pintor')) {
            return 'fa-solid fa-paint-roller';
        }
        if (str_contains($categoryName, 'eletric') || str_contains($titleDesc, 'eletric')) {
            return 'fa-solid fa-bolt';
        }
        if (str_contains($categoryName, 'encanador') || str_contains($categoryName, 'hidraulic') || str_contains($titleDesc, 'encanador')) {
            return 'fa-solid fa-faucet-drip';
        }
        if (str_contains($categoryName, 'pedreiro') || str_contains($categoryName, 'alvenaria') || str_contains($categoryName, 'construcao') || str_contains($titleDesc, 'pedreiro')) {
            return 'fa-solid fa-trowel-bricks';
        }
        if (str_contains($categoryName, 'mecanic') || str_contains($titleDesc, 'mecanic')) {
            return 'fa-solid fa-wrench';
        }
        if (str_contains($categoryName, 'marcen') || str_contains($categoryName, 'montador') || str_contains($titleDesc, 'marcen')) {
            return 'fa-solid fa-hammer';
        }
        if (str_contains($categoryName, 'mudanca') || str_contains($categoryName, 'frete') || str_contains($categoryName, 'carro de mudanca') || str_contains($titleDesc, 'mudanca') || str_contains($titleDesc, 'frete')) {
            return 'fa-solid fa-truck-moving';
        }
        if (str_contains($categoryName, 'manicure') || str_contains($categoryName, 'pedicure') || str_contains($categoryName, 'unha') || str_contains($titleDesc, 'manicure') || str_contains($titleDesc, 'unha')) {
            return 'fa-solid fa-hand-sparkles';
        }
        if (str_contains($categoryName, 'cabel') || str_contains($categoryName, 'barbe') || str_contains($categoryName, 'salao') || str_contains($titleDesc, 'cabel') || str_contains($titleDesc, 'barbe')) {
            return 'fa-solid fa-scissors';
        }
        if (str_contains($categoryName, 'programad') || str_contains($categoryName, 'desenvolved') || str_contains($categoryName, 'informatic') || str_contains($categoryName, 'computador') || str_contains($categoryName, 'ti') || str_contains($titleDesc, 'programad') || str_contains($titleDesc, 'developer') || str_contains($titleDesc, 'software')) {
            return 'fa-solid fa-laptop-code';
        }
        if (str_contains($categoryName, 'faxin') || str_contains($categoryName, 'diarista') || str_contains($categoryName, 'limpeza') || str_contains($titleDesc, 'faxin') || str_contains($titleDesc, 'diarista')) {
            return 'fa-solid fa-broom';
        }
        if (str_contains($categoryName, 'jardin') || str_contains($titleDesc, 'jardin')) {
            return 'fa-solid fa-seedling';
        }
        if (str_contains($categoryName, 'restaurante') || str_contains($categoryName, 'pizzaria') || str_contains($categoryName, 'lanche') || str_contains($titleDesc, 'pizzaria')) {
            return 'fa-solid fa-utensils';
        }
        if (str_contains($categoryName, 'fotograf') || str_contains($titleDesc, 'fotograf') || str_contains($titleDesc, 'filmag')) {
            return 'fa-solid fa-camera';
        }
        if (str_contains($categoryName, 'medico') || str_contains($categoryName, 'dentista') || str_contains($categoryName, 'psicolog') || str_contains($categoryName, 'fisioterap') || str_contains($categoryName, 'saude')) {
            return 'fa-solid fa-stethoscope';
        }
        if (str_contains($categoryName, 'personal') || str_contains($categoryName, 'treinador') || str_contains($categoryName, 'academia')) {
            return 'fa-solid fa-dumbbell';
        }
        if (str_contains($categoryName, 'artista') || str_contains($categoryName, 'cultura') || str_contains($categoryName, 'musica') || str_contains($categoryName, 'artesanato')) {
            return 'fa-solid fa-palette';
        }
        if (str_contains($categoryName, 'imob') || str_contains($categoryName, 'corretor') || str_contains($categoryName, 'imove')) {
            return 'fa-solid fa-building';
        }
        if (str_contains($categoryName, 'agro') || str_contains($categoryName, 'rural') || str_contains($categoryName, 'gado') || str_contains($categoryName, 'trator')) {
            return 'fa-solid fa-tractor';
        }
        if (str_contains($categoryName, 'veiculo') || str_contains($categoryName, 'carro') || str_contains($categoryName, 'moto')) {
            return 'fa-solid fa-car';
        }
        if (str_contains($categoryName, 'emprego') || str_contains($categoryName, 'vaga') || str_contains($categoryName, 'contratante')) {
            return 'fa-solid fa-briefcase';
        }
        if (str_contains($categoryName, 'loja') || str_contains($categoryName, 'comercio') || str_contains($categoryName, 'produto')) {
            return 'fa-solid fa-bag-shopping';
        }

        return match ($this->module) {
            'real_estate' => 'fa-solid fa-building',
            'vehicles' => 'fa-solid fa-car',
            'products' => 'fa-solid fa-bag-shopping',
            'jobs' => 'fa-solid fa-briefcase',
            'agro' => 'fa-solid fa-tractor',
            default => 'fa-solid fa-user-tie',
        };
    }
}
