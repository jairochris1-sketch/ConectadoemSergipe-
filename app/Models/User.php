<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $attributes = [
        'notifications_enabled' => true,
        'header_layout' => 'horizontal',
        'theme_preference' => 'system',
        'notification_messages_enabled' => true,
        'notification_reviews_enabled' => true,
        'notification_reports_enabled' => true,
        'smart_search_enabled' => true,
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'google_id',
        'password',
        'phone',
        'whatsapp',
        'pix_key',
        'city',
        'state',
        'avatar',
        'avatar_change_count',
        'avatar_change_window_started_at',
        'avatar_change_locked_until',
        'banner',
        'instagram',
        'facebook',
        'website',
        'role',
        'subscription_plan',
        'suspended_at',
        'profile_type',
        'onboarding_completed',
        'cpf_cnpj',
        'business_name',
        'business_category',
        'commercial_address',
        'notifications_enabled',
        'header_layout',
        'theme_preference',
        'notification_messages_enabled',
        'notification_reviews_enabled',
        'notification_reports_enabled',
        'smart_search_enabled',
        'last_seen_at',
        'is_available',
        'last_login_ip',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
            'suspended_at' => 'datetime',
            'avatar_change_window_started_at' => 'datetime',
            'avatar_change_locked_until' => 'datetime',
            'notifications_enabled' => 'boolean',
            'notification_messages_enabled' => 'boolean',
            'notification_reviews_enabled' => 'boolean',
            'notification_reports_enabled' => 'boolean',
            'smart_search_enabled' => 'boolean',
            'last_seen_at' => 'datetime',
            'is_available' => 'boolean',
        ];
    }

    public function isAvailableNow(): bool
    {
        if ($this->is_available === false) {
            return false;
        }

        if ($this->last_seen_at) {
            return $this->last_seen_at->gt(now()->subMinutes(30));
        }

        return true;
    }


    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function professionalProfiles()
    {
        return $this->hasMany(Ad::class)->where('module', 'services');
    }

    public function providerClaims()
    {
        return $this->hasMany(ProviderClaim::class, 'claimant_user_id');
    }

    public function professionalProfileLimit(): ?int
    {
        if ($this->role === 'admin') {
            return null;
        }

        $value = $this->planFeatureValue('professional_profiles');
        return $value === null ? null : max(0, (int) $value);
    }

    public function canCreateAnotherProfessionalProfile(): bool
    {
        $limit = $this->professionalProfileLimit();

        return $limit === null || $this->professionalProfiles()->count() < $limit;
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function cultureWorks()
    {
        return $this->hasMany(CultureWork::class);
    }

    public function feedPosts()
    {
        return $this->hasMany(FeedPost::class);
    }

    /**
     * Relacionamento com o plano de assinatura (via tabela plans).
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'subscription_plan', 'slug');
    }

    /**
     * Retorna o objeto Plan do usuário. Usa o plano 'free' como fallback.
     */
    public function getPlan(): Plan
    {
        $slug = $this->normalizedSubscriptionPlan();
        return Plan::where('slug', $slug)->first()
            ?? Plan::where('slug', 'free')->firstOrNew(['slug' => 'free']);
    }

    /**
     * Retorna o valor de uma feature para este usuário.
     *
     * Ordem de prioridade:
     *  1. Override individual (user_feature_overrides)
     *  2. Valor do plano no banco (plan_feature_values)
     *  3. Fallback no config/marketplace.php
     *
     * null = ilimitado | '0' = bloqueado | string numérica = limite
     */
    public function planFeatureValue(string $key): ?string
    {
        // 1. Override individual
        $override = \DB::table('user_feature_overrides')
            ->join('plan_features', 'plan_features.id', '=', 'user_feature_overrides.plan_feature_id')
            ->where('user_feature_overrides.user_id', $this->id)
            ->where('plan_features.key', $key)
            ->value('user_feature_overrides.value');

        if ($override !== null) {
            return $override;
        }

        // 2. Valor do plano no banco
        $plan = Plan::where('slug', $this->normalizedSubscriptionPlan())->first();
        if ($plan) {
            $featureValue = $plan->featureValues()
                ->join('plan_features', 'plan_features.id', '=', 'plan_feature_values.plan_feature_id')
                ->where('plan_features.key', $key)
                ->value('plan_feature_values.value');

            if ($featureValue !== null || $plan->features()->where('plan_features.key', $key)->exists()) {
                return $featureValue; // null aqui = ilimitado
            }
        }

        // 3. Fallback config (compatibilidade)
        return $this->planFeatureValueFromConfig($key);
    }

    /**
     * Fallback para config/marketplace.php caso o banco não tenha o dado.
     */
    private function planFeatureValueFromConfig(string $key): ?string
    {
        $plan = $this->normalizedSubscriptionPlan();
        $configMap = [
            'store_limit'          => 'marketplace.store_limits',
            'product_limit'        => 'marketplace.store_product_limits',
            'analytics_days'       => 'marketplace.store_analytics_period_days',
            'store_banners'        => null,
            'store_gallery'        => null,
            'promotions_limit'     => 'marketplace.store_promotion_limits',
            'store_featured'       => 'marketplace.store_featured_enabled',
            'provider_featured'    => 'marketplace.provider_featured_enabled',
            'professional_profiles'=> 'marketplace.professional_profile_limits',
        ];

        if (!isset($configMap[$key]) || $configMap[$key] === null) {
            return '0';
        }

        $value = config($configMap[$key] . '.' . $plan);
        if ($value === null) return null; // ilimitado
        return (string) $value;
    }

    public function normalizedSubscriptionPlan(): string
    {
        $plan = strtolower(trim((string) ($this->subscription_plan ?: 'free')));

        $aliases = [
            'gold'         => 'enterprise',
            'ouro'         => 'enterprise',
            'premium'      => 'enterprise',
            'professional' => 'pro',
            'profissional' => 'pro',
            // Garante que slugs antigos nunca sobrescrevam o 'start'
        ];

        $plan = $aliases[$plan] ?? $plan;

        // Verifica no banco primeiro
        if (Plan::where('slug', $plan)->exists()) {
            return $plan;
        }

        // Fallback: verifica no config (compatibilidade com slugs antigos)
        $knownPlans = config('marketplace.store_limits', ['free' => 0]);
        return array_key_exists($plan, $knownPlans) ? $plan : 'free';
    }

    public function subscriptionPlanLabel(): string
    {
        return config(
            'marketplace.plan_labels.'.$this->normalizedSubscriptionPlan(),
            'Gratuito'
        );
    }

    public function storeLimit(): ?int
    {
        if ($this->role === 'admin') {
            return null;
        }

        $value = $this->planFeatureValue('store_limit');
        return $value === null ? null : max(0, (int) $value);
    }

    public function storeProductLimit(): ?int
    {
        if ($this->role === 'admin') {
            return null;
        }

        $value = $this->planFeatureValue('product_limit');
        return $value === null ? null : max(0, (int) $value);
    }

    public function canCreateAnotherStore(): bool
    {
        $limit = $this->storeLimit();

        return $limit === null || $this->stores()->count() < $limit;
    }

    public function canAddProductToStore(Store $store, ?Ad $currentProduct = null): bool
    {
        if ($store->user_id !== $this->id) {
            return false;
        }

        $limit = $this->storeProductLimit();
        if ($limit === null) {
            return true;
        }

        $productsQuery = $store->ads()->where('module', 'products');
        if ($currentProduct?->store_id === $store->id) {
            $productsQuery->whereKeyNot($currentProduct->id);
        }

        return $productsQuery->count() < $limit;
    }

    public function canHaveFeaturedStore(): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        $value = $this->planFeatureValue('store_featured');
        return $value === '1' || $value === null;
    }

    public function canHaveFeaturedProvider(): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        $value = $this->planFeatureValue('provider_featured');
        return $value === '1' || $value === null;
    }

    public function storeMediaLimit(string $type): ?int
    {
        if ($this->role === 'admin') {
            return null;
        }

        $featureKey = $type === 'banner' ? 'store_banners' : 'store_gallery';
        $value = $this->planFeatureValue($featureKey);
        return $value === null ? null : max(0, (int) $value);
    }

    public function storeAnalyticsPeriodDays(): int
    {
        if ($this->role === 'admin') {
            return 90;
        }

        $value = $this->planFeatureValue('analytics_days');
        return max(7, (int) ($value ?? 7));
    }

    public function storePromotionLimit(): ?int
    {
        if ($this->role === 'admin') {
            return null;
        }

        $value = $this->planFeatureValue('promotions_limit');
        return $value === null ? null : max(0, (int) $value);
    }

    public function canActivateStorePromotion(Store $store, ?int $exceptPromotionId = null): bool
    {
        if ($store->user_id !== $this->id && $this->role !== 'admin') {
            return false;
        }

        $limit = $this->storePromotionLimit();
        if ($limit === null) {
            return true;
        }

        $activePromotions = $store->promotions()
            ->where('active', true)
            ->where('ends_at', '>', now())
            ->when($exceptPromotionId, fn ($query) => $query->whereKeyNot($exceptPromotionId))
            ->count();

        return $activePromotions < $limit;
    }

    public function favorites()
    {
        return $this->belongsToMany(Ad::class, 'favorites', 'user_id', 'ad_id')
            ->withPivot('created_at');
    }

    public function followedStores()
    {
        return $this->belongsToMany(Store::class, 'store_follows', 'user_id', 'store_id')
            ->withPivot('created_at');
    }

    public function reportNotifications()
    {
        return $this->hasMany(ReportNotification::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function isFreePlan(): bool
    {
        return ($this->subscription_plan ?? 'free') === 'free';
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
