# Publicação em VPS

Este guia prepara o Conectado em Sergipe para uma VPS Linux com Nginx, PHP-FPM e MySQL. Não copie credenciais para o repositório.

## 1. Requisitos

- PHP 8.3 ou superior com as extensões exigidas pelo Laravel, banco, imagens e arquivos;
- Composer;
- Node.js e npm para gerar os assets;
- MySQL ou PostgreSQL;
- Nginx apontando o document root para a pasta `public`;
- processo permanente para a fila;
- cron para o agendador do Laravel;
- certificado HTTPS.

## 2. Preparar o ambiente

Copie `.env.production.example` para `.env` somente no servidor e preencha:

- domínio definitivo em `APP_URL`;
- banco de dados;
- SMTP;
- IPs do proxy em `TRUSTED_PROXIES`;
- remetente oficial;
- demais credenciais do ambiente.

Gere uma chave nova somente no primeiro deploy:

```bash
php artisan key:generate
```

Não gere outra chave em atualizações futuras, pois isso invalida dados criptografados.

## 3. Instalação

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan app:production-check
```

O usuário do PHP-FPM precisa escrever em `storage`, `bootstrap/cache` e `public/uploads`.

## 4. Fila

Mantenha um worker sob Supervisor ou systemd:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Depois de cada publicação:

```bash
php artisan queue:restart
```

## 5. Agendador

Adicione ao cron do usuário da aplicação:

```cron
* * * * * cd /caminho/da/aplicacao && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Nginx e HTTPS

- document root: `/caminho/da/aplicacao/public`;
- encaminhar as requisições PHP para o PHP-FPM;
- bloquear acesso a `.env`, `storage` e arquivos ocultos;
- redirecionar HTTP para HTTPS;
- configurar o limite de upload de acordo com as imagens aceitas;
- conferir se `/up` responde `200`.

Se houver Cloudflare, balanceador ou proxy adicional, informe apenas os IPs reais em `TRUSTED_PROXIES`.

## 7. Atualizações

Antes de atualizar, faça backup do banco e dos uploads. Depois de enviar os arquivos:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart
php artisan app:production-check
```

Não execute `migrate:fresh`, não apague o banco e não substitua o `.env`.

## 8. Verificação

- `/up` retorna `200`;
- cadastro e login funcionam por HTTPS;
- upload de imagens continua acessível;
- formulário de contato chega ao e-mail oficial;
- cliente e lojista recebem e-mails do pedido;
- worker processa a tabela `jobs`;
- a limpeza diária de filas antigas aparece em `php artisan schedule:list`;
- logs não exibem erros recorrentes;
- `APP_DEBUG` permanece desativado.
