@extends('layouts.app')

@section('title', 'Editar Perfil - Conectado em Sergipe')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-2">Seus Dados</span>
                        <h2 class="fw-bold text-dark">Editar Perfil</h2>
                        <p class="text-muted small">Atualize suas informações pessoais e de contato.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success rounded-3 mb-4">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('user.profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nome Completo *</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Nome de usuário *</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $user->username ?? '') }}" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9._]+" autocomplete="username" placeholder="jairo">
                            </div>
                            <small class="text-muted">Será usado para entrar na sua conta.</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">E-mail</label>
                            <input type="email" class="form-control form-control-lg rounded-3 bg-light" id="email" value="{{ $user->email ?? '' }}" readonly>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label fw-semibold">Telefone</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="whatsapp" class="form-label fw-semibold">WhatsApp</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp ?? '') }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="city" class="form-label fw-semibold">Cidade em SE</label>
                            <select class="form-select form-select-lg rounded-3" id="city" name="city">
                                <option value="" disabled>Selecione</option>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}" {{ ($user->city ?? '') === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-photo-preview {
        width: 104px;
        height: 104px;
        overflow: hidden;
        border: 3px solid var(--card);
        border-radius: 50%;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .14);
        background: #eaf2ff;
    }

    .profile-photo-preview img,
    .profile-photo-preview div {
        width: 100%;
        height: 100%;
    }

    .profile-photo-preview img {
        object-fit: cover;
    }

    .profile-photo-preview div {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d6efd;
        font-size: 2rem;
        font-weight: 800;
    }
</style>

@push('scripts')
<script>
    document.getElementById('avatar')?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        if (!file) return;

        const preview = document.getElementById('profile-photo-preview');
        const placeholder = document.getElementById('profile-photo-placeholder');
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
        placeholder?.classList.add('d-none');
    });
</script>
@endpush
@endsection
