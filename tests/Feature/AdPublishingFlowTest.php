<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdPublishingFlowTest extends TestCase
{
    use RefreshDatabase;

    private array $uploadedPaths = [];

    public function test_regular_categories_publish_with_cover_and_appear_in_user_panel(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '79999999999',
        ]);
        $modules = ['products', 'real_estate', 'vehicles', 'jobs', 'agro'];

        foreach ($modules as $module) {
            $title = "Publicação {$module}";
            $response = $this->actingAs($user)->post(route('ad.store'), [
                'module' => $module,
                'category_name' => 'Categoria de teste',
                'title' => $title,
                'price' => 100,
                'city' => 'Aracaju',
                'description' => 'Descrição completa usada para validar a publicação.',
                'whatsapp' => '79999999999',
                'logo' => $this->fakePng("principal-{$module}.png"),
                'banner' => $this->fakePng("capa-{$module}.png"),
            ]);

            $response->assertSessionHasNoErrors()->assertRedirect();

            $ad = Ad::with('mainImage')->where('title', $title)->firstOrFail();
            $this->assertSame('active', $ad->status);
            $this->assertNotNull($ad->logo);
            $this->assertNotNull($ad->banner);
            $this->assertNotNull($ad->mainImage);
            $this->assertSame($ad->logo, $ad->mainImage->image_path);

            $this->uploadedPaths[] = $ad->logo;
            $this->uploadedPaths[] = $ad->banner;

            $this->get(route('ad.show', $ad->slug))
                ->assertOk()
                ->assertSee(asset($ad->card_image), false)
                ->assertSee(asset($ad->banner), false);
        }

        $panelResponse = $this->get(route('user.panel'))->assertOk();
        foreach ($modules as $module) {
            $panelResponse->assertSee("Publicação {$module}");
        }

        $this->assertSame(5, $user->ads()->count());
    }

    public function test_create_form_exposes_previews_and_has_no_step_connector_line(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('ad.create'))
            ->assertOk()
            ->assertViewHas('requestedModule', 'services')
            ->assertSee('id="main-photo-preview-box"', false)
            ->assertSee('id="banner-photo-preview-box"', false)
            ->assertSee('id="prev-card-img"', false)
            ->assertSee('id="wizardForm" novalidate', false)
            ->assertSee('id="mod_services"', false)
            ->assertDontSee('module-card-services', false)
            ->assertSee('module-motion-icon', false)
            ->assertSee('professional-motion-panel', false)
            ->assertSee('publish-step-track', false)
            ->assertSee('publish-design-design4', false)
            ->assertSee('snapshotFileForUpload', false)
            ->assertSee('reader.readAsArrayBuffer(file)', false)
            ->assertSee('const stableFile = await snapshotFileForUpload(file)', false)
            ->assertDontSee("file.size <= 900 * 1024) {\n            return file", false)
            ->assertSee('wizard-submit-button', false)
            ->assertSee('Como você atua na área de serviços?')
            ->assertSee('Prestador de serviços')
            ->assertSee('Empresa de serviços')
            ->assertSee('Assist\u00eancia T\u00e9cnica', false)
            ->assertSee('const serviceCategoriesByProfileKind', false)
            ->assertSee('const initialModule = "services"', false)
            ->assertDontSee('Empresa de serviços — Assistência técnica')
            ->assertSee('Loja ou comércio')
            ->assertSee('Profissional liberal')
            ->assertSee('id="service-profile-kind"', false)
            ->assertDontSee('Seus dados protegidos')
            ->assertDontSee('moduleMotionTrail', false);
    }

    public function test_selected_service_profile_kind_is_not_offered_again_in_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('ad.create', [
                'module' => 'services',
                'profile_kind' => 'liberal_professional',
            ]))
            ->assertOk()
            ->assertSee('name="profile_kind" id="service-profile-kind" value="liberal_professional"', false)
            ->assertDontSee('id="profile_kind_select"', false)
            ->assertDontSee('Como você atua? (Tipo de anunciante)', false);
    }

    public function test_cultural_artist_receives_only_cultural_subcategories(): void
    {
        $user = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($user)
            ->get(route('ad.create', [
                'module' => 'services',
                'profile_kind' => 'cultural_artist',
            ]))
            ->assertOk()
            ->assertSee('Cordelista')
            ->assertSee('Escritor \/ Escritora', false)
            ->assertDontSee('Personalizados, Artes Sublimadas e Logos');

        $this->actingAs($user)
            ->post(route('ad.store'), [
                'module' => 'services',
                'profile_kind' => 'cultural_artist',
                'category_name' => 'Plotagem',
                'title' => 'Perfil cultural inválido',
                'city' => 'Aracaju',
                'description' => 'Valida que uma categoria comercial não entra no perfil cultural.',
                'whatsapp' => '79999999999',
                'phone' => '79999999999',
            ])
            ->assertSessionHasErrors('category_name');
    }

    public function test_brazilian_price_category_and_region_rules_are_saved_correctly(): void
    {
        $user = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($user)->post(route('ad.store'), [
            'module' => 'products',
            'category_name' => 'Celulares & Telefonia',
            'title' => 'Telefone de teste',
            'price' => '80.000',
            'city' => 'Aracaju',
            'region' => 'Centro e bairros próximos',
            'public_address' => 'Endereço que não pertence a produto',
            'description' => 'Descrição completa do produto anunciado para venda.',
            'whatsapp' => '79999999999',
        ])->assertSessionHasNoErrors();

        $product = Ad::where('title', 'Telefone de teste')->firstOrFail();
        $this->assertEquals(80000, (float) $product->price);
        $this->assertSame('Celulares & Telefonia', $product->advertiser_type);
        $this->assertSame('Celulares & Telefonia', $product->display_category);
        $this->assertNull($product->region);
        $this->assertNull($product->public_address);

        $this->get(route('ad.show', $product->slug))
            ->assertOk()
            ->assertSee('R$ 80.000,00')
            ->assertSee('Celulares &amp; Telefonia', false)
            ->assertDontSee('Profissional Autônomo');

        $this->actingAs($user)->post(route('ad.store'), [
            'module' => 'services',
            'profile_kind' => 'service_company',
            'category_name' => 'Eletricista',
            'title' => 'Eletricista de teste',
            'city' => 'Aracaju',
            'region' => 'Centro e bairros próximos',
            'public_address' => 'Rua das Flores, 120, Centro',
            'description' => 'Descrição completa dos serviços profissionais.',
            'whatsapp' => '79999999999',
            'phone' => '79999999999',
        ])->assertSessionHasNoErrors();

        $service = Ad::where('title', 'Eletricista de teste')->firstOrFail();
        $this->assertSame('Eletricista', $service->display_category);
        $this->assertSame('service_company', $service->profile_kind);
        $this->assertSame('Centro e bairros próximos', $service->region);
        $this->assertSame('Rua das Flores, 120, Centro', $service->public_address);

        $this->get(route('provider.show', $service->slug))
            ->assertOk()
            ->assertSee('Empresa de serviços')
            ->assertSee('Local de atendimento')
            ->assertSee('Como chegar')
            ->assertSee('google.com/maps/dir', false);
    }

    public function test_user_can_replace_and_remove_published_ad_images(): void
    {
        $user = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($user)->post(route('ad.store'), [
            'module' => 'products',
            'category_name' => 'Informática',
            'title' => 'Notebook com fotos',
            'price' => '5.000,00',
            'city' => 'Aracaju',
            'description' => 'Descrição completa para testar a edição das imagens.',
            'whatsapp' => '79999999999',
            'logo' => $this->fakePng('principal-antiga.png'),
            'banner' => $this->fakePng('capa-antiga.png'),
            'images' => [$this->fakePng('galeria-antiga.png')],
        ])->assertSessionHasNoErrors();

        $ad = Ad::with('images')->where('title', 'Notebook com fotos')->firstOrFail();
        $galleryImage = $ad->images->firstWhere('image_path', '!=', $ad->logo);
        $this->uploadedPaths = array_merge(
            $this->uploadedPaths,
            $ad->images->pluck('image_path')->all(),
            [$ad->logo, $ad->banner]
        );

        $this->get(route('ad.edit', $ad->id))
            ->assertOk()
            ->assertSee('Excluir imagem atual')
            ->assertSee('Excluir capa atual')
            ->assertSee('Excluir foto')
            ->assertSee('name="images[]"', false);

        $this->put(route('ad.update', $ad->id), [
            'title' => $ad->title,
            'category_name' => 'Informática',
            'price' => '5.500,00',
            'city' => $ad->city,
            'description' => $ad->description,
            'logo' => $this->fakePng('principal-nova.png'),
            'remove_banner' => '1',
            'remove_image_ids' => [$galleryImage->id],
            'images' => [$this->fakePng('galeria-nova.png')],
        ])->assertSessionHasNoErrors();

        $ad->refresh()->load('images');
        $this->assertEquals(5500, (float) $ad->price);
        $this->assertNull($ad->banner);
        $this->assertNotNull($ad->logo);
        $this->assertDatabaseMissing('ad_images', ['id' => $galleryImage->id]);
        $this->assertGreaterThanOrEqual(2, $ad->images->count());

        $this->uploadedPaths = array_merge(
            $this->uploadedPaths,
            $ad->images->pluck('image_path')->all(),
            [$ad->logo]
        );
    }

    public function test_user_can_publish_and_edit_service_with_extended_profile_kinds(): void
    {
        $user = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($user)->post(route('ad.store'), [
            'module' => 'services',
            'profile_kind' => 'liberal_professional',
            'category_name' => 'Advogado',
            'title' => 'Advogado Trabalhista em Aracaju',
            'city' => 'Aracaju',
            'description' => 'Consultoria jurídica trabalhista e previdenciária especializada.',
            'whatsapp' => '79999999999',
            'phone' => '79999999999',
            'liberal_credential' => 'OAB/SE 12.345',
            'liberal_credential_issuer' => 'Ordem dos Advogados do Brasil - Seccional Sergipe',
            'liberal_credential_url' => 'https://www.oab-se.org.br/consulta',
            'liberal_education' => 'Bacharel em Direito',
            'liberal_education_institution' => 'Universidade Tiradentes',
        ])->assertSessionHasNoErrors();

        $service = Ad::where('title', 'Advogado Trabalhista em Aracaju')->firstOrFail();
        $this->assertSame('liberal_professional', $service->profile_kind);
        $this->assertSame('Profissional liberal', $service->profile_kind_label);
        $this->assertSame('OAB/SE 12.345', data_get($service->technical_specs, 'liberal_profile.credential'));
        $this->assertSame('Bacharel em Direito', data_get($service->technical_specs, 'liberal_profile.education'));
        $this->assertFalse((bool) data_get($service->technical_specs, 'liberal_profile.credential_verified'));

        $this->get(route('provider.show', $service->slug))
            ->assertOk()
            ->assertSee('liberal-profile-page', false)
            ->assertSee('Profissional Liberal em Sergipe')
            ->assertSee('OAB/SE 12.345')
            ->assertSee('Bacharel em Direito')
            ->assertSee('Universidade Tiradentes');

        $this->actingAs($user)->put(route('ad.update', $service->id), [
            'title' => 'Escritório de Advocacia & Consultoria',
            'city' => 'Aracaju',
            'description' => 'Sociedade de advogados prestando assessoria jurídica empresarial.',
            'profile_kind' => 'cultural_artist',
            'category_name' => 'Fotógrafo',
        ])->assertSessionHasNoErrors();

        $service->refresh();
        $this->assertSame('cultural_artist', $service->profile_kind);
        $this->assertSame('Artista / Profissional da cultura', $service->profile_kind_label);

        $this->get(route('provider.show', $service->slug))
            ->assertOk()
            ->assertSee('Artista / Profissional da cultura');
    }

    public function test_service_registration_orders_description_before_contacts_and_limits_cnpj_to_companies(): void
    {
        $liberalUser = User::factory()->create(['whatsapp' => '79999999999']);
        $liberalResponse = $this->actingAs($liberalUser)->get(route('ad.create', [
            'module' => 'services',
            'profile_kind' => 'liberal_professional',
        ]));

        $liberalResponse
            ->assertOk()
            ->assertDontSee('name="facebook"', false)
            ->assertSee('id="cnpj-field"', false)
            ->assertSee('id="cnpj" name="cnpj"', false)
            ->assertSee('class="publish-sidebar"', false)
            ->assertSee('O que você vai criar hoje?')
            ->assertSee('name="liberal_credential"', false)
            ->assertSee('name="liberal_education"', false);

        $content = $liberalResponse->getContent();
        $serviceCardPosition = strpos($content, 'id="card-choice-services"');
        $itemsCardPosition = strpos($content, 'id="card-choice-items"');
        $this->assertNotFalse($serviceCardPosition);
        $this->assertNotFalse($itemsCardPosition);
        $this->assertLessThan($itemsCardPosition, $serviceCardPosition);
        $this->assertStringContainsString('is-active', substr($content, $serviceCardPosition - 100, 100));
        $this->assertMatchesRegularExpression('/id="mod_services"[^>]*checked/', $content);
        $descriptionPosition = strpos($content, 'id="description-label"');
        $contactPosition = strpos($content, 'Informações de contato e atendimento');
        $this->assertNotFalse($descriptionPosition);
        $this->assertNotFalse($contactPosition);
        $this->assertLessThan($contactPosition, $descriptionPosition);
        preg_match('/<div[^>]*id="cnpj-field"[^>]*>/', $content, $liberalCnpjField);
        preg_match('/<input[^>]*id="cnpj"[^>]*>/', $content, $liberalCnpjInput);
        $this->assertStringContainsString('d-none', $liberalCnpjField[0] ?? '');
        $this->assertStringContainsString('disabled', $liberalCnpjInput[0] ?? '');

        $this->actingAs($liberalUser)->post(route('ad.store'), [
            'module' => 'services',
            'profile_kind' => 'liberal_professional',
            'category_name' => 'Advogado',
            'title' => 'Profissional liberal sem documentação',
            'city' => 'Aracaju',
            'description' => 'Tentativa usada para validar a obrigatoriedade do registro profissional.',
            'whatsapp' => '79999999999',
            'phone' => '79999999999',
        ])->assertSessionHasErrors(['liberal_credential', 'liberal_credential_issuer']);

        $this->actingAs($liberalUser)->post(route('ad.store'), [
            'module' => 'services',
            'profile_kind' => 'liberal_professional',
            'category_name' => 'Advogado',
            'title' => 'Profissional liberal sem CNPJ',
            'city' => 'Aracaju',
            'description' => 'Perfil profissional usado para validar os campos do cadastro.',
            'whatsapp' => '79999999999',
            'phone' => '79999999999',
            'cnpj' => '12.345.678/0001-90',
            'liberal_credential' => 'OAB/SE 98.765',
            'liberal_credential_issuer' => 'Ordem dos Advogados do Brasil - Seccional Sergipe',
        ])->assertSessionHasNoErrors();

        $this->assertNull(Ad::where('title', 'Profissional liberal sem CNPJ')->value('cnpj'));

        $companyUser = User::factory()->create(['whatsapp' => '79988887777']);
        $companyResponse = $this->actingAs($companyUser)->get(route('ad.create', [
            'module' => 'services',
            'profile_kind' => 'service_company',
        ]));

        $companyContent = $companyResponse->assertOk()->getContent();
        preg_match('/<div[^>]*id="cnpj-field"[^>]*>/', $companyContent, $companyCnpjField);
        preg_match('/<input[^>]*id="cnpj"[^>]*>/', $companyContent, $companyCnpjInput);
        $this->assertStringNotContainsString('d-none', $companyCnpjField[0] ?? 'missing');
        $this->assertStringNotContainsString('disabled', $companyCnpjInput[0] ?? 'missing');

        $this->actingAs($companyUser)->post(route('ad.store'), [
            'module' => 'services',
            'profile_kind' => 'service_company',
            'category_name' => 'Assistência Técnica',
            'title' => 'Empresa de assistência com CNPJ',
            'city' => 'Aracaju',
            'description' => 'Empresa utilizada para validar o cadastro empresarial.',
            'whatsapp' => '79988887777',
            'phone' => '79988887777',
            'cnpj' => '12.345.678/0001-90',
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            '12.345.678/0001-90',
            Ad::where('title', 'Empresa de assistência com CNPJ')->value('cnpj')
        );
    }

    private function fakePng(string $name): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        );

        return UploadedFile::fake()
            ->createWithContent($name, $png)
            ->mimeType('image/png');
    }

    protected function tearDown(): void
    {
        foreach (array_unique($this->uploadedPaths) as $path) {
            @unlink(public_path($path));
        }

        parent::tearDown();
    }
}
