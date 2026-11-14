#!/bin/sh
# Aplica database/seed_dados_teste.sql somente quando SEED_TESTE=1.
# Montado como 03_seed_dados_teste.sh em /docker-entrypoint-initdb.d/; o SQL
# em si fica fora dessa pasta (/seed-dados-teste.sql) para nao ser aplicado
# duas vezes pelo loop do entrypoint oficial do MySQL.
set -e

if [ "$SEED_TESTE" = "1" ]; then
    echo "SEED_TESTE=1: aplicando seed de dados de teste"
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" < /seed-dados-teste.sql
fi
