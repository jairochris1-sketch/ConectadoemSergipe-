@php($searchId = $searchId ?? 'community')
<form action="{{ route('feed.index') }}" method="GET" class="community-search" role="search">
    <label for="{{ $searchId }}-search" class="community-search-label">Pesquisar</label>
    @if(request()->filled('city'))<input type="hidden" name="city" value="{{ request('city') }}">@endif
    <div class="community-search-control">
        <input type="search" id="{{ $searchId }}-search" name="q" value="{{ $search }}" class="community-search-input" maxlength="100" aria-label="Pesquisar na comunidade">
        <button type="submit" class="community-search-button" aria-label="Pesquisar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
    </div>
    @if($search !== '')
        <div class="mt-1"><a href="{{ route('feed.index', request()->filled('city') ? ['city'=>request('city')] : []) }}" class="community-search-clear">Limpar pesquisa</a></div>
    @endif
</form>
