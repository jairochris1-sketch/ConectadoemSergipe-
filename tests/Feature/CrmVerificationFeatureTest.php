<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrmVerificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'services.consultar_crm.url' => 'https://crm-api.test/api/index.php',
            'services.consultar_crm.key' => 'secret-test-key',
            'services.consultar_crm.timeout' => 2,
        ]);
        Http::preventStrayRequests();
    }

    public function test_authenticated_user_can_query_an_active_crm_without_exposing_the_key(): void
    {
        Http::fake([
            'https://crm-api.test/*' => Http::response($this->activePayload()),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(
            route('professionals.crm.verify'),
            ['credential' => 'CRM/SE 1234', 'state' => 'SE', 'category' => 'Cardiologista', 'professional_name' => 'Carlos Almeida']
        );

        $response
            ->assertOk()
            ->assertJsonPath('professional.name', 'DR. CARLOS ALMEIDA')
            ->assertJsonPath('professional.number', '1234')
            ->assertJsonPath('professional.state', 'SE')
            ->assertJsonMissing(['key' => 'secret-test-key']);

        Http::assertSent(fn ($request) => $request['tipo'] === 'crm'
            && $request['uf'] === 'SE'
            && $request['q'] === 'Carlos Almeida'
            && $request['chave'] === 'secret-test-key');
    }

    public function test_physician_profile_saves_registry_result_without_claiming_identity_verification(): void
    {
        Http::fake([
            'https://crm-api.test/*' => Http::response($this->activePayload()),
        ]);
        $user = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($user)->post(route('ad.store'), [
            'module' => 'services',
            'profile_kind' => 'liberal_professional',
            'category_name' => 'Cardiologista',
            'title' => 'Dr. Carlos Almeida Cardiologista',
            'city' => 'Aracaju',
            'description' => 'Atendimento cardiológico presencial e por teleconsulta.',
            'whatsapp' => '79999999999',
            'phone' => '79999999999',
            'liberal_credential' => '1234',
            'liberal_credential_state' => 'SE',
            'liberal_credential_name' => 'Carlos Almeida',
            'liberal_credential_issuer' => 'Conselho Regional de Medicina',
            'service_modes' => ['presencial', 'online'],
        ])->assertSessionHasNoErrors();

        $ad = Ad::where('title', 'Dr. Carlos Almeida Cardiologista')->firstOrFail();
        $this->assertSame('CRM/SE 1234', data_get($ad->technical_specs, 'liberal_profile.credential'));
        $this->assertTrue((bool) data_get($ad->technical_specs, 'liberal_profile.credential_registry_found'));
        $this->assertSame('DR. CARLOS ALMEIDA', data_get($ad->technical_specs, 'liberal_profile.credential_registry_name'));
        $this->assertFalse((bool) data_get($ad->technical_specs, 'liberal_profile.credential_verified'));

        $this->get(route('provider.show', $ad->slug))
            ->assertOk()
            ->assertSee('Registro localizado')
            ->assertSee('Nome localizado:')
            ->assertSee('não comprova a identidade do titular da conta');
    }

    public function test_inactive_or_different_crm_is_rejected(): void
    {
        $payload = $this->activePayload();
        $payload['item'][0]['situacao'] = 'Inativo';
        Http::fake(['https://crm-api.test/*' => Http::response($payload)]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('professionals.crm.verify'), [
                'credential' => '1234',
                'state' => 'SE',
                'category' => 'Médico',
                'professional_name' => 'Carlos Almeida',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'O CRM foi localizado, mas a situação informada não está ativa.');
    }

    public function test_temporary_api_failure_keeps_registration_informed_and_not_verified(): void
    {
        Http::fake(['https://crm-api.test/*' => Http::response([], 503)]);
        $user = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($user)->post(route('ad.store'), [
            'module' => 'services',
            'profile_kind' => 'liberal_professional',
            'category_name' => 'Médico',
            'title' => 'Médico aguardando consulta do CRM',
            'city' => 'Aracaju',
            'description' => 'Perfil com registro informado enquanto a fonte externa está indisponível.',
            'whatsapp' => '79999999999',
            'phone' => '79999999999',
            'liberal_credential' => '5678',
            'liberal_credential_state' => 'SE',
            'liberal_credential_name' => 'Médico de Teste',
            'liberal_credential_issuer' => 'Conselho Regional de Medicina',
        ])->assertSessionHasNoErrors();

        $ad = Ad::where('title', 'Médico aguardando consulta do CRM')->firstOrFail();
        $this->assertFalse((bool) data_get($ad->technical_specs, 'liberal_profile.credential_registry_found'));
        $this->assertSame('unavailable', data_get($ad->technical_specs, 'liberal_profile.credential_registry_check_status'));
        $this->assertFalse((bool) data_get($ad->technical_specs, 'liberal_profile.credential_verified'));
    }

    private function activePayload(): array
    {
        return [
            'status' => 'true',
            'total' => 1,
            'item' => [[
                'tipo' => 'CRM',
                'nome' => 'DR. CARLOS ALMEIDA',
                'numero' => '1234',
                'profissao' => 'Médico',
                'uf' => 'SE',
                'situacao' => 'Ativo',
                'especialidades' => 'Cardiologia - RQE 9999',
                'link' => 'https://crm-api.test/medicos/carlos-almeida',
            ]],
        ];
    }
}
