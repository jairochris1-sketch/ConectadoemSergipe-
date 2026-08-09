@extends('layouts.app')
@section('title', 'Comunidade - Conectado em Sergipe')
@if($sharedPost)
@php
    $sharedVideoUrl = $sharedPost->video_path ? asset($sharedPost->video_path) : $sharedPost->video_url;
    $sharedPageUrl = route('feed.index', ['post' => $sharedPost->id]);
    $sharedTitle = $sharedPost->title ?: 'Publicação do Conectado em Sergipe';
    $sharedDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string) $sharedPost->body)), 180) ?: 'Veja esta publicação da Comunidade Sergipana.';
    $sharedImageUrl = $sharedPost->images->isNotEmpty() ? asset($sharedPost->images->first()->path) : asset('images/logo-hero.png');
    $sharedVideoExtension = strtolower(pathinfo((string) parse_url($sharedVideoUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
    $sharedVideoType = in_array($sharedVideoExtension, ['mov', 'm4v'], true) ? 'video/quicktime' : 'video/mp4';
@endphp
@push('meta')
<meta property="og:type" content="{{ $sharedVideoUrl ? 'video.other' : 'article' }}">
<meta property="og:site_name" content="Conectado em Sergipe">
<meta property="og:title" content="{{ $sharedTitle }}">
<meta property="og:description" content="{{ $sharedDescription }}">
<meta property="og:url" content="{{ $sharedPageUrl }}">
<meta property="og:image" content="{{ $sharedImageUrl }}">
@if($sharedVideoUrl)
<meta property="og:video" content="{{ $sharedVideoUrl }}">
<meta property="og:video:secure_url" content="{{ $sharedVideoUrl }}">
<meta property="og:video:type" content="{{ $sharedVideoType }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $sharedTitle }}">
<meta name="twitter:description" content="{{ $sharedDescription }}">
<meta name="twitter:image" content="{{ $sharedImageUrl }}">
@endpush
@endif
@push('styles')
<style>
.community-page{background:#f4f6f8;min-height:80vh}.community-shell.container{width:calc(100% - 24px);max-width:680px!important;padding-left:0;padding-right:0}.community-card{border:0;border-radius:18px;overflow:hidden}.community-avatar{width:42px;height:42px;object-fit:cover}.community-image{width:100%;max-height:680px;object-fit:cover;background:#eef1f4}.community-action{border:0;background:transparent;font-weight:700;color:var(--foreground)}.community-action.is-liked{color:#dc3545}.community-comment{background:var(--background);border-radius:12px}.community-compose textarea{resize:none}.community-grid-images{display:grid;grid-template-columns:repeat(2,1fr);gap:2px}.community-grid-images img:first-child:last-child{grid-column:1/-1}.community-notice{border-left:5px solid #0d6efd!important}.community-notice.is-important{border-left-color:#ffc107!important}.community-notice.is-urgent{border-left-color:#dc3545!important}.community-poll-option{display:block;border:1px solid #dee2e6;border-radius:12px;padding:.7rem .85rem;margin-bottom:.55rem}.community-poll-result{display:block;height:6px;background:#e9ecef;border-radius:99px;overflow:hidden}.community-poll-result span{display:block;height:100%;background:#0d6efd}.community-user-link,.community-user-link:visited{color:#174f91;font-weight:700;text-decoration:none}.community-user-link:hover,.community-user-link:focus{color:#0c376c;text-decoration:none}.community-user-link:focus-visible{outline:2px solid rgba(23,79,145,.3);outline-offset:2px;border-radius:2px}.community-meta-link,.community-meta-link:visited{color:inherit;text-decoration:none}.community-meta-link:hover,.community-meta-link:focus{text-decoration:none}.community-official-badge{color:#174f91;font-size:.85em}.community-body{color:var(--bs-body-color,#24292f)}.community-search-label{display:block;color:var(--bs-body-color,#24292f);font-size:1rem;font-weight:700;margin-bottom:.45rem}.community-search-control{display:flex;width:100%;height:42px}.community-search-input{min-width:0;flex:1;border:1px solid #aeb8c4;border-right:0;border-radius:2px 0 0 2px;background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#24292f);padding:.55rem .75rem;outline:0}.community-search-input:focus{border-color:#174f91;box-shadow:inset 0 0 0 1px #174f91}.community-search-button{width:48px;border:1px solid #174f91;border-radius:0 2px 2px 0;background:#174f91;color:#fff}.community-search-button:hover,.community-search-button:focus{background:#0c376c}.community-search-clear{color:#174f91;font-size:.8rem;font-weight:600;text-decoration:none}.community-search-clear:hover,.community-search-clear:focus{color:#0c376c;text-decoration:none}
</style>
<style>
.community-topbar{position:sticky;top:0;z-index:1030;height:52px;background:#174f91;border-bottom:1px solid #0c376c;color:#fff}.community-topbar-inner{width:calc(100% - 24px);max-width:1180px;margin:0 auto;height:100%;display:flex;align-items:center;gap:24px}.community-brand,.community-brand:visited{color:#fff;font-size:1.25rem;font-weight:800;letter-spacing:-.03em;text-decoration:none;white-space:nowrap}.community-brand span{color:#bcd7f4}.community-topnav{display:flex;align-self:stretch}.community-topnav a,.community-topnav a:visited{display:flex;align-items:center;padding:0 13px;color:#d9e7f5;font-size:.84rem;font-weight:700;text-decoration:none;border-left:1px solid transparent;border-right:1px solid transparent}.community-topnav a:hover,.community-topnav a:focus,.community-topnav a.is-active{color:#fff;background:rgba(0,0,0,.12);border-color:rgba(255,255,255,.08)}.community-top-actions{margin-left:auto;display:flex;align-items:center;gap:12px}.community-top-actions a,.community-top-actions button{border:0;background:none;padding:0;color:#d9e7f5;font-size:.78rem;font-weight:700;text-decoration:none}.community-top-actions a:hover,.community-top-actions a:focus,.community-top-actions button:hover,.community-top-actions button:focus{color:#fff}.community-page{min-height:calc(100vh - 52px);padding:22px 0 48px;background:#edf0f2}.community-layout{width:calc(100% - 24px);max-width:1180px;margin:0 auto;display:grid;grid-template-columns:210px minmax(0,620px) 270px;justify-content:center;align-items:start;gap:16px}.community-sidebar{position:sticky;top:74px;min-width:0}.community-side-section{padding:0 0 16px;margin-bottom:16px;border-bottom:1px solid #c6cdd4}.community-side-title{margin:0 0 9px;color:#172033;font-size:.85rem;font-weight:800;letter-spacing:.02em}.community-side-nav{display:flex;flex-direction:column;gap:2px}.community-side-nav a,.community-side-nav a:visited{display:flex;align-items:center;gap:9px;padding:6px 7px;color:#405a78;font-size:.82rem;font-weight:600;text-decoration:none;border-radius:2px}.community-side-nav a:hover,.community-side-nav a:focus{background:#dde4ea;color:#174f91}.community-side-nav i{width:17px;text-align:center;color:#597b9f}.community-feed{min-width:0}.community-feed-heading{padding:0 2px 13px;margin-bottom:12px;border-bottom:1px solid #bcc5cd}.community-feed-heading h1{margin:0;color:#172033;font-size:1.45rem;font-weight:800}.community-feed-heading p{margin:.25rem 0 0;color:#66778a;font-size:.83rem}.community-card{border:1px solid #c5ccd3!important;border-radius:3px!important;box-shadow:0 1px 2px rgba(21,35,51,.06)!important;margin-bottom:12px!important}.community-card .card-body{padding:.85rem!important}.community-comment{border:1px solid #d9dee3;border-radius:2px;background:#f5f7f8}.community-compose textarea{border:1px solid #d0d6dc!important;border-radius:2px!important}.community-search{margin:0}.community-search-label{font-size:.85rem;margin-bottom:.4rem}.community-search-control{height:32px}.community-search-input{padding:.3rem .45rem;font-size:.82rem}.community-search-button{width:36px}.community-mobile-search{display:none;margin-bottom:14px}.community-side-card{border:1px solid #bfc7cf;background:#f7f8f9}.community-side-block{padding:11px 12px;border-bottom:1px solid #d1d7dd}.community-side-block:last-child{border-bottom:0}.community-side-block h2{margin:0 0 9px;color:#172033;font-size:.85rem;font-weight:800;letter-spacing:.02em}.community-side-item{display:block;margin-bottom:8px;color:#405a78;font-size:.78rem;line-height:1.35;text-decoration:none}.community-side-item:last-child{margin-bottom:0}.community-side-item:hover,.community-side-item:focus{color:#174f91}.community-side-item strong{color:#174f91}.community-side-empty{margin:0;color:#6b7885;font-size:.76rem}.community-notification-count{display:flex;align-items:center;gap:7px;color:#405a78;font-size:.8rem;text-decoration:none}.community-notification-count i{color:#597b9f}.community-page .alert{border-radius:3px!important}.community-page .rounded-pill{border-radius:3px!important}
@media(max-width:991.98px){.community-layout{max-width:680px;display:block}.community-sidebar{display:none}.community-mobile-search{display:block}.community-topnav{display:none}.community-feed-heading h1{font-size:1.25rem}.community-topbar-inner{max-width:680px}.community-top-actions{gap:9px}}
@media(max-width:575.98px){.community-topbar{height:48px}.community-brand{font-size:1rem}.community-brand span{display:none}.community-top-actions a:nth-child(n+3){display:none}.community-layout{width:calc(100% - 16px)}.community-page{padding-top:14px}.community-card .card-body{padding:.72rem!important}.community-feed-heading p{font-size:.78rem}}
[data-bs-theme="dark"] .community-page{background:#111820}[data-bs-theme="dark"] .community-feed-heading h1,[data-bs-theme="dark"] .community-side-title,[data-bs-theme="dark"] .community-side-block h2{color:#edf3f8}[data-bs-theme="dark"] .community-side-card,[data-bs-theme="dark"] .community-comment{background:#18212b;border-color:#35414d}[data-bs-theme="dark"] .community-card{border-color:#35414d!important}[data-bs-theme="dark"] .community-side-nav a,[data-bs-theme="dark"] .community-side-item,[data-bs-theme="dark"] .community-notification-count{color:#b9c8d6}
.community-page{min-height:80vh;padding:1.5rem 0 3rem;background:#f4f6f8}.community-shell.container{width:calc(100% - 24px);max-width:680px!important;padding-left:0;padding-right:0}.community-card{border:0!important;border-radius:18px!important;box-shadow:0 .125rem .25rem rgba(0,0,0,.075)!important;margin-bottom:1.5rem!important}.community-card .card-body{padding:1rem!important}.community-comment{border:0;border-radius:12px}.community-page .alert{border-radius:1rem!important}.community-page .rounded-pill{border-radius:50rem!important}.community-search{margin-bottom:1.25rem}.community-search-label{font-size:1rem;margin-bottom:.45rem}.community-search-control{height:42px}.community-search-input{padding:.55rem .75rem;font-size:1rem}.community-search-button{width:48px}.community-explore{margin-bottom:1.5rem;padding:12px 14px;border:1px solid #d7dde3;border-radius:10px;background:var(--bs-body-bg,#fff)}.community-explore-title{margin:0 0 9px;color:var(--bs-body-color,#24292f);font-size:.85rem;font-weight:800}.community-explore-links{display:flex;gap:7px;overflow-x:auto;padding-bottom:2px;scrollbar-width:thin}.community-explore-links a,.community-explore-links a:visited{display:inline-flex;align-items:center;gap:5px;flex:0 0 auto;padding:6px 9px;border:1px solid #d7dde3;border-radius:7px;color:#174f91;background:#f8fafc;font-size:.76rem;font-weight:700;text-decoration:none}.community-explore-links a:hover,.community-explore-links a:focus{border-color:#174f91;background:#edf4fb;color:#0c376c}.community-compose.community-card{border:1px solid #b9c2cc!important;border-radius:14px!important;box-shadow:none!important}.community-compose .card-body{padding:18px!important}.community-compose-title{margin:0 0 15px;color:#174f91;font-size:1.55rem;font-weight:800;letter-spacing:-.02em}.community-compose-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}.community-compose-tools{display:flex;align-items:center;gap:8px;min-width:0}.community-compose-type{width:auto;min-width:112px;height:36px;border:1px solid #aeb8c4;border-radius:8px;background:#eef1f4;color:#25364a;font-size:.82rem;font-weight:700}.community-format-tools{display:flex;gap:5px}.community-format-button{width:36px;height:36px;border:1px solid #aeb8c4;border-radius:8px;background:#e7ebef;color:#174f91;font-weight:800}.community-format-button:hover,.community-format-button:focus{border-color:#174f91;background:#dce7f2}.community-compose-file{display:inline-flex;align-items:center;gap:6px;height:36px;margin:0;padding:0 10px;border:1px solid #aeb8c4;border-radius:8px;background:#e7ebef;color:#174f91;font-size:.8rem;font-weight:700;cursor:pointer}.community-compose-file:hover,.community-compose-file:focus-within{background:#dce3e9}.community-compose-submit{height:36px;padding:0 18px;border:1px solid #aeb8c4;border-radius:8px;background:#e7ebef;color:#172033;font-size:.86rem;font-weight:800}.community-compose-submit:hover,.community-compose-submit:focus{border-color:#174f91;background:#dce7f2;color:#174f91}.community-compose-editor{min-height:132px!important;margin:0 0 10px!important;padding:10px!important;border:1px solid #9faab5!important;border-top:2px solid #60758a!important;border-radius:10px!important;background:var(--bs-body-bg,#fff)!important;color:var(--bs-body-color,#24292f);font-size:1rem;line-height:1.45;resize:vertical!important}.community-compose-options{padding-top:10px;border-top:1px solid #d5dbe1}.community-compose-options .form-control,.community-compose-options .form-select{border-radius:8px;font-size:.82rem}.community-compose-help{display:block;margin-top:7px;color:#687788;font-size:.73rem}.community-inline-link,.community-inline-link:visited{color:#174f91;font-weight:700;text-decoration:none}.community-inline-link:hover,.community-inline-link:focus{color:#0c376c;text-decoration:none}.community-link-modal{width:min(1100px,calc(100% - 32px));height:min(760px,calc(100vh - 32px));max-width:none;max-height:none;padding:0;border:0;border-radius:16px;background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#24292f);overflow:hidden;box-shadow:0 24px 70px rgba(15,23,42,.35)}.community-link-modal::backdrop{background:rgba(15,23,42,.62);backdrop-filter:blur(3px)}.community-link-modal-shell{height:100%;display:flex;flex-direction:column}.community-link-modal-header{min-height:52px;display:flex;align-items:center;gap:10px;padding:9px 12px 9px 16px;border-bottom:1px solid #d8dee5}.community-link-modal-title{min-width:0;flex:1;margin:0;color:#174f91;font-size:.95rem;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.community-link-modal-external,.community-link-modal-close{width:34px;height:34px;display:grid;place-items:center;border:1px solid #cbd3db;border-radius:8px;background:#f4f6f8;color:#174f91;text-decoration:none}.community-link-modal-frame{width:100%;flex:1;border:0;background:#fff}@media(max-width:575.98px){.community-compose .card-body{padding:14px!important}.community-compose-title{font-size:1.25rem}.community-compose-toolbar{align-items:stretch;flex-wrap:wrap}.community-compose-tools{width:100%;gap:5px;flex-wrap:wrap}.community-compose-type{min-width:95px;flex:1}.community-compose-file span{display:none}.community-compose-submit{margin-left:auto;padding:0 14px}.community-compose-editor{min-height:110px!important}.community-link-modal{width:calc(100% - 12px);height:calc(100vh - 12px);border-radius:12px}}
</style>
<style>
.community-post-actions{display:flex;align-items:center;gap:2px}.community-card.is-removing{opacity:.45;pointer-events:none;transition:opacity .18s ease}.community-live-status{position:fixed;right:18px;bottom:18px;z-index:1090;max-width:min(360px,calc(100% - 36px));padding:10px 14px;border-radius:10px;background:#174f91;color:#fff;font-size:.88rem;font-weight:700;box-shadow:0 8px 24px rgba(15,23,42,.25)}.community-live-status.is-error{background:#b42318}.community-edit-modal .modal-content{border:0;border-radius:16px}.community-edit-modal textarea{min-height:130px;resize:vertical}.community-topic-urgent{border:1px solid #f2b8bd!important;border-left:5px solid #c62828!important;background:#fff4f4}.community-topic-important{border:1px solid #efd58b!important;border-left:5px solid #d99700!important;background:#fffaf0}.community-topic-informative{border:1px solid #a9caed!important;border-left:5px solid #174f91!important;background:#f4f8fd}.community-topic-updates{border:1px solid #c8b9e8!important;border-left:5px solid #6f42c1!important;background:#f8f5fd}.community-topic-security{border:1px solid #9eb6cf!important;border-left:5px solid #0c376c!important;background:#f1f6fa}.community-topic-culture{border:1px solid #aad5b5!important;border-left:5px solid #21833b!important;background:#f3faf5}.community-topic-badge{display:inline-flex;align-items:center;gap:4px;margin-left:5px;padding:2px 6px;border-radius:6px;background:rgba(23,79,145,.09);color:#40556d;font-size:.68rem;font-weight:700}.community-pinned-badge{color:#a66700}
</style>
<style>
.community-compose-options>.form-select,.community-compose-options>.form-control{width:100%;height:40px;border-color:#cbd3dc;border-radius:8px}.community-topic-urgent{background:linear-gradient(135deg,#ff6767,#ee4545)!important;border:1px solid #e94a4a!important}.community-topic-important{background:linear-gradient(135deg,#d99b22,#b87608)!important;border:1px solid #bd7d10!important}.community-topic-informative{background:linear-gradient(135deg,#347ccc,#174f91)!important;border:1px solid #2868ad!important}.community-topic-updates{background:linear-gradient(135deg,#9256dc,#6934bd)!important;border:1px solid #7440c4!important}.community-topic-security{background:linear-gradient(135deg,#315e91,#0c376c)!important;border:1px solid #174f91!important}.community-topic-culture{background:linear-gradient(135deg,#3da45a,#207d3b)!important;border:1px solid #278844!important}.community-card[class*="community-topic-"]{border-radius:10px!important;color:#fff;overflow:hidden}.community-card[class*="community-topic-"] .community-body,.community-card[class*="community-topic-"] .community-user-link,.community-card[class*="community-topic-"] .community-user-link:visited,.community-card[class*="community-topic-"] .community-meta-link,.community-card[class*="community-topic-"] .community-meta-link:visited,.community-card[class*="community-topic-"] .community-action{color:#fff!important}.community-card[class*="community-topic-"] .text-muted{color:rgba(255,255,255,.8)!important}.community-card[class*="community-topic-"] .border-bottom{border-color:rgba(255,255,255,.24)!important}.community-card[class*="community-topic-"] .community-topic-badge{background:rgba(255,255,255,.18);color:#fff}.community-card[class*="community-topic-"] .community-pinned-badge{color:#fff}.community-card[class*="community-topic-"] .community-post-actions .btn{width:34px;height:34px;padding:0;border-radius:7px;background:rgba(255,255,255,.14);color:#fff!important}.community-card[class*="community-topic-"] .community-comment{background:rgba(255,255,255,.14);color:#fff}.community-card[class*="community-topic-"] .community-poll-option{border-color:rgba(255,255,255,.35);background:rgba(255,255,255,.1)}
@media(max-width:575.98px){.community-compose-toolbar{flex-wrap:nowrap!important;align-items:center!important;gap:6px}.community-compose-tools{width:auto!important;flex:1;flex-wrap:nowrap!important;gap:5px;overflow:hidden}.community-compose-type{width:72px;min-width:72px;flex:0 0 72px;padding-left:7px;padding-right:22px}.community-format-button{width:32px;height:32px;flex:0 0 32px}.community-compose-file{width:34px;height:32px;justify-content:center;padding:0;flex:0 0 34px}.community-compose-submit{height:34px;margin-left:0!important;padding:0 10px;white-space:nowrap}.community-compose-editor{margin-bottom:8px!important}.community-topic-badge{margin-left:3px;padding:2px 4px}.community-card[class*="community-topic-"] .card-body{padding:.8rem!important}}
.community-card[class*="community-topic-"]{color:#e5e6e6}.community-card[class*="community-topic-"] .community-body,.community-card[class*="community-topic-"] .community-user-link,.community-card[class*="community-topic-"] .community-user-link:visited,.community-card[class*="community-topic-"] .community-meta-link,.community-card[class*="community-topic-"] .community-meta-link:visited,.community-card[class*="community-topic-"] .community-inline-link,.community-card[class*="community-topic-"] .community-inline-link:visited,.community-card[class*="community-topic-"] .community-official-badge,.community-card[class*="community-topic-"] .community-action,.community-card[class*="community-topic-"] .text-muted,.community-card[class*="community-topic-"] .community-topic-badge,.community-card[class*="community-topic-"] .community-pinned-badge,.community-card[class*="community-topic-"] .community-post-actions .btn,.community-card[class*="community-topic-"] .community-comment{color:#e5e6e6!important}
.community-topic-urgent{background:#ec0203!important;border-color:#ec0203!important}.community-topic-informative{background:#064bf0!important;border-color:#064bf0!important}
.community-card[class*="community-topic-"]{background:#0e1419!important;border-color:#0e1419!important}
.community-page{color-scheme:dark;background:#080c10!important;color:#e5e6e6}.community-page h1,.community-page h2,.community-page h3,.community-page label{color:#e5e6e6}.community-page>.community-shell>.mb-4 .text-muted{color:#aeb7c0!important}.community-page .community-explore,.community-page .community-compose,.community-page .bg-white,.community-page .alert-light{background:#0e1419!important;color:#e5e6e6!important;border-color:#2b3640!important}.community-page .community-explore-title,.community-page .community-compose-title{color:#e5e6e6}.community-page .community-explore-links a,.community-page .community-explore-links a:visited{background:#151d24;color:#cbd5df;border-color:#303c47}.community-page .community-explore-links a:hover,.community-page .community-explore-links a:focus{background:#1c2730;color:#fff;border-color:#526273}.community-page .community-search-input,.community-page .form-control,.community-page .form-select{background-color:#111820!important;color:#e5e6e6!important;border-color:#394652!important}.community-page .form-control::placeholder,.community-page .community-search-input::placeholder{color:#8e99a4}.community-page .community-compose-type,.community-page .community-format-button,.community-page .community-compose-file,.community-page .community-compose-submit{background:#182129;color:#e5e6e6;border-color:#3a4752}.community-page .community-compose-editor{background:#111820!important;color:#e5e6e6!important;border-color:#3a4752!important;border-top-color:#687989!important}.community-page .community-compose-options{border-color:#2c3741}.community-page .community-compose-help{color:#9ca7b1}.community-page .community-edit-modal .modal-content{background:#0e1419;color:#e5e6e6}.community-page .community-edit-modal .modal-header,.community-page .community-edit-modal .modal-footer{border-color:#2b3640}.community-link-modal{background:#0e1419;color:#e5e6e6}.community-link-modal-header{border-color:#2b3640}.community-link-modal-title{color:#e5e6e6}
.community-topic-urgent{--community-topic-accent:#ec0203}.community-topic-important{--community-topic-accent:#f0ad00}.community-topic-informative{--community-topic-accent:#064bf0}.community-topic-updates{--community-topic-accent:#8b5cf6}.community-topic-security{--community-topic-accent:#38bdf8}.community-topic-culture{--community-topic-accent:#22c55e}.community-card[class*="community-topic-"]{border:2px solid var(--community-topic-accent)!important}.community-card[class*="community-topic-"] .community-topic-badge:not(.community-pinned-badge){border:1px solid var(--community-topic-accent);background:rgba(255,255,255,.06);color:var(--community-topic-accent)!important}.community-card[class*="community-topic-"] .community-topic-badge:not(.community-pinned-badge)::after{content:"★";font-size:.72em;color:var(--community-topic-accent)}
.community-card[class*="community-topic-"]{border:1px solid #2b3640!important}.community-card[class*="community-topic-"] .community-topic-badge:not(.community-pinned-badge)::after{content:none}.community-official-badge::after{content:"★★★★★";display:inline-block;margin-left:4px;color:#f4c542;font-size:1em;letter-spacing:1px;vertical-align:0}
.community-topic-updates{--community-topic-accent:#0f8b8d}.community-card[class*="community-topic-"] .community-topic-badge:not(.community-pinned-badge){background:var(--community-topic-accent)!important;border-color:var(--community-topic-accent)!important;color:#e5e6e6!important}.community-card>.community-body{text-align:justify;text-justify:inter-word;hyphens:auto}
.community-expiration-badge{display:inline-flex;align-items:center;gap:3px;margin-left:5px;padding:2px 6px;border:1px solid #52606c;border-radius:6px;background:#182129;color:#cbd5df;font-size:.68rem;font-weight:700}
.community-card[class*="community-topic-"] .community-user-link,.community-card[class*="community-topic-"] .community-user-link:visited,.community-card[class*="community-topic-"] .community-meta-link,.community-card[class*="community-topic-"] .community-meta-link:visited,.community-card[class*="community-topic-"] .community-inline-link,.community-card[class*="community-topic-"] .community-inline-link:visited{color:#69b7ff!important;font-weight:700;text-decoration:none}.community-card[class*="community-topic-"] .community-user-link:hover,.community-card[class*="community-topic-"] .community-user-link:focus,.community-card[class*="community-topic-"] .community-meta-link:hover,.community-card[class*="community-topic-"] .community-meta-link:focus,.community-card[class*="community-topic-"] .community-inline-link:hover,.community-card[class*="community-topic-"] .community-inline-link:focus{color:#a8d7ff!important;text-decoration:none}
.community-format-button.is-active{border-color:#69b7ff!important;background:#263746!important;color:#a8d7ff!important}.community-text-left{text-align:left!important}.community-text-justify{text-align:justify!important;text-justify:inter-word;hyphens:auto}@media(max-width:575.98px){.community-compose-type{width:66px;min-width:66px;flex-basis:66px}.community-format-button{width:30px;height:32px;flex-basis:30px}.community-compose-file{width:32px;flex-basis:32px}.community-compose-submit{padding-left:8px;padding-right:8px}}
.community-card[class*="community-topic-"] .community-official-badge{color:#3b82f6!important}
.community-poll-option{cursor:pointer}.community-poll-option:has(input:checked){outline:2px solid #69b7ff;outline-offset:1px}.community-author-line{display:flex;align-items:center;gap:4px;min-width:0;white-space:nowrap}.community-official-badge{display:inline-flex;align-items:center;gap:3px;flex:0 0 auto;white-space:nowrap;vertical-align:middle}.community-official-badge::after{margin-left:0!important;white-space:nowrap}@media(max-width:575.98px){.community-post-header{flex-wrap:wrap;align-items:flex-start!important}.community-post-header>.community-avatar{width:34px;height:34px;flex:0 0 34px}.community-post-header>.flex-grow-1{min-width:calc(100% - 42px)}.community-post-header>.community-post-actions{width:100%;justify-content:flex-end;margin-top:-4px}.community-author-line .community-user-link,.community-author-line>strong{font-size:.9rem}.community-official-badge{font-size:.76em}.community-official-badge::after{font-size:1em!important;letter-spacing:0!important}}
.community-shell.container{width:calc(100% - 48px);max-width:1780px!important}.community-two-column-layout{display:grid;grid-template-columns:minmax(400px,520px) minmax(0,680px);align-items:start;gap:28px}.community-controls-column{position:sticky;top:18px;min-width:0}.community-posts-column{min-width:0}.community-controls-column .community-compose{margin-bottom:0!important}@media(max-width:1279.98px) and (min-width:992px){.community-two-column-layout{grid-template-columns:360px minmax(0,680px);gap:20px}.community-compose-toolbar{align-items:stretch;flex-wrap:wrap}.community-compose-tools{width:100%;flex-wrap:wrap}.community-compose-submit{margin-left:auto}}@media(max-width:991.98px){.community-shell.container{width:calc(100% - 24px);max-width:680px!important}.community-two-column-layout{display:block}.community-controls-column{position:static}.community-controls-column .community-compose{margin-bottom:1.5rem!important}.community-posts-column{width:100%}}
.community-poll-voters{margin-top:.65rem;padding:.55rem .7rem;border:1px solid #35414d;border-radius:8px;background:#151d24}.community-poll-voters summary{color:#a8d7ff;font-size:.76rem;font-weight:800;cursor:pointer}.community-poll-voter-group{margin-top:.55rem}.community-poll-voter-group strong{display:block;color:#e5e6e6;font-size:.75rem}.community-poll-voter-list{display:flex;flex-wrap:wrap;gap:4px 8px;margin:.3rem 0 0;padding:0;list-style:none}.community-poll-voter-list a,.community-poll-voter-list span{color:#69b7ff;font-size:.72rem;text-decoration:none}.community-poll-voter-empty{color:#8e99a4!important}
.community-video-url{margin-bottom:.5rem}.community-video-help{display:block;margin-top:4px;color:#9ca7b1;font-size:.7rem}.community-video-help.is-error{color:#ff8f8f}.community-video-shell{position:relative;width:100%;background:#05080b;overflow:hidden}.community-video{display:block;width:100%;max-height:520px;background:#05080b;object-fit:contain}.community-video-play{position:absolute;top:50%;left:50%;width:64px;height:64px;display:grid;place-items:center;padding:0;border:2px solid rgba(255,255,255,.82);border-radius:50%;background:rgba(5,8,11,.68);color:#fff;font-size:1.45rem;transform:translate(-50%,-50%);box-shadow:0 6px 20px rgba(0,0,0,.35);z-index:2}.community-video-play:hover,.community-video-play:focus{background:rgba(23,79,145,.9)}.community-video-play[hidden]{display:none}.community-video-shell+.community-grid-images,.community-video-shell+.community-image{margin-top:2px}@media(hover:none),(pointer:coarse){.community-video-play{display:none!important}}@media(max-width:575.98px){.community-compose-toolbar{display:grid!important;grid-template-columns:minmax(0,1fr) auto;align-items:start!important;gap:6px!important}.community-compose-tools{width:auto!important;min-width:0;overflow:visible!important;flex-wrap:wrap!important}.community-compose-submit{grid-column:2;grid-row:1;margin-left:0!important}.community-compose-file{position:relative;z-index:1}}
.community-compose-toolbar{align-items:flex-start}.community-compose-tools{flex-wrap:wrap;overflow:visible}.community-compose-option-row{display:contents}.community-compose-media-tools{display:flex;flex:0 0 100%;gap:5px;margin-top:2px}@media(max-width:575.98px){.community-compose-toolbar{display:flex!important;flex-wrap:wrap!important;align-items:center!important}.community-compose-tools{display:contents!important}.community-compose-type{order:1}.community-compose-submit{order:2;margin-left:auto!important}.community-compose-option-row{display:flex;order:3;flex:0 0 100%;align-items:center;gap:5px;overflow-x:auto;overflow-y:hidden;padding-bottom:1px;scrollbar-width:none}.community-compose-option-row::-webkit-scrollbar{display:none}.community-format-tools{flex:0 0 auto}.community-compose-media-tools{flex:0 0 auto;margin-top:0}}
.community-support{margin-bottom:1.5rem;padding:14px;border:1px solid #2b3640;border-radius:10px;background:#0e1419}.community-support-title{margin:0 0 5px;color:#e5e6e6;font-size:1rem;font-weight:800}.community-support-text{margin:0 0 11px;color:#aeb7c0;font-size:.78rem;line-height:1.4}.community-support-actions{display:flex;flex-wrap:wrap;gap:7px}.community-support-link{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border:1px solid #3a4752;border-radius:7px;background:#182129;color:#e5e6e6;font-size:.78rem;font-weight:700;text-decoration:none}.community-support-link:hover,.community-support-link:focus{border-color:#3b82f6;background:#1d2a35;color:#fff}.community-support-link.is-report i{color:#ff6b6b}.community-compose-file.has-file{border-color:#25a764!important;color:#8ee3b1!important;background:#153025!important}.community-upload-feedback{display:flex;align-items:center;gap:6px;min-height:20px;margin:-2px 0 8px;color:#8ee3b1;font-size:.74rem;font-weight:700}.community-upload-feedback[hidden]{display:none}.community-upload-feedback i{color:#25a764}
.community-feed-preferences{margin-bottom:14px;padding:10px 12px;border:1px solid #2b3640;border-radius:10px;background:#0e1419}.community-feed-modes{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none}.community-feed-modes::-webkit-scrollbar{display:none}.community-feed-mode{display:inline-flex;align-items:center;gap:6px;flex:0 0 auto;padding:7px 10px;border:1px solid #35414d;border-radius:8px;background:#151d24;color:#b9c8d6;font-size:.76rem;font-weight:800;text-decoration:none}.community-feed-mode:hover,.community-feed-mode:focus,.community-feed-mode.is-active{border-color:#3b82f6;background:#174f91;color:#fff}.community-feed-privacy{margin:8px 0 0;color:#96a4b1;font-size:.68rem;line-height:1.4}.community-feed-privacy a{color:#69b7ff;text-decoration:none}.community-recommended-ad{border:1px solid #2b3640!important;border-radius:10px!important;background:#0e1419!important;color:#e5e6e6;overflow:hidden}.community-ad-header{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-bottom:1px solid #27323c}.community-ad-label{display:inline-flex;align-items:center;gap:5px;padding:4px 7px;border-radius:6px;background:#16314b;color:#8ac7ff;font-size:.7rem;font-weight:800}.community-ad-label.is-sponsored{background:#174f91;color:#fff}.community-ad-dismiss{width:28px;height:28px;display:grid;place-items:center;padding:0;border:1px solid #35414d;border-radius:7px;background:#182129;color:#b9c8d6}.community-ad-dismiss:hover,.community-ad-dismiss:focus{border-color:#69b7ff;color:#fff}.community-ad-link,.community-ad-link:visited{display:block;color:inherit;text-decoration:none}.community-ad-image,.community-ad-placeholder{display:block;width:100%;height:220px}.community-ad-image{object-fit:cover}.community-ad-placeholder{display:grid;place-items:center;background:#151d24;color:#526273;font-size:3rem}.community-ad-content{padding:13px 14px}.community-ad-module{color:#69b7ff;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em}.community-ad-content h2{margin:5px 0 7px;color:#e5e6e6;font-size:1.05rem;font-weight:800}.community-ad-content p{margin:0 0 11px;color:#aeb7c0;font-size:.8rem;line-height:1.45}.community-ad-footer{display:flex;align-items:center;justify-content:space-between;gap:10px}.community-ad-city{color:#cbd5df;font-size:.75rem;font-weight:700}.community-ad-city i{color:#ef3340}.community-ad-footer strong{color:#69b7ff;font-size:.92rem}.community-ad-explanation{padding:0 14px 12px;color:#96a4b1;font-size:.7rem}.community-ad-explanation summary{color:#8ac7ff;font-weight:700;cursor:pointer}.community-ad-explanation p{margin:7px 0 0;line-height:1.4}.community-recommended-ad.is-removing{opacity:.45;pointer-events:none}.community-recommended-ad[hidden]{display:none!important}@media(max-width:575.98px){.community-ad-image,.community-ad-placeholder{height:180px}.community-ad-content{padding:11px 12px}.community-ad-content h2{font-size:.95rem}.community-feed-preferences{padding:9px}}
.community-recommended-ad .community-ad-image,.community-recommended-ad .community-ad-placeholder{height:clamp(240px,44vw,340px);background:#080c10}.community-recommended-ad .community-ad-image{object-fit:contain;object-position:center}.community-ad-footer-actions{display:inline-flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:.45rem .75rem}.community-ad-cta{display:inline-flex;align-items:center;gap:.35rem;color:#69b7ff;font-size:.82rem;font-weight:800;white-space:nowrap}.community-ad-cta i{font-size:.68rem}@media(max-width:575.98px){.community-recommended-ad .community-ad-image,.community-recommended-ad .community-ad-placeholder{height:clamp(230px,68vw,310px)}.community-ad-footer{align-items:flex-end}.community-ad-footer-actions{display:grid;justify-items:end}}
</style>
@endpush
@section('content')
<div class="community-page"><div class="container community-shell"><div class="community-two-column-layout"><aside class="community-controls-column">
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Comunidade Sergipana</h1><p class="text-muted mb-0">Atualizações e avisos da equipe do Conectado em Sergipe.</p></div>
<section class="community-support" aria-labelledby="community-support-title"><h2 id="community-support-title" class="community-support-title"><i class="fa-solid fa-headset me-1" aria-hidden="true"></i> Suporte</h2><p class="community-support-text">Precisa de ajuda? Envie uma denúncia ou fale com a equipe do Conectado em Sergipe.</p><div class="community-support-actions"><a href="{{ route('page.contact',['tipo'=>'denuncia']) }}" class="community-support-link is-report"><i class="fa-solid fa-flag" aria-hidden="true"></i> Enviar denúncia</a><a href="{{ route('page.contact') }}" class="community-support-link"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Entrar em contato</a></div></section>
<section class="community-explore" aria-labelledby="community-explore-title"><h2 id="community-explore-title" class="community-explore-title">Explorar Sergipe</h2><nav class="community-explore-links" aria-label="Explorar Sergipe"><a href="{{ route('module.services') }}"><i class="fa-solid fa-screwdriver-wrench"></i> Serviços</a><a href="{{ route('stores.index') }}"><i class="fa-solid fa-store"></i> Lojas</a><a href="{{ route('module.products') }}"><i class="fa-solid fa-bag-shopping"></i> Produtos</a><a href="{{ route('module.real_estate') }}"><i class="fa-solid fa-building"></i> Imóveis</a><a href="{{ route('module.vehicles') }}"><i class="fa-solid fa-car"></i> Veículos</a><a href="{{ route('culture.index') }}"><i class="fa-solid fa-feather-pointed"></i> Cultura</a></nav></section>
@if(session('success'))<div class="alert alert-success rounded-4">{{ session('success') }}</div>@endif
<div class="community-live-status" data-community-live-status role="status" aria-live="polite" hidden></div>
@if($errors->any())<div class="alert alert-danger rounded-4"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@auth
@if($canPublish)
<form action="{{ route('feed.store') }}" method="POST" enctype="multipart/form-data" class="card community-card community-compose shadow-sm mb-4" data-community-compose-form>@csrf
<div class="card-body"><h2 class="community-compose-title">Criar post no Conectado em Sergipe</h2><div class="community-compose-toolbar"><div class="community-compose-tools"><select name="type" id="feed-type" class="form-select community-compose-type" aria-label="Tipo de post"><option value="post">Post</option><option value="notice">Aviso para todos</option><option value="poll">Enquete</option></select><div class="community-compose-option-row"><div class="community-format-tools" aria-label="Formatação do texto"><button type="button" class="community-format-button" data-community-format="bold" title="Negrito" aria-label="Negrito"><strong>B</strong></button><button type="button" class="community-format-button" data-community-format="italic" title="Itálico" aria-label="Itálico"><em>I</em></button><button type="button" class="community-format-button" data-community-format="link" title="Adicionar hiperlink" aria-label="Adicionar hiperlink"><i class="fa-solid fa-link"></i></button><button type="button" class="community-format-button" data-community-align="left" title="Alinhar à esquerda" aria-label="Alinhar à esquerda"><i class="fa-solid fa-align-left"></i></button><button type="button" class="community-format-button is-active" data-community-align="justify" title="Justificar texto" aria-label="Justificar texto"><i class="fa-solid fa-align-justify"></i></button></div><div class="community-compose-media-tools"><label class="community-compose-file" title="Adicionar imagens" data-community-image-label><i class="fa-regular fa-image"></i><span>Imagens</span><input type="file" name="images[]" class="visually-hidden" accept="image/jpeg,image/png,image/webp" multiple data-community-image-file></label><label class="community-compose-file" title="Adicionar vídeo de até 1 minuto" data-community-video-label><i class="fa-solid fa-video"></i><span>Vídeo</span><input type="file" name="video" class="visually-hidden" accept="video/mp4,video/x-m4v,video/quicktime" data-community-video-file></label></div></div></div><button class="community-compose-submit"><i class="fa-solid fa-arrow-up me-1"></i> Post</button></div><div class="community-upload-feedback" data-community-upload-feedback role="status" aria-live="polite" hidden><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span data-community-upload-feedback-text></span></div><input type="hidden" name="text_alignment" value="{{ old('text_alignment','justify') }}" data-community-alignment-input>
<textarea name="body" class="form-control community-compose-editor" rows="5" maxlength="1500" placeholder="Escreva sua mensagem...">{{ old('body') }}</textarea><div class="community-compose-options"><div data-feed-special hidden><input name="title" class="form-control mb-2" maxlength="180" placeholder="Título do aviso ou pergunta da enquete"><select name="notice_level" class="form-select mb-2" data-feed-notice hidden><option value="information">Informativo</option><option value="important">Importante</option><option value="urgent">Urgente</option></select><div data-feed-poll hidden>@for($option=0;$option<6;$option++)<input name="poll_options[]" class="form-control mb-2" maxlength="180" placeholder="Opção {{ $option+1 }} {{ $option<2?'(obrigatória)':'' }}">@endfor<input type="datetime-local" name="poll_ends_at" class="form-control mb-2" aria-label="Encerramento da enquete"></div></div><div class="community-video-url"><input type="url" name="video_url" value="{{ old('video_url') }}" class="form-control" placeholder="URL direta do vídeo (MP4, até 1 minuto)" data-community-video-url><input type="hidden" name="video_url_duration" value="{{ old('video_url_duration') }}" data-community-video-url-duration><small class="community-video-help" data-community-video-status>Use o upload ou uma URL direta do vídeo, não os dois.</small></div><select name="topic" class="form-select mb-2" aria-label="Assunto do post"><option value="updates" @selected(old('topic')==='updates')>Atualizações</option><option value="urgent" @selected(old('topic')==='urgent')>Urgente</option><option value="important" @selected(old('topic')==='important')>Importante</option><option value="informative" @selected(old('topic')==='informative')>Informativo</option><option value="security" @selected(old('topic')==='security')>Segurança</option><option value="culture" @selected(old('topic')==='culture')>Cultura</option></select><select name="city" class="form-select mb-2" aria-label="Cidade ou alcance da publicação"><option value="Sergipe" @selected(old('city',auth()->user()->city)==='Sergipe')>Sergipe — todas as cidades</option>@foreach($cityOptions as $cityName)<option value="{{ $cityName }}" @selected(old('city',auth()->user()->city)===$cityName)>{{ $cityName }}</option>@endforeach</select><select name="expires_in" class="form-select" aria-label="Tempo de exibição da publicação"><option value="never" @selected(old('expires_in','never')==='never')>Não desaparecer</option><option value="24_hours" @selected(old('expires_in')==='24_hours')>Desaparecer após 24 horas</option><option value="48_hours" @selected(old('expires_in')==='48_hours')>Desaparecer após 48 horas</option><option value="10_days" @selected(old('expires_in')==='10_days')>Desaparecer após 10 dias</option></select><small class="community-compose-help">“Sergipe” envia para todos. O prazo apenas oculta a publicação; ela não é apagada.</small></div></div></form>
@else<div class="alert alert-light border rounded-4"><i class="fa-solid fa-circle-info text-primary me-2"></i>Somente administradores e colaboradores enviam publicações e avisos. Todos os membros podem interagir e votar.</div>@endif
@else<div class="alert alert-light border rounded-4"><i class="fa-solid fa-circle-info text-primary me-2"></i>Entre na sua conta para reagir e votar nas enquetes.</div>@endauth
</aside><section class="community-posts-column" aria-label="Publicações da comunidade">
@php($topicLabels=['urgent'=>'Urgente','important'=>'Importante','informative'=>'Informativo','updates'=>'Atualizações','security'=>'Segurança','culture'=>'Cultura'])
@if($search === '' && !$sharedPost)
<section class="community-feed-preferences" aria-label="Organização das recomendações">
    <nav class="community-feed-modes" aria-label="Escolha como ver o feed">
        <a href="{{ route('feed.index', array_filter(['mode'=>'for_you','city'=>request('city')])) }}" class="community-feed-mode {{ $feedMode==='for_you'?'is-active':'' }}"><i class="fa-solid fa-wand-magic-sparkles"></i> Para você</a>
        <a href="{{ route('feed.index', array_filter(['mode'=>'recent','city'=>request('city')])) }}" class="community-feed-mode {{ $feedMode==='recent'?'is-active':'' }}"><i class="fa-regular fa-clock"></i> Recentes</a>
        <a href="{{ route('feed.index', array_filter(['mode'=>'nearby','city'=>request('city')])) }}" class="community-feed-mode {{ $feedMode==='nearby'?'is-active':'' }}"><i class="fa-solid fa-location-dot"></i> Perto de você</a>
    </nav>
    <p class="community-feed-privacy"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Recomendações usam cidade e interações realizadas neste site. Não armazenamos IP neste recurso e os registros possuem prazo de retenção. <a href="{{ route('page.privacy') }}">Saiba mais</a>.</p>
</section>
@endif
@php($feedAdIndex=0)
@forelse($posts as $post)
<article class="card community-card shadow-sm mb-4 community-topic-{{ $post->topic ?? 'updates' }}" id="publicacao-{{ $post->id }}">
<div class="card-body p-3 d-flex align-items-center gap-2 community-post-header">@if($post->user->avatar)<img src="{{ asset($post->user->avatar) }}" class="community-avatar rounded-circle" alt="">@else<div class="community-avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold">{{ $post->user->role==='admin'?'C':strtoupper(substr($post->user->name,0,1)) }}</div>@endif<div class="flex-grow-1"><div class="community-author-line">@if($post->user->username)<a href="{{ route('profile.show',$post->user->username) }}" class="community-user-link">{{ $post->user->role==='admin'?'Conectado em Sergipe':$post->user->name }}</a>@else<strong>{{ $post->user->role==='admin'?'Conectado em Sergipe':$post->user->name }}</strong>@endif @if($post->user->role==='admin')<span class="community-official-badge" title="Conta oficial" aria-label="Conta oficial"><i class="fa-solid fa-circle-check"></i></span>@elseif($post->user->role==='collaborator')<span class="badge bg-secondary">Colaborador</span>@endif</div><small class="text-muted">@if($post->city)<a href="{{ route('feed.index',['city'=>$post->city]) }}" class="community-meta-link">{{ $post->city }}</a>@else<a href="{{ route('feed.index') }}" class="community-meta-link">Sergipe</a>@endif · {{ $post->published_at?->locale('pt_BR')->diffForHumans() }}<span class="community-topic-badge">{{ $topicLabels[$post->topic ?? 'updates'] }}</span>@if($post->expires_at)<span class="community-expiration-badge"><i class="fa-regular fa-clock"></i> {{ $post->expires_at->locale('pt_BR')->diffForHumans() }}</span>@endif @if($post->is_pinned)<span class="community-topic-badge community-pinned-badge"><i class="fa-solid fa-thumbtack"></i> Fixado</span>@endif</small></div>@auth @if(auth()->id()===$post->user_id || auth()->user()->role==='admin')<div class="community-post-actions">@if(auth()->user()->role==='admin')<form action="{{ route('feed.pin',$post) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-sm {{ $post->is_pinned?'text-warning':'text-muted' }}" title="{{ $post->is_pinned?'Desafixar post':'Fixar post' }}" aria-label="{{ $post->is_pinned?'Desafixar post':'Fixar post' }}"><i class="fa-solid fa-thumbtack"></i></button></form>@endif<button type="button" class="btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#edit-post-{{ $post->id }}" title="Editar post" aria-label="Editar post"><i class="fa-solid fa-pen"></i></button><form action="{{ route('feed.destroy',$post) }}" method="POST" data-feed-delete-form>@csrf @method('DELETE')<button class="btn btn-sm text-danger" title="Excluir post" aria-label="Excluir post"><i class="fa-solid fa-trash"></i></button></form></div>@endif @endauth</div>
@if($post->type==='notice')<div class="px-3 pb-2"><span class="badge bg-{{ $post->notice_level==='urgent'?'danger':($post->notice_level==='important'?'warning text-dark':'primary') }}"><i class="fa-solid fa-bullhorn me-1"></i>{{ $post->notice_level==='urgent'?'Urgente':($post->notice_level==='important'?'Importante':'Aviso') }}</span><h2 class="h5 fw-bold mt-2 mb-0">{{ $post->title }}</h2></div>@elseif($post->type==='poll')<div class="px-3 pb-2"><span class="badge bg-info text-dark"><i class="fa-solid fa-square-poll-vertical me-1"></i>Enquete</span><h2 class="h5 fw-bold mt-2 mb-0">{{ $post->title }}</h2></div>@endif
@if($post->body)<div class="px-3 pb-3 community-body community-text-{{ $post->text_alignment ?? 'justify' }}" style="white-space:pre-wrap"><x-community-rich-text :text="$post->body" :mention-users="$mentionUsers" /></div>@endif
@if($post->video_path || $post->video_url)<div class="community-video-shell" data-community-video-shell><video class="community-video" controls controlslist="nodownload noremoteplayback" disablepictureinpicture playsinline preload="metadata" src="{{ $post->video_path ? asset($post->video_path) : $post->video_url }}" data-community-video><a href="{{ $post->video_path ? asset($post->video_path) : $post->video_url }}" target="_blank" rel="noopener">Abrir vídeo</a></video><button type="button" class="community-video-play" data-community-video-play aria-label="Reproduzir vídeo"><i class="fa-solid fa-play" aria-hidden="true"></i></button></div>@endif
@if($post->type==='poll')@php($pollTotal=$post->pollOptions->sum('votes_count'))<div class="px-3 pb-3">@auth<form action="{{ route('feed.vote',$post) }}" method="POST" data-community-poll-form data-selected-option="{{ $votedOptions[$post->id]??'' }}">@csrf @foreach($post->pollOptions as $option)@php($pollPercentage=$pollTotal?round(($option->votes_count/$pollTotal)*100):0)<label class="community-poll-option" data-community-poll-row data-option-id="{{ $option->id }}"><span class="d-flex justify-content-between"><span><input type="radio" name="option_id" value="{{ $option->id }}" required data-community-poll-option @checked(($votedOptions[$post->id]??null)===$option->id)> {{ $option->label }}</span><strong data-community-poll-percentage>{{ $pollPercentage }}%</strong></span><span class="community-poll-result mt-2"><span data-community-poll-bar style="width:{{ $pollPercentage }}%"></span></span></label>@endforeach</form>@if(auth()->user()->role==='admin')<details class="community-poll-voters" data-community-poll-voters><summary>Ver quem votou</summary>@foreach($post->pollOptions as $option)<div class="community-poll-voter-group" data-voters-option="{{ $option->id }}"><strong>{{ $option->label }}</strong><ul class="community-poll-voter-list" data-voter-list>@forelse($option->votes as $vote)<li>@if($vote->user?->username)<a href="{{ route('profile.show',$vote->user->username) }}">{{ $vote->user->role==='admin'?'Conectado em Sergipe':$vote->user->name }}</a>@else<span>{{ $vote->user?->role==='admin'?'Conectado em Sergipe':$vote->user?->name }}</span>@endif</li>@empty<li><span class="community-poll-voter-empty">Nenhum voto</span></li>@endforelse</ul></div>@endforeach</details>@endif @else @foreach($post->pollOptions as $option)<div class="community-poll-option d-flex justify-content-between"><span>{{ $option->label }}</span><strong>{{ $option->votes_count }}</strong></div>@endforeach @endauth<small class="text-muted d-block mt-2"><span data-community-poll-total>{{ $pollTotal }}</span> voto(s)@if($post->poll_ends_at) · encerra {{ $post->poll_ends_at->format('d/m/Y H:i') }}@endif</small></div>@endif
@if($post->images->isNotEmpty())<div class="{{ $post->images->count()>1?'community-grid-images':'' }}">@foreach($post->images as $image)<img src="{{ asset($image->path) }}" class="community-image" alt="Imagem da publicação de {{ $post->user->name }}" loading="lazy">@endforeach</div>@endif
<div class="card-body p-3"><div class="d-flex gap-3 border-bottom pb-3 mb-3">@auth<form action="{{ route('feed.like',$post) }}" method="POST" data-community-like-form>@csrf<button class="community-action {{ in_array($post->id,$likedPostIds)?'is-liked':'' }}" data-community-like-button aria-pressed="{{ in_array($post->id,$likedPostIds)?'true':'false' }}"><i class="{{ in_array($post->id,$likedPostIds)?'fa-solid':'fa-regular' }} fa-heart" data-community-like-icon></i> <span data-community-like-count>{{ $post->likes_count }}</span></button></form>@else<span><i class="fa-regular fa-heart"></i> {{ $post->likes_count }}</span>@endauth<button type="button" class="community-action ms-auto" data-community-share data-share-url="{{ route('feed.index',['post'=>$post->id]).'#publicacao-'.$post->id }}" data-share-title="Publicação do Conectado em Sergipe" @if($post->video_path)data-share-video-file="{{ asset($post->video_path) }}"@endif title="Compartilhar publicação" aria-label="Compartilhar publicação"><i class="fa-solid fa-share-nodes" aria-hidden="true"></i></button>@auth @if($post->user->role!=='admin')<button class="community-action text-muted" data-bs-toggle="collapse" data-bs-target="#report-{{ $post->id }}"><i class="fa-regular fa-flag"></i> Denunciar</button>@endif @endauth</div>
@foreach($post->comments as $comment)<div class="community-comment px-3 py-2 mb-2">@if($comment->user->username)<a href="{{ route('profile.show',$comment->user->username) }}" class="community-user-link">{{ $comment->user->role==='admin'?'Conectado em Sergipe':$comment->user->name }}</a>@else<strong>{{ $comment->user->role==='admin'?'Conectado em Sergipe':$comment->user->name }}</strong>@endif @if($comment->user->role==='admin')<span class="community-official-badge" title="Conta oficial" aria-label="Conta oficial"><i class="fa-solid fa-circle-check"></i></span>@endif <span class="community-body"><x-community-rich-text :text="$comment->body" :mention-users="$mentionUsers" /></span></div>@endforeach
@auth @if($post->user->role!=='admin')<form action="{{ route('feed.comment',$post) }}" method="POST" class="d-flex gap-2 mt-2">@csrf<input name="body" class="form-control rounded-pill" maxlength="500" required placeholder="Escreva um comentário"><button class="btn btn-outline-primary rounded-pill">Enviar</button></form><div class="collapse mt-3" id="report-{{ $post->id }}"><form action="{{ route('feed.report',$post) }}" method="POST" class="border rounded-3 p-3">@csrf<select name="reason" class="form-select mb-2" required><option value="spam">Spam</option><option value="inappropriate">Conteúdo impróprio</option><option value="scam">Possível golpe</option><option value="harassment">Assédio</option><option value="other">Outro</option></select><textarea name="details" class="form-control mb-2" maxlength="1000" placeholder="Detalhes (opcional)"></textarea><button class="btn btn-danger btn-sm rounded-pill">Enviar denúncia</button></form></div>@endif @endauth</div></article>
@auth @if(auth()->id()===$post->user_id || auth()->user()->role==='admin')
<div class="modal fade community-edit-modal" id="edit-post-{{ $post->id }}" tabindex="-1" aria-labelledby="edit-post-title-{{ $post->id }}" aria-hidden="true" data-feed-edit-modal="{{ $post->id }}">
<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="{{ route('feed.update',$post) }}" method="POST">@csrf @method('PATCH')
<div class="modal-header"><h2 class="modal-title fs-5" id="edit-post-title-{{ $post->id }}">Editar post</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
<div class="modal-body">
@if(in_array($post->type,['notice','poll'],true))<label class="form-label fw-bold" for="edit-title-{{ $post->id }}">{{ $post->type==='poll'?'Pergunta da enquete':'Título' }}</label><input id="edit-title-{{ $post->id }}" name="title" class="form-control mb-3" maxlength="180" value="{{ $post->title }}" required>@endif
<label class="form-label fw-bold" for="edit-body-{{ $post->id }}">Texto</label><textarea id="edit-body-{{ $post->id }}" name="body" class="form-control mb-3" maxlength="1500" @required($post->type==='notice')>{{ $post->body }}</textarea>
@if($post->type==='notice')<label class="form-label fw-bold" for="edit-level-{{ $post->id }}">Tipo de aviso</label><select id="edit-level-{{ $post->id }}" name="notice_level" class="form-select mb-3" required><option value="information" @selected($post->notice_level==='information')>Informativo</option><option value="important" @selected($post->notice_level==='important')>Importante</option><option value="urgent" @selected($post->notice_level==='urgent')>Urgente</option></select>@endif
<label class="form-label fw-bold" for="edit-topic-{{ $post->id }}">Assunto</label><select id="edit-topic-{{ $post->id }}" name="topic" class="form-select mb-3" required>@foreach($topicLabels as $topicValue=>$topicLabel)<option value="{{ $topicValue }}" @selected(($post->topic ?? 'updates')===$topicValue)>{{ $topicLabel }}</option>@endforeach</select><label class="form-label fw-bold" for="edit-alignment-{{ $post->id }}">Alinhamento do texto</label><select id="edit-alignment-{{ $post->id }}" name="text_alignment" class="form-select mb-3"><option value="left" @selected(($post->text_alignment ?? 'justify')==='left')>Alinhar à esquerda</option><option value="justify" @selected(($post->text_alignment ?? 'justify')==='justify')>Justificar</option></select>
<label class="form-label fw-bold" for="edit-city-{{ $post->id }}">Cidade ou alcance</label><select id="edit-city-{{ $post->id }}" name="city" class="form-select mb-3"><option value="Sergipe" @selected($post->city==='Sergipe')>Sergipe — todas as cidades</option>@foreach($cityOptions as $cityName)<option value="{{ $cityName }}" @selected($post->city===$cityName)>{{ $cityName }}</option>@endforeach</select><label class="form-label fw-bold" for="edit-expiration-{{ $post->id }}">Tempo de exibição</label><select id="edit-expiration-{{ $post->id }}" name="expires_in" class="form-select"><option value="keep">Manter prazo atual</option><option value="never">Não desaparecer</option><option value="24_hours">24 horas a partir de agora</option><option value="48_hours">48 horas a partir de agora</option><option value="10_days">10 dias a partir de agora</option></select>@if($post->type==='poll')<small class="text-muted d-block mt-2">As opções e os votos da enquete serão preservados.</small>@endif
</div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary rounded-pill">Salvar alterações</button></div></form></div></div></div>
@endif @endauth
@if($loop->iteration % 2 === 0 && isset($recommendedAds[$feedAdIndex]))
    @include('feed.partials.recommended-ad', ['feedAd'=>$recommendedAds[$feedAdIndex], 'feedMode'=>$feedMode])
    @php($feedAdIndex++)
@endif
@empty<div class="text-center bg-white rounded-4 p-5 shadow-sm"><i class="fa-regular {{ $search !== '' ? 'fa-magnifying-glass' : 'fa-images' }} fs-1 text-muted"></i><h2 class="h5 mt-3">{{ $search !== '' ? 'Nenhuma publicação encontrada' : 'Ainda não há publicações' }}</h2>@if($search !== '')<p class="text-muted mb-0">Tente pesquisar por outro termo.</p>@endif</div>@endforelse
@while(isset($recommendedAds[$feedAdIndex]))
    @include('feed.partials.recommended-ad', ['feedAd'=>$recommendedAds[$feedAdIndex], 'feedMode'=>$feedMode])
    @php($feedAdIndex++)
@endwhile
<div class="d-flex justify-content-between my-4">@if($posts->previousPageUrl())<a class="btn btn-outline-primary rounded-pill" href="{{ $posts->previousPageUrl() }}">Anteriores</a>@else<span></span>@endif @if($posts->nextPageUrl())<a class="btn btn-primary rounded-pill" href="{{ $posts->nextPageUrl() }}">Carregar mais</a>@endif</div>
</section></div></div></div>
<dialog class="community-link-modal" id="community-link-modal" aria-labelledby="community-link-modal-title"><div class="community-link-modal-shell"><header class="community-link-modal-header"><h2 class="community-link-modal-title" id="community-link-modal-title">Página do Conectado em Sergipe</h2><a href="{{ route('home') }}" class="community-link-modal-external" data-community-modal-open-page aria-label="Abrir página normalmente" title="Abrir página normalmente"><i class="fa-solid fa-arrow-up-right-from-square"></i></a><button type="button" class="community-link-modal-close" data-community-modal-close aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button></header><iframe class="community-link-modal-frame" data-community-modal-frame title="Conteúdo do Conectado em Sergipe"></iframe></div></dialog>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const modal=document.getElementById('community-link-modal');
    const modalFrame=modal?.querySelector('[data-community-modal-frame]');
    const modalTitle=modal?.querySelector('.community-link-modal-title');
    const modalOpenPage=modal?.querySelector('[data-community-modal-open-page]');
    document.addEventListener('click',(event)=>{
        const link=event.target.closest('[data-community-modal-link]');
        if(!link||!modal||!modalFrame||!modalTitle||!modalOpenPage)return;
        event.preventDefault();
        modalFrame.src=link.href;
        modalTitle.textContent=link.textContent.trim()||'Página do Conectado em Sergipe';
        modalOpenPage.href=link.href;
        if(typeof modal.showModal==='function')modal.showModal();
        else modal.setAttribute('open','');
    });
    modal?.querySelector('[data-community-modal-close]')?.addEventListener('click',()=>{
        if(typeof modal.close==='function')modal.close();
        else modal.removeAttribute('open');
    });
    modal?.addEventListener('click',(event)=>{
        if(event.target!==modal)return;
        if(typeof modal.close==='function')modal.close();
        else modal.removeAttribute('open');
    });
    modal?.addEventListener('close',()=>{if(modalFrame)modalFrame.src='about:blank';});

    const feedAdCards=Array.from(document.querySelectorAll('[data-feed-ad-card]'));
    const feedAdCsrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
    const passiveTrackingAllowed=!(navigator.globalPrivacyControl===true||navigator.doNotTrack==='1');
    const sendFeedAdEvent=async(card,eventType)=>{
        if(!card?.dataset.feedAdEventUrl)return null;
        const response=await fetch(card.dataset.feedAdEventUrl,{
            method:'POST',
            credentials:'same-origin',
            keepalive:eventType==='click',
            headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':feedAdCsrf},
            body:JSON.stringify({event_type:eventType,mode:card.dataset.feedAdMode||'for_you',city:card.dataset.feedAdCity||null}),
        });
        if(!response.ok)throw new Error('Não foi possível registrar esta preferência.');
        return response.json();
    };
    if(passiveTrackingAllowed&&'IntersectionObserver'in window){
        const feedAdObserver=new IntersectionObserver((entries,observer)=>{
            entries.forEach(entry=>{
                if(!entry.isIntersecting||entry.intersectionRatio<.45)return;
                observer.unobserve(entry.target);
                sendFeedAdEvent(entry.target,'impression').catch(()=>{});
            });
        },{threshold:[.45]});
        feedAdCards.forEach(card=>feedAdObserver.observe(card));
    }
    feedAdCards.forEach(card=>{
        card.querySelector('[data-feed-ad-link]')?.addEventListener('click',()=>{
            if(passiveTrackingAllowed)sendFeedAdEvent(card,'click').catch(()=>{});
        });
        card.querySelector('[data-feed-ad-dismiss]')?.addEventListener('click',async()=>{
            card.classList.add('is-removing');
            try{
                await sendFeedAdEvent(card,'dismiss');
                card.hidden=true;
                const status=document.querySelector('[data-community-live-status]');
                if(status){status.textContent='Entendido. Você verá menos anúncios como este.';status.classList.remove('is-error');status.hidden=false;window.setTimeout(()=>{status.hidden=true;},3500);}
            }catch(error){
                card.classList.remove('is-removing');
            }
        });
    });

    const type=document.getElementById('feed-type');
    if(!type)return;
    const special=document.querySelector('[data-feed-special]');
    const notice=document.querySelector('[data-feed-notice]');
    const poll=document.querySelector('[data-feed-poll]');
    const editor=document.querySelector('.community-compose-editor');
    const composeForm=document.querySelector('[data-community-compose-form]');
    const imageFile=document.querySelector('[data-community-image-file]');
    const imageLabel=document.querySelector('[data-community-image-label]');
    const videoFile=document.querySelector('[data-community-video-file]');
    const videoLabel=document.querySelector('[data-community-video-label]');
    const videoUrl=document.querySelector('[data-community-video-url]');
    const videoUrlDuration=document.querySelector('[data-community-video-url-duration]');
    const videoStatus=document.querySelector('[data-community-video-status]');
    const uploadFeedback=document.querySelector('[data-community-upload-feedback]');
    const uploadFeedbackText=document.querySelector('[data-community-upload-feedback-text]');
    let validatedVideoFile=null;
    let validatedVideoUrl='';
    let bypassVideoValidation=false;
    const setVideoStatus=(message,isError=false)=>{
        if(!videoStatus)return;
        videoStatus.textContent=message;
        videoStatus.classList.toggle('is-error',isError);
    };
    const setUploadFeedback=(message='')=>{
        if(!uploadFeedback||!uploadFeedbackText)return;
        uploadFeedbackText.textContent=message;
        uploadFeedback.hidden=message==='';
    };
    const readVideoDuration=(source)=>new Promise((resolve,reject)=>{
        const preview=document.createElement('video');
        const timer=window.setTimeout(()=>reject(new Error('Não foi possível carregar os dados do vídeo.')),15000);
        preview.preload='metadata';
        preview.onloadedmetadata=()=>{
            window.clearTimeout(timer);
            Number.isFinite(preview.duration)?resolve(preview.duration):reject(new Error('Duração inválida.'));
        };
        preview.onerror=()=>{
            window.clearTimeout(timer);
            reject(new Error('Use um arquivo ou uma URL direta de vídeo válida.'));
        };
        preview.src=source;
    });
    const validateVideoFile=async()=>{
        const file=videoFile?.files?.[0];
        if(!file){validatedVideoFile=null;return true;}
        const source=URL.createObjectURL(file);
        try{
            const duration=await readVideoDuration(source);
            if(duration>60)throw new Error('O vídeo deve ter no máximo 1 minuto.');
            validatedVideoFile=file;
            setVideoStatus(`Vídeo selecionado: ${Math.ceil(duration)} segundos.`);
            return true;
        }catch(error){
            validatedVideoFile=null;
            videoFile.value='';
            setVideoStatus(error.message,true);
            return false;
        }finally{URL.revokeObjectURL(source);}
    };
    const validateVideoUrl=async()=>{
        const url=videoUrl?.value.trim()||'';
        if(!url){validatedVideoUrl='';if(videoUrlDuration)videoUrlDuration.value='';return true;}
        try{
            const duration=await readVideoDuration(url);
            if(duration>60)throw new Error('O vídeo deve ter no máximo 1 minuto.');
            validatedVideoUrl=url;
            if(videoUrlDuration)videoUrlDuration.value=String(duration);
            setVideoStatus(`URL validada: ${Math.ceil(duration)} segundos.`);
            return true;
        }catch(error){
            validatedVideoUrl='';
            if(videoUrlDuration)videoUrlDuration.value='';
            setVideoStatus(error.message,true);
            return false;
        }
    };
    imageFile?.addEventListener('change',()=>{
        const files=Array.from(imageFile.files||[]);
        imageLabel?.classList.toggle('has-file',files.length>0);
        if(files.length===1)setUploadFeedback(`Imagem pronta: ${files[0].name}`);
        else if(files.length>1)setUploadFeedback(`${files.length} imagens prontas para publicar.`);
        else setUploadFeedback('');
    });
    videoFile?.addEventListener('change',async()=>{
        const file=videoFile.files?.[0];
        videoLabel?.classList.remove('has-file');
        if(!file){setUploadFeedback('');return;}
        setUploadFeedback(`Verificando vídeo: ${file.name}`);
        const isValid=await validateVideoFile();
        videoLabel?.classList.toggle('has-file',isValid&&Boolean(videoFile.files?.[0]));
        setUploadFeedback(isValid&&videoFile.files?.[0] ? `Vídeo pronto: ${file.name}` : '');
    });
    videoUrl?.addEventListener('input',()=>{validatedVideoUrl='';if(videoUrlDuration)videoUrlDuration.value='';});
    videoUrl?.addEventListener('blur',validateVideoUrl);
    composeForm?.addEventListener('submit',async(event)=>{
        if(bypassVideoValidation){bypassVideoValidation=false;return;}
        const file=videoFile?.files?.[0]||null;
        const url=videoUrl?.value.trim()||'';
        if(file&&url){event.preventDefault();setVideoStatus('Escolha apenas upload ou URL do vídeo.',true);return;}
        if(file&&validatedVideoFile!==file){
            event.preventDefault();
            if(await validateVideoFile()){bypassVideoValidation=true;composeForm.requestSubmit();}
            return;
        }
        if(url&&validatedVideoUrl!==url){
            event.preventDefault();
            if(await validateVideoUrl()){bypassVideoValidation=true;composeForm.requestSubmit();}
        }
    });
    const sync=()=>{special.hidden=type.value==='post';notice.hidden=type.value!=='notice';poll.hidden=type.value!=='poll';};
    const replaceSelection=(content,selectionStart,selectionEnd)=>{
        editor.setRangeText(content,editor.selectionStart,editor.selectionEnd,'end');
        editor.focus();
        editor.setSelectionRange(editor.selectionStart-content.length+selectionStart,editor.selectionStart-content.length+selectionEnd);
        editor.dispatchEvent(new Event('input',{bubbles:true}));
    };
    document.querySelectorAll('[data-community-format]').forEach((button)=>button.addEventListener('click',()=>{
        const selected=editor.value.slice(editor.selectionStart,editor.selectionEnd);
        const format=button.dataset.communityFormat;
        if(format==='bold'){
            const value=selected||'texto em negrito';
            replaceSelection(`**${value}**`,2,2+value.length);
        }else if(format==='italic'){
            const value=selected||'texto em itálico';
            replaceSelection(`*${value}*`,1,1+value.length);
        }else{
            if(!selected){window.alert('Selecione o texto que será transformado em link.');return;}
            const url=window.prompt('Cole o endereço do link. Para páginas do site, você também pode usar /lojas ou /servicos:','https://');
            if(!url)return;
            try{
                const parsed=new URL(url,window.location.origin);
                if(!['http:','https:'].includes(parsed.protocol))throw new Error('invalid');
                const destination=parsed.origin===window.location.origin
                    ? `${parsed.pathname}${parsed.search}${parsed.hash}`
                    : parsed.href;
                replaceSelection(`[${selected}](${destination})`,1,1+selected.length);
            }catch(error){window.alert('Informe um endereço válido, como https://exemplo.com ou /lojas.');}
        }
    }));
    const alignmentInput=document.querySelector('[data-community-alignment-input]');
    const alignmentButtons=document.querySelectorAll('[data-community-align]');
    const setAlignment=(alignment)=>{
        if(!alignmentInput)return;
        alignmentInput.value=alignment;
        editor.style.textAlign=alignment;
        alignmentButtons.forEach((button)=>button.classList.toggle('is-active',button.dataset.communityAlign===alignment));
    };
    alignmentButtons.forEach((button)=>button.addEventListener('click',()=>setAlignment(button.dataset.communityAlign)));
    setAlignment(alignmentInput?.value||'justify');
    type.addEventListener('change',sync);
    sync();

    const liveStatus=document.querySelector('[data-community-live-status]');
    let statusTimer;
    const showStatus=(message,isError=false)=>{
        if(!liveStatus)return;
        window.clearTimeout(statusTimer);
        liveStatus.textContent=message;
        liveStatus.classList.toggle('is-error',isError);
        liveStatus.hidden=false;
        statusTimer=window.setTimeout(()=>{liveStatus.hidden=true;},4000);
    };

    document.querySelectorAll('[data-feed-delete-form]').forEach((form)=>form.addEventListener('submit',async(event)=>{
        event.preventDefault();
        if(!window.confirm('Excluir este post? Esta ação não pode ser desfeita.'))return;

        const card=form.closest('article');
        const button=form.querySelector('button[type="submit"],button:not([type])');
        const postId=card?.id.replace('publicacao-','');
        button?.setAttribute('disabled','disabled');
        card?.classList.add('is-removing');

        try{
            const response=await fetch(form.action,{
                method:'POST',
                body:new FormData(form),
                headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            });
            const data=await response.json().catch(()=>({}));
            if(!response.ok)throw new Error(data.message||'Não foi possível excluir o post.');

            document.querySelector(`[data-feed-edit-modal="${postId}"]`)?.remove();
            card?.remove();
            showStatus(data.message||'Publicação excluída.');
        }catch(error){
            button?.removeAttribute('disabled');
            card?.classList.remove('is-removing');
            showStatus(error.message||'Não foi possível excluir o post.',true);
        }
    }));
});

document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('[data-community-like-form]').forEach((form)=>form.addEventListener('submit',async(event)=>{
        event.preventDefault();
        if(form.dataset.submitting==='true')return;
        const button=form.querySelector('[data-community-like-button]');
        const icon=form.querySelector('[data-community-like-icon]');
        const count=form.querySelector('[data-community-like-count]');
        form.dataset.submitting='true';
        button?.setAttribute('disabled','disabled');

        try{
            const response=await fetch(form.action,{
                method:'POST',
                credentials:'same-origin',
                headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                body:new FormData(form),
            });
            if(!response.ok)throw new Error('like_failed');
            const data=await response.json();
            button?.classList.toggle('is-liked',Boolean(data.liked));
            button?.setAttribute('aria-pressed',data.liked?'true':'false');
            icon?.classList.toggle('fa-solid',Boolean(data.liked));
            icon?.classList.toggle('fa-regular',!data.liked);
            if(count)count.textContent=String(data.likes_count);
        }catch(error){
            // Mantém o estado atual se a conexão falhar, sem recarregar a página.
        }finally{
            form.dataset.submitting='false';
            button?.removeAttribute('disabled');
        }
    }));
    document.querySelectorAll('[data-community-video-shell]').forEach((shell)=>{
        const video=shell.querySelector('[data-community-video]');
        const playButton=shell.querySelector('[data-community-video-play]');
        if(!video||!playButton)return;
        const showPlay=()=>{playButton.hidden=false;};
        const hidePlay=()=>{playButton.hidden=true;};
        playButton.addEventListener('click',()=>video.play().catch(showPlay));
        video.addEventListener('play',hidePlay);
        video.addEventListener('playing',hidePlay);
        video.addEventListener('pause',showPlay);
        video.addEventListener('ended',showPlay);
    });
    document.querySelectorAll('[data-community-poll-form]').forEach((form)=>{
        form.querySelectorAll('[data-community-poll-option]').forEach((option)=>option.addEventListener('change',async()=>{
            if(form.dataset.submitting==='true')return;
            const previousOption=form.dataset.selectedOption||'';
            form.dataset.submitting='true';
            form.style.pointerEvents='none';

            try{
                const response=await fetch(form.action,{
                    method:'POST',
                    credentials:'same-origin',
                    headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                    body:new FormData(form),
                });
                const data=await response.json().catch(()=>({}));
                if(!response.ok)throw new Error(data.message||'Não foi possível registrar seu voto.');

                form.dataset.selectedOption=String(data.selected_option_id);
                form.querySelectorAll('[data-community-poll-row]').forEach((row)=>{
                    const result=data.options?.find((item)=>String(item.id)===row.dataset.optionId);
                    if(!result)return;
                    const percentage=row.querySelector('[data-community-poll-percentage]');
                    const bar=row.querySelector('[data-community-poll-bar]');
                    if(percentage)percentage.textContent=`${result.percentage}%`;
                    if(bar)bar.style.width=`${result.percentage}%`;
                    const voterList=form.parentElement?.querySelector(`[data-voters-option="${result.id}"] [data-voter-list]`);
                    if(voterList&&Array.isArray(result.voters)){
                        voterList.replaceChildren();
                        if(result.voters.length===0){
                            const item=document.createElement('li');
                            const empty=document.createElement('span');
                            empty.className='community-poll-voter-empty';
                            empty.textContent='Nenhum voto';
                            item.appendChild(empty);
                            voterList.appendChild(item);
                        }else{
                            result.voters.forEach((voter)=>{
                                const item=document.createElement('li');
                                const identity=document.createElement(voter.url?'a':'span');
                                identity.textContent=voter.name||'Usuário';
                                if(voter.url)identity.href=voter.url;
                                item.appendChild(identity);
                                voterList.appendChild(item);
                            });
                        }
                    }
                });
                const total=form.parentElement?.querySelector('[data-community-poll-total]');
                if(total)total.textContent=String(data.total);
            }catch(error){
                form.querySelectorAll('[data-community-poll-option]').forEach((radio)=>{
                    radio.checked=previousOption!==''&&radio.value===previousOption;
                });
                showStatus(error.message||'Não foi possível registrar seu voto.',true);
            }finally{
                form.dataset.submitting='false';
                form.style.pointerEvents='';
            }
        }));
    });
    document.querySelectorAll('[data-community-share]').forEach((button)=>button.addEventListener('click',async()=>{
        const shareData={title:button.dataset.shareTitle,url:button.dataset.shareUrl};
        const original=button.innerHTML;

        try{
            if(navigator.share){
                if(button.dataset.shareVideoFile&&navigator.canShare&&window.File){
                    try{
                        button.disabled=true;
                        button.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Preparando vídeo';
                        const response=await fetch(button.dataset.shareVideoFile,{credentials:'same-origin'});
                        if(response.ok){
                            const blob=await response.blob();
                            const extension=(new URL(button.dataset.shareVideoFile,window.location.href).pathname.split('.').pop()||'mp4').toLowerCase();
                            const file=new File([blob],`conectado-em-sergipe.${extension}`,{type:blob.type||'video/mp4'});
                            const videoShare=Object.assign({},shareData,{files:[file]});
                            if(navigator.canShare(videoShare)){
                                await navigator.share(videoShare);
                                return;
                            }
                        }
                    }catch(error){
                        if(error.name==='AbortError')return;
                    }finally{
                        button.disabled=false;
                        button.innerHTML=original;
                    }
                }
                await navigator.share(shareData);
                return;
            }

            if(navigator.clipboard?.writeText){
                await navigator.clipboard.writeText(shareData.url);
                button.innerHTML='<i class="fa-solid fa-check"></i> Link copiado';
                window.setTimeout(()=>{button.innerHTML=original;},2200);
                return;
            }

            window.prompt('Copie o link desta publicação:',shareData.url);
        }catch(error){
            if(error.name!=='AbortError')window.prompt('Copie o link desta publicação:',shareData.url);
        }
    }));
});
</script>
@endpush
