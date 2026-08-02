<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Nova mensagem de contato</title>
</head>
<body style="margin:0;padding:24px;background:#f4f7fb;color:#172033;font-family:Arial,sans-serif">
    <div style="max-width:640px;margin:0 auto;padding:28px;border:1px solid #dbe4ef;border-radius:16px;background:#fff">
        <h1 style="margin-top:0;font-size:22px">Nova mensagem pelo Conectado em Sergipe</h1>
        <p><strong>Nome:</strong> {{ $contact['name'] }}</p>
        <p><strong>E-mail:</strong> {{ $contact['email'] }}</p>
        <p><strong>Assunto:</strong> {{ $contact['subject'] }}</p>
        <div style="margin-top:22px;padding:18px;border-radius:12px;background:#f8fafc;white-space:pre-wrap">{{ $contact['message'] }}</div>
        <p style="margin-bottom:0;color:#64748b;font-size:13px">Responda diretamente a este e-mail para falar com o remetente.</p>
    </div>
</body>
</html>
