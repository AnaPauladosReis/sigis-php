# SIGIS

**SIGIS** (Sistema Integrado de Gestão de Impacto Social, Produção Social e Doações) é uma aplicação web para organizações sociais gerenciarem todo o ciclo de reaproveitamento de materiais: da entrada de doações até a produção e o direcionamento final para instituições beneficiadas.

## Funcionalidades

- **Projetos Sociais** — cadastro dos projetos mantidos pela organização.
- **Grupos de Impacto & Itens** — categorias de materiais (têxteis, plásticos, móveis, eletrônicos etc.) e seus itens, com peso unitário e multiplicador de impacto.
- **Instituições Beneficiadas** — cadastro das instituições que recebem doações.
- **Entradas de Materiais** — registro de materiais recebidos.
- **Estoque** — controle de quantidades disponíveis por item.
- **Produção Social** — registro de itens produzidos a partir do material recebido.
- **Direcionamento de Doações** — vínculo entre produção e instituições beneficiadas.
- **Termo de Doação** — geração do documento formal de doação.
- **Evidências & Relatórios** — upload de fotos/comprovantes e exportação de relatórios.
- **Dashboard** administrativo com visão geral dos indicadores.

Stack: PHP puro (sem framework) + MySQL, front-end com Bootstrap 5.

## Rodando com Docker (recomendado)

Pré-requisitos: [Docker](https://docs.docker.com/get-docker/) e o plugin Docker Compose.

1. Copie o arquivo de variáveis de ambiente:

   ```bash
   cp .env.example .env
   ```

2. Suba os containers:

   ```bash
   docker compose up -d --build
   ```

   Isso cria dois containers:
   - `app` — Apache + PHP 8.2 servindo a aplicação em `http://localhost:8080`
   - `db` — MySQL 8.0, já inicializado com o schema em `database/schema.sql` (tabelas + dados de exemplo)

3. Acesse **http://localhost:8080** e faça login com o usuário de demonstração:

   | Email | Senha |
   |---|---|
   | `admin@gersc.org.br` | `gersc123` |

4. Para parar os containers:

   ```bash
   docker compose down
   ```

   Para parar e apagar também os dados do banco (volume `db_data`):

   ```bash
   docker compose down -v
   ```

### Variáveis de ambiente (`.env`)

| Variável | Descrição | Padrão |
|---|---|---|
| `DB_NAME` | Nome do banco de dados | `sigis` |
| `DB_USER` | Usuário do MySQL usado pela aplicação | `sigis` |
| `DB_PASS` | Senha desse usuário | `sigis` |
| `DB_ROOT_PASS` | Senha do usuário `root` do MySQL | `root` |

Os uploads de evidências ficam persistidos em `./uploads`, montado como volume no container `app` — arquivos enviados sobrevivem a rebuilds.

## Rodando sem Docker

Requisitos: PHP 8+ com extensão `pdo_mysql`, servidor Apache/Nginx e MySQL 8+.

1. Crie o banco e importe o schema:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. Configure as credenciais em `config/db.php` (ou exporte as variáveis de ambiente `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, que têm prioridade sobre os valores padrão do arquivo).

3. Aponte o document root do seu servidor web para a raiz do projeto.

## Estrutura do projeto

```
config/         Configuração de conexão com o banco (config/db.php)
database/       Schema SQL (database/schema.sql)
includes/       Bootstrap, funções utilitárias e layout compartilhado
assets/         CSS
uploads/        Arquivos enviados (evidências)
*.php           Uma página por funcionalidade (dashboard, projetos, estoque, doacoes...)
```
