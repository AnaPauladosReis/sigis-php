# Migrations

Mudanças de schema em um banco que já existe (produção, ou qualquer ambiente
que já rodou `database/schema.sql` antes) entram aqui como um arquivo novo,
nunca editando `database/schema.sql` diretamente nesse momento.

Convenção:

- Nome: `NNN_descricao.sql`, com `NNN` sequencial de 3 dígitos
  (`001_login_tentativas.sql`, `002_...`, ...).
- Cada migration é aplicada manualmente, uma vez, em ordem crescente:
  ```bash
  mysql -u root -p sigis < database/migrations/001_login_tentativas.sql
  ```
- Uma migration já aplicada **nunca é editada depois** — qualquer correção
  vira uma migration nova.
- Toda mudança de schema é feita em duas partes: a migration aqui (para quem
  já tem o banco) **e** a mesma mudança refletida em `database/schema.sql`
  (para quem está instalando do zero), conforme a seção 0 do
  `docs/PLANO-DE-TRABALHO.md`.
