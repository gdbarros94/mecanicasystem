# Prompt para Criação do Backend PHP para Oficina Mecânica

## Descrição do Aplicativo

O sistema é uma aplicação para oficinas mecânicas, desenvolvida em PHP puro, que gerencia produtos, serviços, estoque, usuários, mecânicos, entre outros. O banco de dados é relacional (MariaDB/MySQL) e possui tabelas como:

- `inventory_list`: controle de estoque de produtos.
- `product_list`: cadastro de produtos.
- `service_list`: cadastro de serviços.
- `mechanic_list`: cadastro de mecânicos.
- `users`: cadastro de usuários.
- Outras tabelas relacionadas a transações, relatórios, configurações do sistema, etc.

Os processos principais envolvem:
- Cadastro, edição, exclusão e listagem de produtos, serviços, mecânicos, usuários e estoque.
- Autenticação de usuários.
- Relatórios de vendas e serviços.
- Configurações do sistema.

## Objetivo

Criar um backend moderno em PHP, na pasta `backend`, que expõe uma API RESTful para todas as entidades do sistema, realizando CRUD completo e retornando objetos JSON para serem consumidos pelo frontend (Flutter).

---

## TODO Detalhado para a Inteligência Artificial

### 1. Estrutura Inicial
- Criar a pasta `/backend`.
- Criar subpastas: `/backend/api`, `/backend/config`, `/backend/models`, `/backend/controllers`.
- Criar arquivo principal de rotas: `/backend/api/index.php`.

### 2. Configuração
- Criar arquivo de configuração do banco de dados em `/backend/config/db.php`.
- Implementar conexão segura com o banco.

### 3. Modelos
- Para cada entidade (`Product`, `Service`, `Inventory`, `Mechanic`, `User`, etc.), criar um modelo PHP em `/backend/models/`.
- Cada modelo deve conter métodos para CRUD (create, read, update, delete).

### 4. Controladores
- Para cada entidade, criar um controlador em `/backend/controllers/` que recebe requisições HTTP, chama os métodos do modelo e retorna respostas JSON.

### 5. Rotas (API)
- No `/backend/api/index.php`, criar rotas RESTful para cada entidade:
  - GET `/products` (listar), GET `/products/{id}` (detalhe)
  - POST `/products` (criar)
  - PUT `/products/{id}` (atualizar)
  - DELETE `/products/{id}` (excluir)
  - Repetir para `services`, `inventory`, `mechanics`, `users`, etc.

### 6. Respostas JSON
- Todas as respostas devem ser em JSON, com status, mensagem e dados.
- Exemplo: `{ "status": "success", "data": { ... } }`

### 7. Autenticação
- Implementar autenticação por token (JWT recomendado).
- Proteger rotas sensíveis.

### 8. Validação e Segurança
- Validar dados recebidos.
- Proteger contra SQL Injection e XSS.

### 9. Documentação
- Documentar todas as rotas e exemplos de requisições/respostas.

### 10. Testes
- Testar todos os endpoints com dados reais do banco.

---

## Instruções para Iteração

1. Analise o banco de dados e entidades existentes.
2. Crie os arquivos e pastas conforme o TODO.
3. Implemente CRUD para cada entidade, testando cada endpoint.
4. Garanta que todas as respostas estejam em JSON e sejam compatíveis com o consumo pelo Flutter.
5. Implemente autenticação e proteja as rotas.
6. Documente o backend.
7. Só finalize quando todos os itens do TODO estiverem completos e testados.
