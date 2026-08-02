<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito - Painel Administrativo</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @include('components.theme-head')

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-card {
            background: var(--card);
            color: var(--foreground);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 420px;
            width: 100%;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/theme-toggle.css') }}?v=2.0">
</head>
<body>

<div class="container p-3">
    <div class="admin-card mx-auto p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-3">
                <i class="fa-solid fa-shield-halved text-primary fs-1"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">Painel Admin</h3>
            <p class="text-muted small">Acesso exclusivo para administradores</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-4">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">E-mail Administrativo</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control form-control-lg border-start-0 rounded-end-3" id="email" name="email" value="{{ old('email', 'admin@conectadoemsergipe.com.br') }}" placeholder="admin@dominio.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" class="form-control form-control-lg border-start-0 rounded-end-3" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-sm">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Entrar no Painel
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('home') }}" class="text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Marketplace</a>
            </div>
        </form>
    </div>
</div>

@include('components.theme-toggle')
<script src="{{ asset('js/main.js') }}?v=1.0"></script>
</body>
</html>
