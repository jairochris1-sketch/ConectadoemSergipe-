<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\PlanFeatureValue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPlanController extends Controller
{
    // ─── Lista de Planos ────────────────────────────────────────────────────

    public function index()
    {
        $plans    = Plan::withCount('featureValues')->ordered()->get();
        $features = PlanFeature::ordered()->get();

        return view('admin.plans.index', compact('plans', 'features'));
    }

    // ─── Criar Plano ────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug'           => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('plans')],
            'name'           => 'required|string|max:100',
            'badge_label'    => 'nullable|string|max:50',
            'headline'       => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:500',
            'price'          => 'required|numeric|min:0',
            'color'          => 'required|in:secondary,primary,purple,success,warning,danger',
            'is_highlighted' => 'boolean',
            'sort_order'     => 'integer|min:0',
        ]);

        Plan::create(array_merge($validated, [
            'is_active'      => true,
            'is_highlighted' => $request->boolean('is_highlighted'),
            'sort_order'     => $validated['sort_order'] ?? 99,
        ]));

        return back()->with('success', "Plano \"{$validated['name']}\" criado com sucesso!");
    }

    // ─── Atualizar Plano ────────────────────────────────────────────────────

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'badge_label'    => 'nullable|string|max:50',
            'headline'       => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:500',
            'price'          => 'required|numeric|min:0',
            'color'          => 'required|in:secondary,primary,purple,success,warning,danger',
            'is_highlighted' => 'boolean',
            'sort_order'     => 'integer|min:0',
        ]);

        $plan->update(array_merge($validated, [
            'is_highlighted' => $request->boolean('is_highlighted'),
        ]));

        return back()->with('success', "Plano \"{$plan->name}\" atualizado com sucesso!");
    }

    // ─── Ativar/Desativar Plano ─────────────────────────────────────────────

    public function toggleActive(Plan $plan)
    {
        if ($plan->slug === 'free') {
            return back()->with('error', 'O plano Gratuito não pode ser desativado.');
        }

        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'ativado' : 'desativado';

        return back()->with('success', "Plano \"{$plan->name}\" {$status} com sucesso!");
    }

    // ─── Features de um Plano ───────────────────────────────────────────────

    public function features(Plan $plan)
    {
        $plan->load(['featureValues.feature']);
        $allFeatures = PlanFeature::ordered()->get();

        // Features que o plano NÃO tem ainda
        $assignedIds  = $plan->featureValues->pluck('plan_feature_id')->toArray();
        $freeFeatures = $allFeatures->whereNotIn('id', $assignedIds);

        return view('admin.plans.features', compact('plan', 'allFeatures', 'freeFeatures'));
    }

    // ─── Adicionar Feature ao Plano ─────────────────────────────────────────

    public function addFeature(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'plan_feature_id' => ['required', 'exists:plan_features,id',
                Rule::unique('plan_feature_values')->where('plan_id', $plan->id)],
            'value'           => 'nullable|string|max:50',
            'show_on_page'    => 'boolean',
        ]);

        PlanFeatureValue::create([
            'plan_id'         => $plan->id,
            'plan_feature_id' => $validated['plan_feature_id'],
            'value'           => $validated['value'] === 'unlimited' ? null : $validated['value'],
            'show_on_page'    => $request->boolean('show_on_page', true),
        ]);

        $feature = PlanFeature::find($validated['plan_feature_id']);
        return back()->with('success', "Feature \"{$feature->name}\" adicionada ao plano \"{$plan->name}\"!");
    }

    // ─── Atualizar Valor de Feature no Plano ───────────────────────────────

    public function updateFeature(Request $request, Plan $plan, PlanFeatureValue $featureValue)
    {
        if ($featureValue->plan_id !== $plan->id) {
            abort(403);
        }

        $validated = $request->validate([
            'value'        => 'nullable|string|max:50',
            'show_on_page' => 'boolean',
        ]);

        $featureValue->update([
            'value'        => $validated['value'] === 'unlimited' ? null : $validated['value'],
            'show_on_page' => $request->boolean('show_on_page', true),
        ]);

        return back()->with('success', 'Valor da feature atualizado!');
    }

    // ─── Remover Feature do Plano ───────────────────────────────────────────

    public function removeFeature(Plan $plan, PlanFeatureValue $featureValue)
    {
        if ($featureValue->plan_id !== $plan->id) {
            abort(403);
        }

        $featureValue->delete();

        return back()->with('success', 'Feature removida do plano.');
    }

    // ─── Criar Feature ──────────────────────────────────────────────────────

    public function storeFeature(Request $request)
    {
        $validated = $request->validate([
            'key'         => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('plan_features')],
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'type'        => 'required|in:boolean,integer,unlimited',
            'sort_order'  => 'integer|min:0',
        ]);

        PlanFeature::create(array_merge($validated, [
            'sort_order' => $validated['sort_order'] ?? 99,
        ]));

        return back()->with('success', "Feature \"{$validated['name']}\" criada com sucesso!");
    }

    // ─── Trocar Plano de um Usuário ─────────────────────────────────────────

    public function assignUserPlan(Request $request, User $user)
    {
        $validated = $request->validate([
            'plan_slug' => 'required|exists:plans,slug',
        ]);

        $user->update(['subscription_plan' => $validated['plan_slug']]);
        $planName = Plan::where('slug', $validated['plan_slug'])->value('name');

        return back()->with('success', "Plano do usuário \"{$user->name}\" alterado para {$planName}.");
    }

    // ─── Override de Feature para Usuário ──────────────────────────────────

    public function setUserOverride(Request $request, User $user)
    {
        $validated = $request->validate([
            'plan_feature_id' => 'required|exists:plan_features,id',
            'value'           => 'nullable|string|max:50',
            'reason'          => 'nullable|string|max:255',
        ]);

        \DB::table('user_feature_overrides')->updateOrInsert(
            ['user_id' => $user->id, 'plan_feature_id' => $validated['plan_feature_id']],
            [
                'value'      => $validated['value'] === 'unlimited' ? null : $validated['value'],
                'reason'     => $validated['reason'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Override de feature aplicado ao usuário.');
    }
}
