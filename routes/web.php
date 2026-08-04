<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPlanController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminUpdateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationPreferenceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductFavoriteController;
use App\Http\Controllers\ProductQuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoreBusinessHoursController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\StorePromotionController;
use App\Http\Controllers\CultureWorkController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/busca/sugestoes', [HomeController::class, 'suggestions'])
    ->middleware('throttle:60,1')
    ->name('search.suggestions');
Route::get('/home/sugestoes', [HomeController::class, 'suggestions'])
    ->middleware('throttle:60,1')
    ->name('home.suggestions');
Route::get('/anuncio/{slug}', [AdController::class, 'show'])->name('ad.show');
Route::get('/anuncio/{slug}/publicado', [AdController::class, 'published'])->name('ad.published');
Route::get('/prestador/{slug}', [AdController::class, 'provider'])->name('provider.show');
Route::post('/anuncio/{ad}/denunciar', [ReportController::class, 'store'])->middleware('throttle:5,1')->name('reports.store');
Route::post('/loja/{store}/denunciar', [ReportController::class, 'storeReport'])->middleware('throttle:5,1')->name('store.reports.store');
Route::get('/denuncia/{publicId}/obrigado', [ReportController::class, 'thankYou'])->name('reports.thank_you');

// Gerenciamento de Anúncios CRUD
Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::get('/anunciar', [AdController::class, 'create'])->name('ad.create');
    Route::post('/anunciar', [AdController::class, 'store'])->name('ad.store');
    Route::get('/anuncio/{id}/editar', [AdController::class, 'edit'])->name('ad.edit');
    Route::put('/anuncio/{id}/atualizar', [AdController::class, 'update'])->name('ad.update');
    Route::delete('/anuncio/{id}/excluir', [AdController::class, 'destroy'])->name('ad.destroy');
    Route::post('/produtos/{product}/favorito', [ProductFavoriteController::class, 'toggle'])->middleware('throttle:20,1')->name('products.favorite.toggle');
    Route::post('/produtos/{product}/perguntas', [ProductQuestionController::class, 'store'])->middleware('throttle:5,1')->name('products.questions.store');
    Route::post('/perguntas/{question}/resposta', [ProductQuestionController::class, 'answer'])->middleware('throttle:10,1')->name('products.questions.answer');
    Route::post('/anuncio/{ad}/avaliacoes', [ReviewController::class, 'store'])->middleware('throttle:5,1')->name('reviews.store');
    Route::post('/loja/{store}/avaliacoes', [ReviewController::class, 'storeReview'])->middleware('throttle:5,1')->name('store.reviews.store');
    Route::put('/avaliacoes/{review}', [ReviewController::class, 'update'])->middleware('throttle:10,1')->name('reviews.update');
    Route::delete('/avaliacoes/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/avaliacoes/{review}/resposta', [ReviewController::class, 'storeProfessionalReply'])->middleware('throttle:10,1')->name('reviews.reply.store');
    Route::put('/avaliacoes/{review}/resposta', [ReviewController::class, 'updateProfessionalReply'])->middleware('throttle:10,1')->name('reviews.reply.update');
    Route::delete('/avaliacoes/{review}/resposta', [ReviewController::class, 'destroyProfessionalReply'])->name('reviews.reply.destroy');
    Route::post('/avaliacoes/{review}/denunciar', [ReviewController::class, 'report'])->middleware('throttle:5,1')->name('reviews.report');
});

// Lojas & Empresas
Route::get('/lojas', [StoreController::class, 'index'])->name('stores.index');
Route::get('/lojas/{store:slug}/produtos/{product:slug}', [StoreProductController::class, 'show'])
    ->scopeBindings()
    ->name('store.products.show');
Route::get('/loja/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::post('/loja/{store}/evento', [StoreController::class, 'recordEvent'])
    ->middleware('throttle:120,1')
    ->name('store.events.store');

// Carrinho em sessão
Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrinho/adicionar/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/carrinho/item/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/item/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/carrinho', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/checkout/cupom', [CartController::class, 'applyCoupon'])->name('checkout.coupon.apply');
Route::delete('/checkout/cupom', [CartController::class, 'removeCoupon'])->name('checkout.coupon.remove');

// Painel do Anunciante & Perfil
Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::get('/usuario/painel', [UserController::class, 'panel'])->name('user.panel');
    Route::get('/usuario/perfil', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/usuario/perfil', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::post('/usuario/avatar', [UserController::class, 'updateAvatar'])->name('user.avatar.update');
    Route::get('/usuario/configuracoes', [UserController::class, 'settings'])->name('user.settings');
    Route::post('/usuario/configuracoes', [UserController::class, 'updateSettings'])->name('user.settings.update');
    Route::get('/usuario/notificacoes/{notification}', [UserController::class, 'openNotification'])->name('user.notifications.open');
    Route::post('/usuario/notificacoes/preferencia', [UserController::class, 'updateNotificationPreference'])->name('user.notifications.preference');
    Route::post('/usuario/disponibilidade', [UserController::class, 'updateAvailability'])->name('user.availability.update');

    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout', [OrderController::class, 'place'])->name('checkout.place');
    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/pedidos/{order}/cancelar', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/minha-loja/criar', [StoreController::class, 'create'])->name('store.create');
    Route::post('/minha-loja', [StoreController::class, 'store'])->name('store.store');
    Route::get('/minha-loja/editar', [StoreController::class, 'edit'])->name('store.edit');
    Route::put('/minha-loja', [StoreController::class, 'update'])->name('store.update');
    Route::post('/minha-loja/status', [StoreController::class, 'toggleStatus'])->name('store.toggle_status');
    Route::delete('/minha-loja', [StoreController::class, 'destroy'])->name('store.destroy');
    Route::get('/minha-loja/{store}/editar', [StoreController::class, 'manage'])->name('store.manage');
    Route::put('/minha-loja/{store}', [StoreController::class, 'updateStore'])->name('store.update_specific');
    Route::post('/minha-loja/{store}/status', [StoreController::class, 'toggleStoreStatus'])->name('store.toggle_specific');
    Route::delete('/minha-loja/{store}', [StoreController::class, 'destroyStore'])->name('store.destroy_specific');
    Route::post('/minha-loja/{store}/promocoes', [StorePromotionController::class, 'store'])->name('store.promotions.store');
    Route::put('/minha-loja/{store}/promocoes/{promotion}', [StorePromotionController::class, 'update'])->name('store.promotions.update');
    Route::post('/minha-loja/{store}/promocoes/{promotion}/status', [StorePromotionController::class, 'toggle'])->name('store.promotions.toggle');
    Route::delete('/minha-loja/{store}/promocoes/{promotion}', [StorePromotionController::class, 'destroy'])->name('store.promotions.destroy');
    Route::put('/minha-loja/{store}/horarios', [StoreBusinessHoursController::class, 'update'])->name('store.business_hours.update');
    Route::get('/minha-loja/{store}/pedidos', [OrderController::class, 'sellerIndex'])->name('seller.orders.index');
    Route::get('/minha-loja/{store}/pedidos/{order}', [OrderController::class, 'sellerShow'])->name('seller.orders.show');
    Route::patch('/minha-loja/{store}/pedidos/{order}/status', [OrderController::class, 'updateStatus'])->name('seller.orders.status');
    Route::post('/loja/{store}/seguir', [StoreController::class, 'toggleFollow'])
        ->middleware('throttle:30,1')
        ->name('store.follow.toggle');

    // Chat de Mensagens
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/enviar', [ChatController::class, 'sendMessage'])->name('chat.send');
});

// Painel Administrativo Exclusivo e Completo
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/usuarios', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/usuarios/novo', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/usuarios/{id}/role', [AdminController::class, 'toggleUserRole'])->name('admin.users.toggle_role');

    Route::get('/anuncios', [AdminController::class, 'ads'])->name('admin.ads');
    Route::post('/anuncios/novo', [AdminController::class, 'storeAd'])->name('admin.ads.store');
    Route::post('/anuncios/{id}/status', [AdminController::class, 'toggleAdStatus'])->name('admin.ads.toggle_status');

    Route::get('/denuncias', [AdminReportController::class, 'index'])->name('admin.reports');
    Route::get('/denuncias/{report}', [AdminReportController::class, 'show'])->name('admin.reports.show');
    Route::post('/denuncias/{report}/acao', [AdminReportController::class, 'action'])->name('admin.reports.action');
    Route::get('/avaliacoes', [AdminReviewController::class, 'index'])->name('admin.reviews');
    Route::post('/avaliacoes/{review}/acao', [AdminReviewController::class, 'action'])->name('admin.reviews.action');

    Route::get('/categorias', [AdminController::class, 'categories'])->name('admin.categories');
    Route::post('/categorias/nova', [AdminController::class, 'storeCategory'])->name('admin.categories.store');

    Route::get('/lojas', [AdminController::class, 'stores'])->name('admin.stores');
    Route::get('/lojas/{store}', [AdminController::class, 'showStore'])->name('admin.stores.show');
    Route::post('/lojas/{store}/acao', [AdminController::class, 'storeAction'])->name('admin.stores.action');
    Route::get('/configuracoes', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/configuracoes', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::post('/configuracoes/modelo-anunciar', [AdminController::class, 'updatePublishPageDesign'])->name('admin.settings.publish_design');

    // Atualização do Sistema
    Route::get('/atualizacao', [AdminUpdateController::class, 'index'])->name('admin.system.update');
    Route::post('/atualizacao/executar', [AdminUpdateController::class, 'runUpdate'])->name('admin.system.update.run');

    // Gestão de Planos e Features
    Route::get('/planos', [AdminPlanController::class, 'index'])->name('admin.plans.index');
    Route::post('/planos', [AdminPlanController::class, 'store'])->name('admin.plans.store');
    Route::put('/planos/{plan}', [AdminPlanController::class, 'update'])->name('admin.plans.update');
    Route::post('/planos/{plan}/toggle', [AdminPlanController::class, 'toggleActive'])->name('admin.plans.toggle');
    Route::get('/planos/{plan}/features', [AdminPlanController::class, 'features'])->name('admin.plans.features');
    Route::post('/planos/{plan}/features', [AdminPlanController::class, 'addFeature'])->name('admin.plans.features.add');
    Route::put('/planos/{plan}/features/{featureValue}', [AdminPlanController::class, 'updateFeature'])->name('admin.plans.features.update');
    Route::delete('/planos/{plan}/features/{featureValue}', [AdminPlanController::class, 'removeFeature'])->name('admin.plans.features.remove');
    Route::post('/features', [AdminPlanController::class, 'storeFeature'])->name('admin.features.store');

    // Trocar plano de usuário + override individual
    Route::post('/usuarios/{user}/plano', [AdminPlanController::class, 'assignUserPlan'])->name('admin.users.assign_plan');
    Route::post('/usuarios/{user}/override', [AdminPlanController::class, 'setUserOverride'])->name('admin.users.feature_override');
});

// Autenticação / Registro / Recuperação de Senha
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/cadastro', [AuthController::class, 'showRegister'])->name('register');
Route::post('/cadastro', [AuthController::class, 'register']);

Route::get('/esqueci-senha', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/esqueci-senha', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Módulos Filtrados de Categorias
Route::get('/servicos', [HomeController::class, 'servicesModule'])->name('module.services');
Route::get('/produtos', [HomeController::class, 'productsModule'])->name('module.products');
Route::get('/imoveis', [HomeController::class, 'realEstateModule'])->name('module.real_estate');
Route::get('/veiculos', [HomeController::class, 'vehiclesModule'])->name('module.vehicles');
Route::get('/empregos', [HomeController::class, 'jobsModule'])->name('module.jobs');
Route::get('/agro', [HomeController::class, 'agroModule'])->name('module.agro');
Route::post('/localizacao', [LocationPreferenceController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('location.store');
Route::delete('/localizacao', [LocationPreferenceController::class, 'destroy'])
    ->middleware('throttle:10,1')
    ->name('location.destroy');

// Páginas Institucionais
Route::get('/sobre', [PageController::class, 'about'])->name('page.about');
Route::get('/planos', [PageController::class, 'plans'])->name('page.plans');
Route::get('/contato', [PageController::class, 'contact'])->name('page.contact');
Route::post('/contato', [PageController::class, 'sendContact'])
    ->middleware('throttle:5,1')
    ->name('page.contact.send');
Route::get('/privacidade', [PageController::class, 'privacy'])->name('page.privacy');
Route::get('/termos', [PageController::class, 'terms'])->name('page.terms');

// Módulo de Cultura, Cordel & Arte (Estante Pública)
Route::get('/cultura-e-cordel', [CultureWorkController::class, 'index'])->name('culture.index');
Route::get('/cordelista/{username}', [CultureWorkController::class, 'authorProfile'])->name('culture.author');
Route::get('/cultura-e-cordel/{slug}', [CultureWorkController::class, 'show'])->name('culture.show');

Route::middleware('auth')->group(function () {
    Route::post('/cultura-e-cordel/{id}/like', [CultureWorkController::class, 'toggleLike'])->name('culture.like');

    Route::get('/minhas-obras', [CultureWorkController::class, 'myWorks'])->name('culture.my-works');
    Route::get('/obras/criar', [CultureWorkController::class, 'create'])->name('culture.create');
    Route::post('/obras', [CultureWorkController::class, 'store'])->name('culture.store');
    Route::get('/obras/{id}/editar', [CultureWorkController::class, 'edit'])->name('culture.edit');
    Route::put('/obras/{id}', [CultureWorkController::class, 'update'])->name('culture.update');
    Route::delete('/obras/{id}', [CultureWorkController::class, 'destroy'])->name('culture.destroy');
});
