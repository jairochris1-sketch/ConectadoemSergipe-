<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdFavoriteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCommunityHelpController;
use App\Http\Controllers\AdminCultureController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPlanController;
use App\Http\Controllers\AdminProviderClaimController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminFeedController;
use App\Http\Controllers\AdminUpdateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CrmVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationPreferenceController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductFavoriteController;
use App\Http\Controllers\ProductQuestionController;
use App\Http\Controllers\QuickProfileController;
use App\Http\Controllers\ProviderClaimController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceBookingController;
use App\Http\Controllers\ServicePaymentSettingsController;
use App\Http\Controllers\ServiceSubscriptionController;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoreBusinessHoursController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\StorePromotionController;
use App\Http\Controllers\CultureWorkController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\CommunityHelpRequestController;
use App\Http\Controllers\AdminSupportController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rotas do Chat de Suporte Online (Público / Widget)
Route::get('/suporte/departamentos', [SupportChatController::class, 'getDepartments'])->name('support.departments');
Route::post('/suporte/iniciar', [SupportChatController::class, 'startTicket'])->middleware('throttle:15,1')->name('support.start');
Route::get('/suporte/{ticket}/status', [SupportChatController::class, 'getTicketStatus'])->name('support.status');
Route::post('/suporte/{ticket}/mensagem', [SupportChatController::class, 'sendMessage'])->middleware('throttle:60,1')->name('support.send_message');
Route::post('/suporte/{ticket}/encerrar', [SupportChatController::class, 'closeTicket'])->name('support.close');
Route::post('/suporte/{ticket}/avaliar', [SupportChatController::class, 'rateTicket'])->name('support.rate');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cadastro-rapido', [QuickProfileController::class, 'create'])->name('quick-profile.create');
Route::post('/cadastro-rapido', [QuickProfileController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('quick-profile.store');
Route::get('/bem-vindo', LandingPageController::class)->name('landing');
Route::get('/plataforma', fn () => redirect()->route('home'))->name('home.redirect');
Route::get('/lojas-e-vendas', [HomeController::class, 'storesAndSalesPage'])->name('stores-sales.index');
Route::get('/destaques', [HomeController::class, 'highlights'])->name('highlights.index');
Route::get('/comunidade', [FeedController::class, 'index'])->name('feed.index');
Route::get('/comunidade/pedidos', [CommunityHelpRequestController::class, 'index'])
    ->name('community-help.index');
Route::get('/comunidade/pedidos/novo', [CommunityHelpRequestController::class, 'create'])
    ->middleware(['auth', 'not_suspended'])
    ->name('community-help.create');
Route::get('/comunidade/pedidos/{helpRequest}/editar', [CommunityHelpRequestController::class, 'edit'])
    ->middleware(['auth', 'not_suspended'])
    ->name('community-help.edit');
Route::post('/comunidade/pedidos', [CommunityHelpRequestController::class, 'store'])
    ->middleware(['auth', 'not_suspended', 'throttle:5,60'])
    ->name('community-help.store');
Route::put('/comunidade/pedidos/{helpRequest}', [CommunityHelpRequestController::class, 'update'])
    ->middleware(['auth', 'not_suspended', 'throttle:10,60'])
    ->name('community-help.update');
Route::post('/comunidade/pedidos/{helpRequest}/responder', [CommunityHelpRequestController::class, 'respond'])
    ->middleware(['auth', 'not_suspended', 'throttle:10,1'])
    ->name('community-help.respond');
Route::post('/comunidade/pedidos/{helpRequest}/respostas/{response}/denunciar', [CommunityHelpRequestController::class, 'reportResponse'])
    ->middleware(['auth', 'not_suspended', 'throttle:5,1'])
    ->name('community-help.responses.report');
Route::patch('/comunidade/pedidos/{helpRequest}/status', [CommunityHelpRequestController::class, 'updateStatus'])
    ->middleware(['auth', 'not_suspended', 'throttle:20,1'])
    ->name('community-help.status');
Route::patch('/comunidade/pedidos/{helpRequest}/respostas/{response}/confirmar', [CommunityHelpRequestController::class, 'selectResponse'])
    ->middleware(['auth', 'not_suspended', 'throttle:20,1'])
    ->name('community-help.responses.select');
Route::patch('/comunidade/pedidos/{helpRequest}/respostas/{response}/moderacao', [CommunityHelpRequestController::class, 'moderateResponse'])
    ->middleware(['auth', 'not_suspended', 'throttle:20,1'])
    ->name('community-help.responses.moderate');
Route::patch('/comunidade/pedidos/{helpRequest}/moderacao', [CommunityHelpRequestController::class, 'moderate'])
    ->middleware(['auth', 'not_suspended', 'throttle:20,1'])
    ->name('community-help.moderate');
Route::get('/comunidade/pedidos/{helpRequest}', [CommunityHelpRequestController::class, 'show'])
    ->name('community-help.show');
Route::post('/comunidade/anuncios/{ad}/evento', [FeedController::class, 'trackAdEvent'])
    ->middleware('throttle:120,1')
    ->name('feed.ads.event');
Route::get('/perfil/{username}', [UserController::class, 'publicProfile'])
    ->where('username', '[A-Za-z0-9._]+')
    ->name('profile.show');
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
Route::get('/prestador/{ad}/agendar', [ServiceBookingController::class, 'booking'])->name('service-booking.book');
Route::post('/webhooks/asaas/{paymentSetting}', AsaasWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('service-payments.asaas-webhook');
Route::post('/anuncio/{ad}/denunciar', [ReportController::class, 'store'])->middleware('throttle:5,1')->name('reports.store');
Route::post('/loja/{store}/denunciar', [ReportController::class, 'storeReport'])->middleware('throttle:5,1')->name('store.reports.store');
Route::get('/denuncia/{publicId}/obrigado', [ReportController::class, 'thankYou'])->name('reports.thank_you');

// Gerenciamento de Anúncios CRUD
Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::post('/prestador/{ad}/agendar', [ServiceBookingController::class, 'storeAppointment'])->name('service-booking.store');
    Route::get('/prestador/{ad}/agendamentos/{appointment}/enviar-whatsapp/{event}', [ServiceBookingController::class, 'whatsappConfirmation'])->name('service-booking.whatsapp');
    Route::get('/meus-servicos/{ad}/agenda', [ServiceBookingController::class, 'manage'])->name('service-booking.manage');
    Route::patch('/meus-servicos/{ad}/agenda/status', [ServiceBookingController::class, 'toggle'])->name('service-booking.toggle');
    Route::patch('/meus-servicos/{ad}/vitrine', [ServiceBookingController::class, 'linkStore'])->name('service-booking.store-link');
    Route::post('/meus-servicos/{ad}/procedimentos', [ServiceBookingController::class, 'storeProcedure'])->name('service-booking.procedures.store');
    Route::put('/meus-servicos/{ad}/procedimentos/{procedure}', [ServiceBookingController::class, 'updateProcedure'])->name('service-booking.procedures.update');
    Route::delete('/meus-servicos/{ad}/procedimentos/{procedure}', [ServiceBookingController::class, 'destroyProcedure'])->name('service-booking.procedures.destroy');
    Route::post('/meus-servicos/{ad}/profissionais', [ServiceBookingController::class, 'storeStaff'])->name('service-booking.staff.store');
    Route::put('/meus-servicos/{ad}/profissionais/{staff}', [ServiceBookingController::class, 'updateStaff'])->name('service-booking.staff.update');
    Route::patch('/meus-servicos/{ad}/agendamentos/{appointment}', [ServiceBookingController::class, 'updateAppointment'])->name('service-booking.appointments.update');
    Route::post('/meus-servicos/{ad}/agendamentos/manual', [ServiceBookingController::class, 'storeManualAppointment'])->name('service-booking.appointments.manual');
    Route::post('/meus-servicos/{ad}/bloqueios', [ServiceBookingController::class, 'storeScheduleBlock'])->name('service-booking.blocks.store');
    Route::delete('/meus-servicos/{ad}/bloqueios/{block}', [ServiceBookingController::class, 'destroyScheduleBlock'])->name('service-booking.blocks.destroy');
    Route::patch('/prestador/{ad}/agendamentos/{appointment}/cancelar', [ServiceBookingController::class, 'cancelCustomerAppointment'])->name('service-booking.customer.cancel');
    Route::patch('/prestador/{ad}/agendamentos/{appointment}/remarcar', [ServiceBookingController::class, 'rescheduleCustomerAppointment'])->name('service-booking.customer.reschedule');
    Route::post('/meus-servicos/{ad}/financeiro', [ServiceBookingController::class, 'storeFinancialEntry'])->name('service-booking.financial.store');
    Route::put('/meus-servicos/{ad}/pagamentos', [ServicePaymentSettingsController::class, 'update'])->name('service-payments.settings.update');
    Route::post('/meus-servicos/{ad}/pagamentos/verificar', [ServicePaymentSettingsController::class, 'verify'])->name('service-payments.settings.verify');
    Route::post('/meus-servicos/{ad}/pagamentos/webhook', [ServicePaymentSettingsController::class, 'registerWebhook'])->name('service-payments.settings.webhook');
    Route::post('/meus-servicos/{ad}/planos-clientes', [ServicePaymentSettingsController::class, 'storePlan'])->name('service-subscription-plans.store');
    Route::put('/meus-servicos/{ad}/planos-clientes/{plan}', [ServicePaymentSettingsController::class, 'updatePlan'])->name('service-subscription-plans.update');
    Route::post('/prestador/{ad}/planos/{plan}/assinar', [ServiceSubscriptionController::class, 'store'])->name('service-subscriptions.store');
    Route::delete('/minhas-assinaturas/{subscription}', [ServiceSubscriptionController::class, 'cancel'])->name('service-subscriptions.cancel');
    Route::post('/comunidade/publicar', [FeedController::class, 'store'])->middleware('throttle:10,60')->name('feed.store');
    Route::post('/comunidade/{post}/curtir', [FeedController::class, 'toggleLike'])->middleware('throttle:60,1')->name('feed.like');
    Route::post('/comunidade/{post}/comentar', [FeedController::class, 'comment'])->middleware('throttle:20,1')->name('feed.comment');
    Route::post('/comunidade/{post}/denunciar', [FeedController::class, 'report'])->middleware('throttle:5,1')->name('feed.report');
    Route::post('/comunidade/{post}/votar', [FeedController::class, 'vote'])->middleware('throttle:30,1')->name('feed.vote');
    Route::patch('/comunidade/{post}', [FeedController::class, 'update'])->name('feed.update');
    Route::patch('/comunidade/{post}/fixar', [FeedController::class, 'togglePin'])->name('feed.pin');
    Route::delete('/comunidade/{post}', [FeedController::class, 'destroy'])->name('feed.destroy');
    Route::get('/prestador/{ad}/reivindicar', [ProviderClaimController::class, 'create'])->name('provider.claim.create');
    Route::post('/prestador/{ad}/reivindicar', [ProviderClaimController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('provider.claim.store');

    Route::get('/anunciar', [AdController::class, 'create'])->name('ad.create');
    Route::post('/profissionais/consultar-crm', CrmVerificationController::class)
        ->middleware('throttle:10,1')
        ->name('professionals.crm.verify');
    Route::post('/anunciar', [AdController::class, 'store'])->name('ad.store');
    Route::get('/anuncio/{id}/editar', [AdController::class, 'edit'])->name('ad.edit');
    Route::post('/anuncio/{ad}/posicao-capa', [AdController::class, 'updateCoverPosition'])->name('ad.cover_position');
    Route::put('/anuncio/{id}/atualizar', [AdController::class, 'update'])->name('ad.update');
    Route::delete('/anuncio/{id}/excluir', [AdController::class, 'destroy'])->name('ad.destroy');
    Route::post('/anuncios/{ad}/favorito', [AdFavoriteController::class, 'store'])->middleware('throttle:30,1')->name('ads.favorite.store');
    Route::delete('/anuncios/{ad}/favorito', [AdFavoriteController::class, 'destroy'])->middleware('throttle:30,1')->name('ads.favorite.destroy');
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
    Route::post('/chat/{user}/bloquear', [ChatController::class, 'blockUser'])->name('chat.block');
    Route::post('/chat/{user}/desbloquear', [ChatController::class, 'unblockUser'])->name('chat.unblock');
    Route::post('/chat/{user}/denunciar', [ChatController::class, 'reportUser'])->name('chat.report');
});

// Painel Administrativo Exclusivo e Completo
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/usuarios', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/usuarios/novo', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/usuarios/{id}/editar', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/usuarios/{id}/role', [AdminController::class, 'toggleUserRole'])->name('admin.users.toggle_role');
    Route::post('/usuarios/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
    Route::delete('/usuarios/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::get('/anuncios', [AdminController::class, 'ads'])->name('admin.ads');
    Route::post('/anuncios/novo', [AdminController::class, 'storeAd'])->name('admin.ads.store');
    Route::post('/anuncios/{id}/editar', [AdminController::class, 'updateAd'])->name('admin.ads.update');
    Route::post('/anuncios/{id}/status', [AdminController::class, 'toggleAdStatus'])->name('admin.ads.toggle_status');
    Route::delete('/anuncios/{id}', [AdminController::class, 'destroyAd'])->name('admin.ads.destroy');
    Route::post('/anuncios/{ad}/reivindicacao', [AdminController::class, 'toggleProviderClaiming'])->name('admin.ads.toggle_claiming');
    Route::get('/reivindicacoes', [AdminProviderClaimController::class, 'index'])->name('admin.provider_claims.index');
    Route::post('/reivindicacoes/{claim}/analisar', [AdminProviderClaimController::class, 'review'])->name('admin.provider_claims.review');

    Route::get('/denuncias', [AdminReportController::class, 'index'])->name('admin.reports');
    Route::get('/denuncias/{report}', [AdminReportController::class, 'show'])->name('admin.reports.show');
    Route::post('/denuncias/{report}/acao', [AdminReportController::class, 'action'])->name('admin.reports.action');
    Route::get('/avaliacoes', [AdminReviewController::class, 'index'])->name('admin.reviews');
    Route::post('/avaliacoes/{review}/acao', [AdminReviewController::class, 'action'])->name('admin.reviews.action');
    Route::get('/comunidade', [AdminFeedController::class, 'index'])->name('admin.feed.index');
    Route::post('/comunidade/{post}/acao', [AdminFeedController::class, 'action'])->name('admin.feed.action');
    Route::get('/cultura', [AdminCultureController::class, 'index'])->name('admin.culture.index');
    Route::post('/cultura/{work}/acao', [AdminCultureController::class, 'action'])->name('admin.culture.action');
    Route::get('/ajuda-comunitaria', [AdminCommunityHelpController::class, 'index'])->name('admin.community-help.index');
    Route::get('/ajuda-comunitaria/{helpRequest}', [AdminCommunityHelpController::class, 'show'])->name('admin.community-help.show');

    Route::get('/categorias', [AdminController::class, 'categories'])->name('admin.categories');
    Route::post('/categorias/nova', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/categorias/{category}', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::post('/categorias/{category}/status', [AdminController::class, 'toggleCategoryStatus'])->name('admin.categories.toggle');
    Route::delete('/categorias/{category}', [AdminController::class, 'deleteCategory'])->name('admin.categories.destroy');

    Route::get('/lojas', [AdminController::class, 'stores'])->name('admin.stores');
    Route::get('/lojas/{store}', [AdminController::class, 'showStore'])->name('admin.stores.show');
    Route::post('/lojas/{store}/acao', [AdminController::class, 'storeAction'])->name('admin.stores.action');
    Route::get('/pedidos', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/pedidos/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/pedidos/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');
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

    // Central de Multiatendimento & Suporte ao Vivo (Filas, Atendentes, Chat)
    Route::get('/suporte', [AdminSupportController::class, 'index'])->name('admin.support.index');
    Route::get('/suporte/fila-data', [AdminSupportController::class, 'getQueueData'])->name('admin.support.queue_data');
    Route::get('/suporte/ticket/{ticket}', [AdminSupportController::class, 'getTicketDetails'])->name('admin.support.ticket_details');
    Route::post('/suporte/atender/{ticket}', [AdminSupportController::class, 'claimTicket'])->name('admin.support.claim');
    Route::post('/suporte/transferir/{ticket}', [AdminSupportController::class, 'transferTicket'])->name('admin.support.transfer');
    Route::post('/suporte/mensagem/{ticket}', [AdminSupportController::class, 'sendMessage'])->name('admin.support.send_message');
    Route::post('/suporte/encerrar/{ticket}', [AdminSupportController::class, 'closeTicket'])->name('admin.support.close');
    Route::get('/suporte/respostas-rapidas', [AdminSupportController::class, 'cannedResponses'])->name('admin.support.canned_responses');
    Route::post('/suporte/respostas-rapidas', [AdminSupportController::class, 'storeCannedResponse'])->name('admin.support.store_canned');
});

// Autenticação / Registro / Recuperação de Senha
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::get('/cadastro', [AuthController::class, 'showRegister'])->name('register');
Route::get('/cadastro/concluido', [AuthController::class, 'showRegistrationSuccess'])->name('register.success');
Route::get('/cadastro/sugestoes-usuario', [AuthController::class, 'suggestUsernames'])->name('register.suggest-usernames');
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
Route::get('/cordelista/{identifier}', [CultureWorkController::class, 'authorProfile'])->name('culture.author');
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
