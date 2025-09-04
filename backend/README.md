# API Documentation - Sistema de Oficina Mecânica

## Visão Geral

Esta API RESTful foi desenvolvida para gerenciar um sistema de oficina mecânica, fornecendo endpoints para todas as entidades principais do sistema.

**Base URL:** `http://localhost/backend/api/`

## Autenticação

A API utiliza JWT (JSON Web Tokens) para autenticação. Após fazer login, você receberá um token que deve ser incluído no header `Authorization` de todas as requisições protegidas.

```
Authorization: Bearer {seu-token-jwt}
```

### Login

**POST** `/auth/login`

Realiza autenticação do usuário.

**Request Body:**
```json
{
    "username": "admin",
    "password": "sua-senha"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Login realizado com sucesso",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "firstname": "Admin",
            "lastname": "User",
            "username": "admin",
            "type": 1
        }
    }
}
```

## Produtos

### Listar Produtos

**GET** `/products`

Lista todos os produtos ativos.

**Response:**
```json
{
    "status": "success",
    "message": "Produtos listados com sucesso",
    "data": [
        {
            "id": 1,
            "name": "Óleo Motor",
            "description": "Óleo para motor 5W30",
            "price": 45.90,
            "image_path": "uploads/products/1.jpg",
            "status": 1,
            "date_created": "2024-01-01 10:00:00",
            "date_updated": "2024-01-01 10:00:00"
        }
    ]
}
```

### Obter Produto

**GET** `/products/{id}`

Obtém informações de um produto específico.

### Criar Produto

**POST** `/products`

Cria um novo produto. *(Autenticação requerida)*

**Request Body:**
```json
{
    "name": "Nome do Produto",
    "description": "Descrição do produto",
    "price": 99.99,
    "image_path": "uploads/products/produto.jpg",
    "status": 1
}
```

### Atualizar Produto

**PUT** `/products/{id}`

Atualiza um produto existente. *(Autenticação requerida)*

### Deletar Produto

**DELETE** `/products/{id}`

Remove um produto (soft delete). *(Autenticação requerida)*

### Buscar Produtos

**GET** `/products?search={termo}`

Busca produtos por nome ou descrição.

## Serviços

### Listar Serviços

**GET** `/services`

Lista todos os serviços ativos.

### Obter Serviço

**GET** `/services/{id}`

Obtém informações de um serviço específico.

### Criar Serviço

**POST** `/services`

Cria um novo serviço. *(Autenticação requerida)*

**Request Body:**
```json
{
    "name": "Nome do Serviço",
    "description": "Descrição do serviço",
    "price": 150.00,
    "status": 1
}
```

### Atualizar Serviço

**PUT** `/services/{id}`

Atualiza um serviço existente. *(Autenticação requerida)*

### Deletar Serviço

**DELETE** `/services/{id}`

Remove um serviço (soft delete). *(Autenticação requerida)*

### Buscar Serviços

**GET** `/services?search={termo}`

Busca serviços por nome ou descrição.

## Estoque

### Listar Estoque

**GET** `/inventory`

Lista todas as entradas de estoque com informações dos produtos.

### Obter Entrada de Estoque

**GET** `/inventory/{id}`

Obtém informações de uma entrada específica.

### Criar Entrada de Estoque

**POST** `/inventory`

Adiciona nova entrada de estoque. *(Autenticação requerida)*

**Request Body:**
```json
{
    "product_id": 1,
    "quantity": 50,
    "stock_date": "2024-01-01"
}
```

### Atualizar Entrada de Estoque

**PUT** `/inventory/{id}`

Atualiza uma entrada de estoque. *(Autenticação requerida)*

### Deletar Entrada de Estoque

**DELETE** `/inventory/{id}`

Remove uma entrada de estoque. *(Autenticação requerida)*

### Obter Estoque por Produto

**GET** `/inventory/{product_id}/stock`

Retorna o estoque total de um produto específico.

### Produtos com Baixo Estoque

**GET** `/inventory?low_stock=1&limit=10`

Lista produtos com estoque baixo (menor que o limite especificado).

### Relatório de Movimentação

**GET** `/inventory?movement=1&start_date=2024-01-01&end_date=2024-01-31`

Relatório de movimentação de estoque por período.

## Mecânicos

### Listar Mecânicos

**GET** `/mechanics`

Lista todos os mecânicos ativos.

### Obter Mecânico

**GET** `/mechanics/{id}`

Obtém informações de um mecânico específico.

### Criar Mecânico

**POST** `/mechanics`

Cria um novo mecânico. *(Autenticação requerida)*

**Request Body:**
```json
{
    "firstname": "João",
    "middlename": "da",
    "lastname": "Silva",
    "status": 1
}
```

### Atualizar Mecânico

**PUT** `/mechanics/{id}`

Atualiza um mecânico existente. *(Autenticação requerida)*

### Deletar Mecânico

**DELETE** `/mechanics/{id}`

Remove um mecânico (soft delete). *(Autenticação requerida)*

### Buscar Mecânicos

**GET** `/mechanics?search={termo}`

Busca mecânicos por nome.

### Mecânicos Ativos

**GET** `/mechanics?active=1`

Lista apenas mecânicos ativos.

## Usuários

### Listar Usuários

**GET** `/users`

Lista todos os usuários. *(Autenticação requerida)*

### Obter Usuário

**GET** `/users/{id}`

Obtém informações de um usuário específico. *(Autenticação requerida)*

### Criar Usuário

**POST** `/users`

Cria um novo usuário. *(Apenas administradores)*

**Request Body:**
```json
{
    "firstname": "Nome",
    "lastname": "Sobrenome",
    "username": "usuario",
    "password": "senha123",
    "avatar": "uploads/avatars/user.jpg",
    "type": 2
}
```

### Atualizar Usuário

**PUT** `/users/{id}`

Atualiza um usuário existente. *(Autenticação requerida)*

### Deletar Usuário

**DELETE** `/users/{id}`

Remove um usuário. *(Apenas administradores)*

### Alterar Senha

**POST** `/users/{id}/change-password`

Altera a senha de um usuário. *(Autenticação requerida)*

**Request Body:**
```json
{
    "new_password": "nova-senha"
}
```

### Buscar Usuários

**GET** `/users?search={termo}`

Busca usuários por nome ou username. *(Autenticação requerida)*

## Transações

### Listar Transações

**GET** `/transactions`

Lista todas as transações.

### Obter Transação

**GET** `/transactions/{id}`

Obtém informações completas de uma transação, incluindo produtos e serviços.

### Criar Transação

**POST** `/transactions`

Cria uma nova transação. *(Autenticação requerida)*

**Request Body:**
```json
{
    "mechanic_id": 1,
    "client_name": "João Cliente",
    "contact": "11999999999",
    "email": "joao@email.com",
    "address": "Rua das Flores, 123",
    "amount": 500.00,
    "status": 0,
    "products": [
        {
            "product_id": 1,
            "qty": 2,
            "price": 45.90
        }
    ],
    "services": [
        {
            "service_id": 1,
            "price": 150.00
        }
    ]
}
```

### Atualizar Transação

**PUT** `/transactions/{id}`

Atualiza uma transação existente. *(Autenticação requerida)*

### Deletar Transação

**DELETE** `/transactions/{id}`

Remove uma transação. *(Autenticação requerida)*

### Atualizar Status

**POST** `/transactions/{id}/update-status`

Atualiza apenas o status da transação. *(Autenticação requerida)*

**Request Body:**
```json
{
    "status": 2
}
```

**Status disponíveis:**
- 0: Pendente
- 1: Em Progresso
- 2: Concluído
- 3: Pago
- 4: Cancelado

### Buscar Transações

**GET** `/transactions?search={termo}`

Busca transações por código, nome do cliente ou contato.

### Relatório de Vendas

**GET** `/transactions?report=1&start_date=2024-01-01&end_date=2024-01-31`

Gera relatório de vendas por período.

**Response:**
```json
{
    "status": "success",
    "message": "Relatório de vendas gerado com sucesso",
    "data": {
        "sales": [...],
        "total_amount": 15000.00,
        "total_transactions": 50,
        "period": {
            "start_date": "2024-01-01",
            "end_date": "2024-01-31"
        }
    }
}
```

## Códigos de Status HTTP

- **200**: Sucesso
- **400**: Erro de validação/dados inválidos
- **401**: Não autorizado (token inválido/ausente)
- **404**: Recurso não encontrado
- **405**: Método não permitido
- **500**: Erro interno do servidor

## Estrutura de Resposta Padrão

Todas as respostas seguem o padrão:

```json
{
    "status": "success|error",
    "message": "Mensagem descritiva",
    "data": {} // Dados da resposta (quando aplicável)
}
```

## Exemplos de Uso

### Exemplo 1: Login e Criação de Produto

```bash
# 1. Fazer login
curl -X POST http://localhost/backend/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"senha"}'

# 2. Criar produto (com token)
curl -X POST http://localhost/backend/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{"name":"Pneu 185/65R15","description":"Pneu aro 15","price":280.00}'
```

### Exemplo 2: Buscar Produtos com Baixo Estoque

```bash
curl -X GET "http://localhost/backend/api/inventory?low_stock=1&limit=5" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

### Exemplo 3: Criar Transação Completa

```bash
curl -X POST http://localhost/backend/api/transactions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "mechanic_id": 1,
    "client_name": "Maria Silva",
    "contact": "11987654321",
    "email": "maria@email.com",
    "address": "Av. Principal, 456",
    "products": [
      {"product_id": 1, "qty": 1, "price": 45.90}
    ],
    "services": [
      {"service_id": 1, "price": 150.00}
    ]
  }'
```

Esta documentação cobre todos os endpoints disponíveis na API. Para usar a API em produção, certifique-se de:

1. Configurar as credenciais do banco de dados em `/config/db.php`
2. Alterar a chave secreta JWT em `/auth/Auth.php`
3. Configurar HTTPS para produção
4. Implementar rate limiting se necessário
