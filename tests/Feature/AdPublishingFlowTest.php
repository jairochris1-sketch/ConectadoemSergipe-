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
            ->assertSee('#wizard-step-5 .wizard-submit-button', false)
            ->assertSee('Qual perfil representa você?')
            ->assertSee('Prestador de serviços')
            ->assertSee('Empresa de serviços')
            ->assertSee('Loja / Comércio')
            ->assertSee('Restaurante / Alimentação')
            ->assertSee('id="service-profile-kind"', false)
            ->assertDontSee('Seus dados protegidos')
            ->assertDontSee('moduleMotionTrail', false);
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
