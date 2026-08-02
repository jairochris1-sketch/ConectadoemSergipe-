#!/bin/bash
# Script de Atualização Interna da Aplicação - Conectado em Sergipe

echo "=========================================================="
echo " Iniciando atualização do sistema via script interno..."
echo "=========================================================="

# Ir para o diretório da aplicação
cd "$(dirname "$0")"

# 1. Puxar alterações do Git
echo "-> Sincronizando repositório Git..."
git fetch origin
git pull origin main

# 2. Executar migrações
echo "-> Executando migrações do banco de dados..."
php artisan migrate --force

# 3. Limpar e otimizar cache do Laravel
echo "-> Otimizando caches do Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=========================================================="
echo " Atualização realizada com sucesso!"
echo "=========================================================="
