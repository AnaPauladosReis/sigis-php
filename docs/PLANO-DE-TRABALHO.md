# SIGIS — Plano de trabalho até a banca final

> Documento para o **Claude Code** trabalhar no repositório `AnaPauladosReis/sigis-php`.
> Levantamento feito em 08/11/2026 sobre o commit `d3010d9` (branch `main`), comparando o código com o documento da GERSC ("Projeto – Gerência de Responsabilidade Social – TCC": RF001–RF027, RN001–RN038, RNF001–RNF008).
> Sugestão: salvar este arquivo como `docs/PLANO-DE-TRABALHO.md` no repositório e marcar as caixas `[x]` conforme as tarefas forem concluídas.

---

## 0. Como o Claude Code deve usar este plano

1. **Uma tarefa por vez.** Ler a tarefa inteira, propor o plano em poucas linhas, implementar, testar e só então passar para a próxima.
2. **Uma branch por fase** (`fase-1-seguranca`, `fase-2-perfis`…) e **um commit por tarefa**, com mensagem em português no padrão do histórico (`Adicionar…`, `Corrigir…`, `Ajustar…`).
3. **Não fazer push nem abrir PR sem a Ana pedir.**
4. **Respeitar a arquitetura atual** (é uma decisão do projeto, defendida na banca):
   - PHP 8.2 **procedural**, sem framework e **sem Composer** em produção.
   - Padrão de dois arquivos por módulo: `X.php` (tela, GET) e `X_salvar.php` (gravação, POST → valida → grava → `flash_set()` → `redirect()`).
   - Todo acesso ao banco via **PDO com prepared statements** (`$pdo` vem de `includes/bootstrap.php`).
   - Toda saída HTML passa por `e()`.
   - Funções compartilhadas novas vão para `includes/functions.php` (ou um novo `includes/*.php` incluído pelo `bootstrap.php`).
   - Interface em português, Bootstrap 5 e o CSS de `assets/css/style.css`.
5. **Mudanças de banco** sempre em duas partes:
   - um arquivo novo em `database/migrations/NNN_descricao.sql` (para bancos que já existem);
   - a mesma mudança refletida em `database/schema.sql` (para instalações do zero).
6. **Definição de pronto** de cada tarefa: critério de aceite testado manualmente com `docker compose up -d --build` + `php -l` sem erros em todos os arquivos alterados + este arquivo atualizado.
7. Quando a tarefa marcar **⚠ Decisão da Ana**, perguntar antes de implementar.

### Prompt sugerido para cada sessão

```
Leia docs/PLANO-DE-TRABALHO.md. Vamos fazer a tarefa X.Y.
Siga as regras da seção 0. Antes de editar, me mostre o plano em poucas linhas.
Ao terminar, rode php -l nos arquivos alterados, descreva como testei o critério de aceite
e marque a tarefa como concluída no plano.
```

### Ambiente local

```bash
cp .env.example .env
docker compose up -d --build        # app em http://localhost:8080
# login de demonstração: admin@gersc.org.br / gersc123
docker compose down -v              # apaga o banco para recomeçar do zero
```

---

## 1. Onde o projeto está hoje

**Funciona de ponta a ponta:** login/logout · cadastro e edição de itens com multiplicador · entradas de materiais (atualizam o estoque) · estoque com busca · produção social (consome estoque, registra resíduo) · direcionamento de doações em 3 passos (valida saldo, calcula vidas impactadas, infere o projeto) · Termo de Doação no modelo oficial SESI com numeração `NNN/AAAA` e impressão · upload de evidências · 6 relatórios em CSV · dashboard · importação dos dados reais (87 itens, 13 projetos, 10 grupos, 37 instituições).

**Situação dos 18 módulos do MVP da GERSC**

| Situação | Módulos |
|---|---|
| Concluído (9) | Login e acesso · Itens de impacto · Entradas · Estoque · Produção social · Direcionamento de doações · Termo de Doação · Indicadores (vidas) · Dashboard |
| Parcial (6) | Projetos (sem editar/inativar) · Grupos (sem criar/editar pela tela) · Instituições (sem editar/inativar/histórico) · Movimentações (sem histórico unificado nem ajuste) · Evidências (ligadas à instituição, não à doação) · Relatórios (só CSV, sem filtro de período) |
| Pendente (3) | Usuários e permissões · Origens das entradas · Auditoria |

---

## 2. Visão geral das fases

| Fase | Tema | Prioridade | Requisitos principais |
|---|---|---|---|
| 1 | Segurança e infraestrutura | **P0 – antes de qualquer outra coisa** | RNF002, RNF006 |
| 2 | Regras de estoque e validações | **P0** | RN014–RN016, RN030, RNF006 |
| 3 | Usuários, perfis e permissões | **P0** | RF002, RF021, RN001, RN002, RN031 |
| 4 | Completar cadastros (editar/inativar) e origens | **P0** | RF003–RF008, RN011–RN013, RN037 |
| 5 | Fluxo de negócio (produção, doação, evidências, movimentações) | P1 | RF011–RF015, RN018, RN020, RN028 |
| 6 | Auditoria | P1 | RF020, RN030, RN034, RNF008 |
| 7 | Consultas, relatórios e dashboard | P1 | RF013, RF017–RF019, RF023–RF025 |
| 8 | Configurações e trocas solidárias | P2 | RF022, RF026, RF027 |
| 9 | Testes automatizados e CI | P1 | Estratégia de testes do TCC, RNF004 |
| 10 | Implantação e entregáveis | P1 | Entregáveis do TCC |

P0 = necessário para a banca final · P1 = muito recomendado · P2 = se houver tempo (pode virar "trabalho futuro").

---

## Fase 1 — Segurança e infraestrutura (P0)

### [x] 1.1 Impedir que arquivos internos sejam servidos pelo Apache
**Problema:** o `Dockerfile` copia o repositório inteiro para `/var/www/html` e o `.dockerignore` só exclui `.git`, `.gitattributes` e `README.md`. Ficam acessíveis pelo navegador `database/*.sql` (inclusive o hash da senha do admin), `docs/` e, se existir no momento do build, o **`.env` com as senhas do banco**. Além disso, o `php:8.2-apache` usa `AllowOverride None`, então `config/.htaccess` e `uploads/.htaccess` **são ignorados**.
**Arquivos:** `Dockerfile`, `.dockerignore`, novo `docker/apache-sigis.conf`.
**Passos:**
- Acrescentar ao `.dockerignore`: `.env`, `database/`, `docs/`, `tests/`, `.github/`, `*.md`, `docker-compose*.yml`.
- Criar `docker/apache-sigis.conf` com as regras no próprio Apache (sem depender de `.htaccess`): negar `config/`, `includes/` e qualquer arquivo iniciado por ponto; em `uploads/`, negar a execução de PHP (`<FilesMatch "\.(php|phtml|phar|php\d)$"> Require all denied`) e `php_admin_flag engine off`.
- No `Dockerfile`, copiar esse arquivo para `/etc/apache2/conf-enabled/`.
- Manter os `.htaccess` (servem para hospedagem compartilhada).

**Aceite:** `curl -I localhost:8080/database/schema.sql` → 403/404; `curl -I localhost:8080/includes/functions.php` → 403; um `.php` colocado à mão em `uploads/evidencias/` não executa.

### [x] 1.2 Corrigir o upload de evidências
**Problema:** `evidencias_salvar.php` valida o MIME real, mas monta a extensão a partir do **nome enviado** (`pathinfo($_FILES['arquivo']['name'])`). Um GIF válido chamado `x.php` é gravado como `.php` — junto com 1.1, isso é execução remota de código.
**Passos:**
- Derivar a extensão do MIME validado: `image/jpeg→jpg`, `image/png→png`, `image/webp→webp`, `image/gif→gif`.
- Validar também com `getimagesize()`.
- Limitar o tamanho (ex.: 5 MB) e tratar todos os códigos de `$_FILES['arquivo']['error']`.
- Gerar o nome com `bin2hex(random_bytes(8))` em vez de `mt_rand()`.

**Aceite:** enviar `teste.php` com conteúdo de GIF grava `evidencia-…gif`; arquivo de 10 MB é recusado com mensagem amigável.

### [x] 1.3 Proteção CSRF em todos os formulários
**Problema:** nenhum formulário de escrita tem token.
**Passos:**
- Em `includes/functions.php`: `csrf_token()`, `csrf_field()` (imprime `<input type="hidden" name="csrf">`) e `csrf_check()` (compara com `hash_equals`; se falhar, `http_response_code(400)` e encerra).
- Adicionar `csrf_field()` em **todos** os `<form method="post">`: modais de `projetos`, `grupos` (novo e editar item), `instituicoes`, `entradas`, `producao`, `evidencias`, os 3 passos de `doacoes.php` e `login.php`.
- Chamar `csrf_check()` no início de todo `*_salvar.php`, `grupos_item_editar.php`, no bloco POST de `doacoes.php` e no POST do `login.php`.
- Trocar o logout para POST (botão em formulário com token).

**Aceite:** `grep -L "csrf_check" *_salvar.php grupos_item_editar.php` não retorna nada; um POST sem token devolve 400.

### [x] 1.4 Endurecer login e sessão
**Passos:**
- `session_regenerate_id(true)` logo após o login bem-sucedido.
- Antes do `session_start()` em `bootstrap.php`: `session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])])`.
- Limitar tentativas: tabela `login_tentativas (email, ip, criado_em)`; após 5 falhas em 15 min para o mesmo e-mail ou IP, bloquear por 15 min com mensagem genérica.
- Expirar a sessão após inatividade (ex.: 2 h).

**Aceite:** o ID da sessão muda após o login; a 6ª senha errada seguida bloqueia mesmo com a senha certa.

### [x] 1.5 Não vazar mensagens de erro
**Problema:** `config/db.php` imprime `$e->getMessage()` e `doacoes.php` mostra a mensagem da exceção ao usuário; exceções não tratadas (ex.: CNPJ duplicado em `instituicoes_salvar.php`) mostram erro cru do PHP.
**Passos:**
- `config/db.php`: registrar com `error_log()` e mostrar só "Erro ao conectar ao banco de dados."
- Em `bootstrap.php`: `set_exception_handler()` que faz `error_log()` e mostra uma página amigável (`includes/erro.php`); `display_errors=0` no Docker.
- Em `doacoes.php`: trocar `$ex->getMessage()` por mensagem genérica + `error_log()`.

**Aceite:** com o banco parado, a tela mostra mensagem genérica e o detalhe aparece só em `docker compose logs app`.

### [x] 1.6 Neutralizar injeção de fórmulas no CSV
**Arquivo:** `relatorios_exportar.php`.
**Passos:** antes do `fputcsv`, prefixar com `'` todo valor que comece com `=`, `+`, `-`, `@`, tab ou CR. Gravar o BOM UTF-8 no início para o Excel abrir acentos corretamente.
**Aceite:** uma origem cadastrada como `=1+1` aparece literalmente no Excel.

### [x] 1.7 Docker: credenciais e porta do banco
**Passos:**
- Remover `ports: "3306:3306"` do serviço `db` (o app acessa pela rede interna). Se precisar acessar de fora, criar `docker-compose.override.yml` só local.
- Tirar os valores padrão de senha (`:-sigis`, `:-root`) e exigir `.env` (`${DB_PASS:?defina DB_PASS no .env}`).
- Atualizar `README.md`.

**Aceite:** `docker compose up` sem `.env` falha com mensagem clara; `nc -z localhost 3306` falha.

### [x] 1.8 Instalação do zero com os dados reais
**Problema:** o `docker-compose.yml` só monta `database/schema.sql` em `/docker-entrypoint-initdb.d`. As **37 instituições reais** (`import_instituicoes_nn_2026.sql`) não são carregadas, e o `schema.sql` traz 4 instituições e 4 evidências de exemplo (os arquivos dessas evidências não existem).
**Passos:**
- Montar os scripts em ordem: `01_schema.sql`, `02_instituicoes.sql` e, opcionalmente, `03_seed_dados_teste.sql` (só se `SEED_TESTE=1`).
- Mover as 4 instituições e as 4 evidências fictícias do `schema.sql` para o seed de teste.
- Criar `database/migrations/` com um `README.md` explicando a convenção (`NNN_descricao.sql`, aplicadas em ordem, nunca editadas depois de aplicadas).
- Remover do Git `uploads/evidencias/evidencia-20260813024937-3406.png` e ignorar `uploads/evidencias/*` (mantendo `.gitkeep`).

**Aceite:** `docker compose down -v && docker compose up -d --build` → tela de Instituições mostra as 37 reais e nenhuma fictícia.

---

## Fase 2 — Regras de estoque e validações (P0)

### [x] 2.1 Função única para movimentar estoque
**Problema:** a validação de saldo em `producao_salvar.php` e `doacoes.php` acontece **antes** do `beginTransaction()` e o `UPDATE estoque SET saldo = saldo - ?` não confere o saldo — dois usuários ao mesmo tempo podem deixar o estoque negativo (RN015). `entradas_salvar.php` e `producao_salvar.php` também não fazem `rollBack()` se algo falhar.
**Passos:**
- Criar `includes/estoque.php` (incluído pelo `bootstrap.php`) com `estoque_movimentar(PDO $pdo, int $itemId, float $delta, string $tipo, ?string $refTipo, ?int $refId): void`. Para saída: `UPDATE estoque SET saldo = saldo + ? WHERE item_id = ? AND saldo + ? >= 0` e lançar exceção se `rowCount() === 0`.
- Usar essa função em `entradas_salvar.php`, `producao_salvar.php` e `doacoes.php`, sempre dentro de `try { beginTransaction … commit } catch { rollBack … }`.
- (A tabela de movimentações entra na tarefa 5.4; deixar a função pronta para gravar nela.)

**Aceite:** simular duas doações simultâneas do último saldo (dois navegadores) → uma é recusada e o saldo nunca fica negativo.

### [x] 2.2 Validação de dados no servidor
**Passos:** criar helpers (`validar_data()`, `validar_numero_positivo()`, `validar_cnpj()` com dígitos verificadores, `existe_id($pdo, $tabela, $id)`) e aplicar em todos os `*_salvar.php`:
- datas válidas e não futuras;
- quantidades > 0;
- `item_id`, `projeto_id`, `instituicao_id` existentes e ativos;
- CNPJ com formato e dígitos válidos; **CNPJ duplicado** → mensagem amigável (hoje estoura exceção).

**Aceite:** enviar formulários manipulados (ID inexistente, quantidade negativa, data `2026-13-40`) gera mensagem de erro, nunca erro do PHP.

### [x] 2.3 Unidade correta na produção social
**Problema:** `producao.php` rotula a quantidade consumida como "kg" e `producao_salvar.php` diz "disponível: X kg", mas os itens têm unidades `un`, `par`, `L`, `kg`.
**Passos:** mostrar a unidade real do item no formulário (ao trocar o item) e nas mensagens/listagens.
**Aceite:** consumir "Toalhas de Mesa" mostra "un".

---

## Fase 3 — Usuários, perfis e permissões (P0)

> ⚠ **Decisão da Ana:** confirmar a matriz de permissões abaixo (proposta a partir da tabela de perfis do documento da GERSC).

| Tela / ação | Colaborador | Administrador | Gestor |
|---|---|---|---|
| Dashboard, relatórios, consultas | ✔ | ✔ | ✔ |
| Entradas, produção, evidências | ✔ | ✔ | — |
| Cadastrar/editar projetos e instituições | ✔ | ✔ | — |
| Direcionar doação e emitir termo | — | ✔ | — |
| Grupos, itens e multiplicadores | — | ✔ | — |
| Usuários, configurações, auditoria | — | ✔ | — |

### [ ] 3.1 Controle de acesso por perfil
**Problema:** `usuarios.perfil` existe e é guardado na sessão, mas nenhuma tela verifica — `require_login()` é o único controle.
**Passos:**
- Padronizar os valores de `usuarios.perfil` para `Colaborador`, `Administrador`, `Gestor` (migration).
- Criar `require_perfil(array $perfis)` e `pode(string $acao): bool` em `functions.php`, com a matriz acima num único array.
- Chamar no topo de cada tela e de cada `*_salvar.php`.
- Esconder no menu (`includes/layout_top.php`) e nos botões o que o perfil não pode usar.

**Aceite:** um usuário Gestor não vê "Direcionamento de Doações" no menu e recebe 403 ao acessar `doacoes.php` ou fazer POST em `entradas_salvar.php`.

### [x] 3.2 Gestão de usuários (RF002)
**Passos:**
- Migration: `usuarios.ativo TINYINT(1) NOT NULL DEFAULT 1`, `usuarios.trocar_senha TINYINT(1) NOT NULL DEFAULT 0`.
- `usuarios.php` + `usuarios_salvar.php` (só Administrador): listar, criar, editar nome/e-mail/perfil, ativar/desativar, redefinir senha (gera senha temporária e marca `trocar_senha = 1`).
- Login recusa usuário inativo; se `trocar_senha = 1`, redireciona para trocar a senha.
- Impedir que o último administrador ativo seja desativado.

**Aceite:** criar um Colaborador, entrar com ele, desativá-lo pelo admin → o próximo login é recusado.

### [x] 3.3 Meu perfil (RF021)
**Passos:** `perfil.php` + `perfil_salvar.php`: ver dados, alterar nome e senha (pedindo a senha atual, mínimo 8 caracteres). Link no rodapé da sidebar.
**Aceite:** trocar a própria senha e entrar com a nova.

---

## Fase 4 — Completar cadastros e origens (P0)

> Regra geral (RN037): **nada é apagado fisicamente**. Cadastros são **inativados**; registros inativos somem dos seletores de novas operações, mas continuam nas listagens (com filtro "mostrar inativos"), no histórico, nos termos e nos relatórios.

### [x] 4.1 Coluna de ativação nos cadastros
Migration: `ativo TINYINT(1) NOT NULL DEFAULT 1` em `grupos_impacto`, `itens`, `instituicoes`. Em `projetos`, padronizar `status` em `Ativo`/`Inativo`/`Encerrado`.
**Aceite:** migration aplica num banco existente sem perder dados.

### [ ] 4.2 Projetos sociais: editar e inativar (RF003)
`projetos.php` ganha botão editar (modal igual ao de itens) e ativar/inativar; `projetos_salvar.php` passa a tratar `id` (update) além do insert.
**Aceite:** projeto inativado não aparece em Entradas/Produção, mas continua nos relatórios.

### [ ] 4.3 Grupos de impacto pela tela (RF004)
Hoje grupos só são criados via SQL. Criar novo grupo (nome + cor), editar e inativar em `grupos.php` + `grupos_salvar.php`. Inativar grupo com itens ativos → pedir confirmação ou bloquear.
**Aceite:** criar o grupo "Calçados", adicionar um item nele e usá-lo numa entrada.

### [ ] 4.4 Itens: inativar (RF005)
Botão ativar/inativar no modal de edição. Itens inativos saem de Entradas, Produção e do passo 2 de Doações.
**Aceite:** item inativo não aparece no wizard de doação; termos antigos com ele continuam abrindo.

### [ ] 4.5 Instituições: editar, inativar e histórico (RF006, RF007)
- Botão editar no cadastro (mesmos campos do modal atual) e ativar/inativar.
- Nova tela `instituicao.php?id=` com dados, lista de doações recebidas (data, termo, itens, vidas) e evidências.
- Instituição inativa não aparece no passo 1 de Doações.

**Aceite:** abrir uma instituição e ver todas as doações e o total de vidas impactadas.

### [ ] 4.6 Origens das entradas como cadastro (RF008, RN011, RN012)
**Problema:** `entradas.origem` é texto livre (`VARCHAR(150)`).
**Passos:**
- Tabela `origens (id, nome, categoria, ativo)` com categorias: Indústria, Multa social, Biblioteca, Campanha, Gincana, Troca solidária, Doação espontânea, Outra.
- Telas `origens.php` + `origens_salvar.php` (CRUD com inativação).
- `entradas.origem_id` (FK, obrigatório em novas entradas). Migration que cria uma origem para cada texto distinto já gravado e preenche o `origem_id`; manter a coluna antiga até validar.
- Formulário de entrada passa a usar `<select>`.

**Aceite:** não é possível registrar entrada sem origem cadastrada; o relatório de entradas mostra origem e categoria.

### [ ] 4.7 Entrada sempre vinculada a projeto (RN013)
Hoje o projeto da entrada é opcional (`<select name="projeto_id">` sem `required`). Tornar obrigatório no formulário e no servidor; pré-selecionar o projeto padrão do item (`itens.projeto_id`).
**Aceite:** POST sem `projeto_id` é recusado.

### [ ] 4.8 Estoque mínimo editável (opcional — "controle de estoque mínimo" está nas propostas futuras do documento)
A coluna `estoque.minimo` existe e `estoque.php` já usa para "baixo", mas não há como editar. Adicionar edição do mínimo por item (Administrador) e destacar itens abaixo do mínimo no dashboard.
**Aceite:** definir mínimo 10 num item com saldo 5 → aparece como baixo.

---

## Fase 5 — Fluxo de negócio (P1)

### [ ] 5.1 Produção social gera itens no estoque (RN017, RN018)
**Problema:** `produto_gerado` é texto livre e a quantidade gerada **não entra no estoque** — o produto da produção não pode ser doado depois.
> ⚠ **Decisão da Ana:** o produto gerado deve ser sempre um item já cadastrado (ex.: "Naninhas") ou pode ser criado na hora?
**Passos (proposta):**
- Migration: `producoes.item_gerado_id` (FK `itens`).
- Formulário: `<select>` de itens para o produto gerado (com opção "cadastrar novo item" se a decisão permitir).
- `producao_salvar.php`: na mesma transação, saída do item consumido e **entrada** do item gerado, ambas via `estoque_movimentar()`.
- Migration de dados: tentar casar `produto_gerado` antigo com `itens.nome`; o que não casar fica só como texto.

**Aceite:** produzir 10 naninhas a partir de retalhos → saldo de retalhos cai, saldo de naninhas sobe, e as naninhas aparecem no wizard de doação.

### [ ] 5.2 Doação sempre associada a projeto (RN020)
**Problema:** `doacoes.php` infere o projeto pelos itens; quando os itens são de projetos diferentes, grava `projeto_id = NULL`, e essa doação some do gráfico "Distribuição por projeto".
> ⚠ **Decisão da Ana:** (A) guardar o projeto **por item** — recomendado; ou (B) pedir o projeto no passo 1 e limitar os itens a ele.
**Passos (opção A):**
- Migration: `doacao_itens.projeto_id` e `doacao_itens.vidas_impactadas` (vidas por linha); preencher a partir de `itens.projeto_id` para as doações existentes.
- Gravar projeto e vidas por item na confirmação.
- Dashboard e relatórios passam a somar vidas por projeto a partir de `doacao_itens`.

**Aceite:** uma doação com itens de "Alimentos" e "Higiene" aparece nas duas fatias do gráfico, com as vidas certas.

### [ ] 5.3 Evidências ligadas à doação (RF015, RN028)
**Problema:** `evidencias` só tem `instituicao_id`; não há vínculo com a doação e a tela mostra só as 8 mais recentes.
**Passos:**
- Migration: `evidencias.doacao_id` (FK), `descricao`, `tipo` (foto/documento), `enviado_por`, `criado_em`.
- Aceitar também PDF para documentos (validação de MIME do item 1.2).
- Enviar evidência a partir da tela do termo (pré-preenche doação e instituição).
- Tela do termo mostra as evidências daquela doação; lista de termos mostra um selo "sem evidência" nas doações sem nenhuma.
- Tela de evidências com listagem completa, filtros (instituição, período) e paginação.

> ⚠ **Decisão da Ana:** RN028 diz que toda doação "deverá possuir" evidência. Bloquear algo ou apenas sinalizar as pendentes? (Proposta: sinalizar.)

**Aceite:** anexar uma foto pelo termo 009/2026 e vê-la na página do termo e da instituição.

### [ ] 5.4 Histórico de movimentações e ajuste de estoque (RF013, RN016, RN030)
**Passos:**
- Tabela `movimentacoes (id, item_id, tipo, quantidade, saldo_apos, ref_tipo, ref_id, motivo, usuario_id, criado_em)`, com tipos: `entrada`, `doacao`, `producao_consumo`, `producao_gerado`, `ajuste`, `troca`.
- `estoque_movimentar()` (tarefa 2.1) passa a gravar nela.
- Migration que gera o histórico a partir de `entradas`, `producoes` e `doacao_itens` já existentes.
- Tela `movimentacoes.php` com filtros por item, tipo, período e usuário.
- Ajuste manual de estoque (Administrador) com motivo obrigatório.

**Aceite:** para qualquer item, a soma das movimentações bate com `estoque.saldo`.

### [ ] 5.5 Data da doação
Hoje `data_doacao` é sempre `CURDATE()`. Permitir informar a data no passo 3 (padrão: hoje, sem datas futuras), para lançar doações retroativas da planilha.
**Aceite:** registrar uma doação com data de 15/10/2026 e o termo sai com essa data.

---

## Fase 6 — Auditoria (P1)

### [ ] 6.1 Log de auditoria (RF020, RN030, RN034, RNF008)
**Passos:**
- Tabela `auditoria (id, usuario_id, acao, entidade, entidade_id, antes JSON, depois JSON, ip, criado_em)`.
- Helper `auditar($pdo, $acao, $entidade, $id, $antes, $depois)` chamado em todo insert/update dos `*_salvar.php`, na confirmação de doação, na emissão de termo, no upload de evidência, em login/logout e falhas de login.
- Atenção especial à alteração de **multiplicador** (guardar valor antigo e novo — RN023).
- Tela `auditoria.php` (Administrador) com filtros por usuário, entidade, ação e período.

**Aceite:** alterar o multiplicador de um item aparece na auditoria com o valor anterior e o novo; o total de vidas das doações antigas não muda.

---

## Fase 7 — Consultas, relatórios e dashboard (P1)

### [ ] 7.1 Lista de termos com busca (RF014, RF024)
`termo.php` hoje lista só os 10 últimos. Criar listagem completa com busca por número, instituição e período, e paginação.

### [ ] 7.2 Filtros e paginação nas listagens (RF024)
Entradas, produção, doações, evidências e movimentações: filtros por período, projeto, instituição, item e grupo; paginação de 25 em 25. Reaproveitar um helper `paginar()`.

### [ ] 7.3 Relatórios com período e versão para impressão/PDF (RF018, RF023)
- Filtro de data inicial/final em todos os relatórios.
- Novos relatórios: movimentações de estoque e resíduos reaproveitados por projeto e período (RN025).
- Versão HTML para impressão de cada relatório (CSS de impressão, "Salvar como PDF" do navegador), seguindo o mesmo caminho do termo — **sem biblioteca de PDF**, para manter zero dependências.

### [ ] 7.4 Corrigir e completar o dashboard (RF017)
- **Bug:** "Itens doados" soma quantidades de unidades diferentes (`un` + `kg` + `L`). Mostrar o número de doações e/ou o total por unidade.
- Acrescentar: projetos ativos, doações realizadas, itens produzidos, **toneladas reutilizadas** (`quantidade × peso_unitario`).
- Filtro de período (mês atual, ano, personalizado).
- Itens abaixo do estoque mínimo (depende de 4.8).

> ⚠ **Pendência de dados:** grande parte dos itens tem `peso_unitario = 0` na planilha. Sem esses pesos, o indicador de toneladas fica subestimado — levantar os pesos com a GERSC.

### [ ] 7.5 Busca global da barra superior
O campo "Buscar em SIGIS…" em `includes/layout_top.php` não faz nada (`onsubmit="return false"`). Implementar `busca.php` (instituições, itens, projetos, termos) **ou** remover o campo.

### [ ] 7.6 Página do projeto (RF019)
`projeto.php?id=` com entradas, produção, doações, vidas impactadas e resíduos do projeto. (Aproveitar para trocar as consultas N+1 de `projetos.php` por uma consulta agregada.)

---

## Fase 8 — Configurações e trocas solidárias (P2)

### [ ] 8.1 Configurações do sistema (RF022)
Os dados do termo (`TERMO_REGIONAL`, `TERMO_CIDADE`, `TERMO_SIGNATARIO_NOME`, `TERMO_SIGNATARIO_CARGO`) estão fixos com `define()` em `termo.php`. Criar tabela `configuracoes (chave, valor)` e tela `configuracoes.php` (Administrador) para esses campos e para o número inicial do termo. Registrar alterações na auditoria.

### [ ] 8.2 Trocas solidárias (RF027)
Tabela `trocas (id, data, parceiro, item_entregue_id, qtd_entregue, item_recebido_id, qtd_recebida, observacao, usuario_id)`; telas `trocas.php` + `trocas_salvar.php`; as duas pontas movimentam o estoque via `estoque_movimentar()` (tipo `troca`).

### [ ] 8.3 Parametrização das vidas impactadas (RF026)
Já atendido em parte (multiplicador por item, editável; vidas gravadas na doação). Completar com a auditoria do multiplicador (6.1) e com texto de ajuda na tela de itens explicando a regra.

---

## Fase 9 — Testes automatizados e CI (P1)

> Manter o **runtime** sem dependências. Ferramentas de teste ficam só no ambiente de desenvolvimento.

### [ ] 9.1 Isolar as regras de negócio em funções
Mover para `includes/regras.php` funções puras e testáveis: `calcular_vidas($itens)`, `proximo_numero_termo($numerosExistentes, $ano)`, `validar_cnpj()`, `extensao_por_mime()`, `pode($perfil, $acao)`.

### [ ] 9.2 Testes unitários e de integração
`tests/run.php`: um executor simples em PHP puro (sem Composer) com asserts, rodando contra um banco de teste (`sigis_test`). Cobrir: cálculo de vidas, numeração do termo (virada de ano), saldo nunca negativo, validação de CNPJ, matriz de permissões, extensão do upload.

### [ ] 9.3 Testes E2E com Playwright
`tests/e2e/` (Node, só em desenvolvimento) com os **6 cenários do documento da GERSC**: registro de entrada · produção social · realização da doação · cálculo das vidas · geração do termo · dashboard e indicadores. Usar o seed de teste.

### [ ] 9.4 CI no GitHub Actions
`.github/workflows/ci.yml`: `php -l` em todos os `.php`, `tests/run.php` com serviço MySQL 8 e, opcionalmente, os testes E2E com `docker compose`.

### [ ] 9.5 Registro dos testes para o artigo
`docs/testes.md` com a tabela cenário × resultado × data, e os testes de aceitação feitos com a GERSC (ver 10.4).

---

## Fase 10 — Implantação e entregáveis (P1)

### [ ] 10.1 Ambiente de produção
- `docker-compose.prod.yml`: banco sem porta exposta, `restart: unless-stopped`, Nginx como proxy reverso com HTTPS (Let's Encrypt) — conforme a infraestrutura sugerida no documento (VPS Linux + Docker + Nginx).
- Backup diário: `mysqldump` + cópia de `uploads/`, com retenção de 7 dias (script em `scripts/backup.sh` + cron).

### [ ] 10.2 Primeiro acesso seguro
Remover o usuário `admin@gersc.org.br / gersc123` do `schema.sql` de produção: criar o primeiro administrador por script (`scripts/criar_admin.php`) com `trocar_senha = 1`. Manter o usuário de demonstração só no seed de teste.

### [ ] 10.3 Documentação
- `README.md`: instalação local e em produção, variáveis, backup e restauração.
- `docs/manual-usuario.md`: passo a passo de cada tela por perfil, com capturas atualizadas.
- `docs/documentacao-tecnica.md`: atualizar diagrama ER (novas tabelas), matriz de permissões, fluxo de estoque e decisões de arquitetura (incluindo a justificativa de PHP procedural em vez de React + Node).

### [ ] 10.4 Validação com a GERSC
Checklist de aceitação com os critérios do documento: controle dos projetos · entradas e saídas · produção social · cálculo automático das vidas · termo automático · evidências · indicadores atualizados · relatórios · rastreabilidade. Registrar o retorno e os ajustes em `docs/testes.md`.

---

## 3. Decisões pendentes da Ana (resolver antes das tarefas indicadas)

| # | Decisão | Tarefa |
|---|---|---|
| 1 | Confirmar a matriz de permissões por perfil | 3.1 |
| 2 | Produto da produção: só item já cadastrado ou pode criar na hora? | 5.1 |
| 3 | Projeto da doação: por item (recomendado) ou um projeto por doação? | 5.2 |
| 4 | Evidência obrigatória: bloquear ou só sinalizar doações sem evidência? | 5.3 |
| 5 | Numeração do termo `NNN/AAAA` reiniciando a cada ano: confirmar com a GERSC | 7.1 / 8.1 |
| 6 | Pesos unitários que faltam na planilha (para "toneladas reutilizadas") | 7.4 |
| 7 | Onde hospedar (VPS própria, do SESI, outra) | 10.1 |

## 4. Ordem sugerida de execução

1. Fase 1 inteira (1.1 → 1.8) — corrige riscos reais, incluindo execução de código pelo upload.
2. Fase 2 (2.1 → 2.3) — base para todo o resto do estoque.
3. Fase 3 (3.1 → 3.3).
4. Fase 4 (4.1 → 4.8).
5. 5.2, 5.1, 5.4, 5.3, 5.5.
6. 6.1.
7. 7.4, 7.1, 7.3, 7.2, 7.6, 7.5.
8. Fase 9 em paralelo a partir da Fase 2 (escrever o teste junto com cada regra).
9. Fase 10 nas últimas semanas antes da banca final.
10. Fase 8 se houver tempo — senão, citar como trabalho futuro na apresentação.
