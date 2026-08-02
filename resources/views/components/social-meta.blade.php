@php
    $socialType = $socialType ?? 'website';
    $socialSiteName = \App\Models\Setting::get('site_name', 'Conectado em Sergipe');
@endphp
<meta name="description" content="{{ $socialDescription }}">
<link rel="canonical" href="{{ $socialUrl }}">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="{{ $socialSiteName }}">
<meta property="og:type" content="{{ $socialType }}">
<meta property="og:title" content="{{ $socialTitle }}">
<meta property="og:description" content="{{ $socialDescription }}">
<meta property="og:url" content="{{ $socialUrl }}">
<meta property="og:image" content="{{ $socialImage }}">
<meta property="og:image:alt" content="{{ $socialTitle }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $socialTitle }}">
<meta name="twitter:description" content="{{ $socialDescription }}">
<meta name="twitter:image" content="{{ $socialImage }}">
