# Rentify - Plataforma de Aluguer para a Comunidade Académica
Projeto de Programação Web Lado Servidor 2026/2027  
Licenciatura em Engenharia Informática  
Universidade Fernando Pessoa  
Bruno Castro 2024118435 e Filipe Silva 2024118471  

## Designação e Descrição
Plataforma marketplace de aluguer de itens na comunidade académica da UFP, com itens de estudantes e da própria faculdade.  
A plataforma permite que estudantes e serviços da faculdade (biblioteca, laboratórios, departamentos) anunciem itens disponíveis para aluguer (como livros, equipamento informático ou equipamento de laboratório).  
Para tal, indicam fotos, preço por dia (que pode ser gratuito), descrição, local de entrega no campus, janela de disponibilidade, condições da reserva, ...  
Os estudantes podem pesquisar e filtrar itens por categoria, preço, estado físico, disponibilidade nas datas pretendidas, origem (estudante ou faculdade), média de avaliações do proprietário, entre outros.  
Em cada reserva há dois papéis: o proprietário (dono do item, estudante ou serviço) e o locatário (o estudante que aluga).  
Após o aluguer, o locatário pode avaliar o proprietário, tal como o proprietário pode avaliar o locatário.  
O registo público é restrito a estudantes com email institucional verificado e existe um processo de pagamento para os itens pagos  

## Perfis de Utilizador Previstos
- Estudante (anuncia itens e aluga itens de estudantes e da faculdade)
- Serviço (conta institucional de um serviço da faculdade, que anuncia os itens da instituição; não aluga)
- Admin

## Lista Preliminar das Atividades
- Utilizador
- Item
- Categoria (relação N:N com Item)
- Foto
- Reserva
- Avaliação
- Pagamento

## Enumeração dos Requisitos Funcionais Principais
- Registo e autenticação de utilizadores com 3 perfis (Estudante, Serviço e Admin);
    - Registo só para Estudantes, com email institucional confirmado por link
    - Contas Serviço criadas pelo Admin
    - Contas Admin por seeder
- CRUD de Itens pelo Estudante e pelo Serviço, apenas sobre os seus próprios itens (fotos, preço por dia, local de entrega, janela de disponibilidade, condições)
- Identificação dos itens da faculdade como oficiais
- Gestão e moderação pelo Admin (utilizadores, itens, categorias e contas Serviço)
- Pesquisa e filtragem de itens por categoria, preço, disponibilidade nas datas, origem e média de avaliações do proprietário
- Pagamento por Stripe (modo de teste), nos itens com preço superior a 0 ou caução
- Confirmação de entrega e de devolução do item
- Sistema de avaliação mútua, só depois de a reserva estar concluída, além de avaliação individual de cada item
- API REST de Itens (consultar, criar, atualizar e remover) com autenticação por token

## Fluxo da Reserva
Pedido → pagamento (só se tiver caução ou preço for superior a 0) → entrega → devolução → avaliação
---
---
## SETUP
###  Requisitos
- PHP (versão indicada no `composer.json`) e Composer
- Node.js e npm
- Servidor MySQL, se não se usar SQLite

### Passo a Passo
1. Instalar dependências PHP:
```bash
   composer install
```
2. Criar o ficheiro `.env` e key:
```bash
   copy .env.example .env      # Windows (em Linux/macOS usar `cp`)
   php artisan key:generate
```
3. Configurar a base de dados e os utilizadores de demonstração no `.env`:
   - **SQLite:** definir `DB_CONNECTION=sqlite`.
   - Definir `ADMIN_EMAIL`, `ADMIN_PASSWORD` e `DEMO_PASSWORD` com credenciais locais de teste.
4. Criar as tabelas com os dados de seed:
```bash
   php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
   php artisan migrate:fresh --seed
```
5. Criar o link público para as imagens:
```bash
   php artisan storage:link
```
6. Instalar e compilar o frontend:
```bash
   npm install
   npm run build
```
7. Iniciar servidor:
```bash
   php artisan serve --port=8080
```

---
## Decisões Técnicas
- **Internacionalização (PT/EN):**   
Sistema nativo do Laravel (`lang/pt/` e `lang/en/`), em vez do i18next.  
Como a aplicação é feita no servidor (Blade), as mensagens de validação e flash só podem ser traduzidas pelo servidor.  
O idioma por defeito é `pt` (`APP_LOCALE` no `.env` e `.env.example`).
- **Layout:**   
Todas as vistas usam `@extends('layout')`.
