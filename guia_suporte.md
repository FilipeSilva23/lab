# Guia
## Estrutura  do Projeto
- **app/Http/Controllers/**: Faz a ponte entre as rotas e a resposta. Recebe o pedido (já validado pelo FormRequest), chama o Service quando há regras de negócio e escolhe a view
- **app/Http/Requests/**: FormRequests. `rules()` valida o formato dos dados e `authorize()` decide se o utilizador pode fazer a ação. Correm antes do controller
- **app/Http/Middleware/**: Filtros que correm antes do controller (ex.: `SetLocale` aplica o idioma guardado na sessão, validado contra `config('rentify.locales')`)
- **app/Policies/**: Quem pode fazer o quê sobre um registo concreto (ex.: `ItemPolicy`: admin pode tudo, o dono só os seus itens)
- **app/Enums/**: Valores fixos em vez de strings soltas (ex.: `UserRole`: student, service, admin; usado como cast no `User`)
- **app/Services/**: Regras de negócio em classes PHP simples que não sabem que existe HTTP (ex.: `ReservationService`: limite de dias, intervalo entre reservas, stock, itens do mesmo dono)
- **app/Models/**: Cada ficheiro representa uma tabela (ex.: `User.php` para `users`). Define relações, `$fillable` e `casts`
- **routes/web.php**: URLs e rotas web (grupos `guest` e `auth`)
- **resources/views/**: HTML + Blade (layout, partials, vistas por recurso)
- **resources/css/ e resources/js/**: Frontend (Tailwind via Vite)
- **lang/pt e lang/en**: Textos da interface. Nas views usa-se `__('chave')`, nunca texto fixo
- **config/rentify.php**: Configuração própria (`locales`, `max_days`, `gap_days`, credenciais do Admin lidas do `.env`)
- **database/migrations/**: Estrutura das tabelas
- **database/seeders/**: Dados iniciais (Admin, categorias, locais de entrega) e dados de demo
- **database/factories/**: Dados falsos para testes (`UserFactory`, `ItemFactory`)
---

## Onde fica cada regra
| Regra | Onde | Exemplo |
|---|---|---|
| Formato dos dados | FormRequest `rules()` | `StoreItemRequest` |
| Quem pode fazer a ação | Policy / `authorize()` / middleware `auth` | `ItemPolicy` |
| Regra de negócio | Service | `ReservationService` |
| Valor configurável | `config/rentify.php` | `max_days`, `gap_days` |
| Segredo / ambiente | `.env` | `APP_KEY`, `ADMIN_PASSWORD` |
| Texto visível | `lang/` | `__('items.title')` |
| Valor fixo | Enum | `UserRole` |
### Eloquent (ORM)  
Permite interagir com a base de dados através de classes e objetos em PHP, dispensando a escrita manual de SQL

---
## Migration
- `database/migrations/`
- Ficheiro que descreve a **estrutura da tabela na base de dados** (colunas, tipos, chaves).
- `up()` para criar ou alterar
- `down()` para reverter
- Vai para o git, só tem efeito quando se corre `php artisan migrate`.

## Model
- `app/Models/`
- Classe PHP que representa uma tabela **no código** (Eloquent)
- Permite usar `Item::all()` em vez de SQL e define as relações (`belongsTo`, `hasMany`, `belongsToMany`) e o `$fillable`.
- **Não cria a tabela.**

## Migration vs Model
| | Migration | Model |
|---|---|---|
| Responde a | "Como é a tabela?" | "Como uso a tabela no código?" |
| Cria a tabela | Sim | Não |
| Quando é necessário | Sempre que há uma tabela | Quando a tabela é usada no código |



---

## Comandos de Migration (Base de Dados)
- **php artisan make:migration create_items_table**  
Cria um novo ficheiro de migração na pasta `database/migrations/` preparado para estruturar a tabela `items`.

- **php artisan migrate**  
Executa todas as migrações pendentes, aplicando a função `up()` de cada uma para criar efetivamente as tabelas na base de dados

- **php artisan migrate:rollback**
  Anula o **último batch** (todas as migrações do último `migrate`). Para anular só uma: `--step=1`

- **php artisan migrate:fresh** (com `--seed` para voltar a povoar)
  Apaga **todas as tabelas e todos os dados** e volta a aplicar as migrações de raiz. Só em desenvolvimento, nunca em produção

- **php artisan migrate:status**  
Verifica o status de todas as tabelas

---

## Comandos de Desenvolvimento e Utilidade Geral
- **php artisan serve --port=8080**  
Inicia o servidor de desenvolvimento local

- **php artisan route:list**  
Mostra todas as rotas registadas na aplicação. Usar para diagnosticar erros 404

- **php artisan tinker**  
Abre consola interativa no terminal com a aplicação carregada, permitindo testar comandos e consultas à base de dados em tempo real

- **php artisan about**  
Mostra um resumo completo das versões e configurações atuais do projeto

---

## Comandos de Criação Rápida (Scaffolding)
- **php artisan make:model Item**: Só o model (as migrações já existem). Nomes em inglês, singular. Com `-mcr` cria também migração e controller de recurso
- **php artisan make:controller ItemController --resource --model=Item --requests**: Controller de recurso com os FormRequests Store/Update
- **php artisan make:request StoreItemRequest**: FormRequest isolado
- **php artisan make:policy ItemPolicy --model=Item**: Policy com os métodos base
- **php artisan make:factory ItemFactory**: Factory para testes
- **php artisan make:seeder NomeSeeder**: Seeder. Tem de ser chamado no `DatabaseSeeder` ou com `db:seed --class=NomeSeeder`, e deve poder correr duas vezes sem duplicar (`updateOrCreate`)
- **php artisan make:test Item/ItemCrudTest --pest**: Teste Pest
---

## Comandos de Manutenção
- **php artisan key:generate**: Gera `APP_KEY` no `.env`. Necessário depois de criar o `.env` ao clonar. **Nunca em produção**: invalida sessões e dados encriptados
- **php artisan config:clear**: Limpa a cache de configuração. Só importa se a config estiver em cache (`config:cache`)
- **php artisan optimize:clear**: Limpa todas as caches (config, rotas, views)
- **php artisan db:seed** (`--class=AdminSeeder`): Corre os seeders
- **composer audit** e **npm audit**: Procuram vulnerabilidades conhecidas nas dependências
