@props(['ad', 'favoriteFolderAssignments' => collect()])

@php
    $favoriteFolderId = $favoriteFolderAssignments->get($ad->id);
    $isFavorite = $favoriteFolderAssignments->has($ad->id);
@endphp

<button
    type="button"
    class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm {{ $isFavorite ? 'is-favorite' : '' }}"
    aria-label="{{ $isFavorite ? 'Organizar favorito' : 'Favoritar' }}"
    aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"
    title="{{ $isFavorite ? 'Organizar favorito' : 'Salvar anúncio' }}"
    data-favorite-button
    data-ad-id="{{ $ad->id }}"
    data-ad-title="{{ $ad->title }}"
    data-favorite-folder-id="{{ $favoriteFolderId }}"
    data-store-endpoint="{{ route('ads.favorite.store', $ad) }}"
    data-destroy-endpoint="{{ route('ads.favorite.destroy', $ad) }}"
    data-login-url="{{ route('login') }}"
>
    <i class="{{ $isFavorite ? 'fa-solid' : 'fa-regular' }} fa-bookmark text-primary" aria-hidden="true"></i>
</button>
