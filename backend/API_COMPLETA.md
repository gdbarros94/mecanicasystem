# API Backend - Sistema de Oficina Mecânica - COMPLETA

## Visão Geral
API REST completa para sistema de gestão de oficina mecânica, incluindo autenticação JWT, CRUD completo para todas as entidades, relatórios avançados e upload de imagens.

## Base URL
```
http://localhost/system/backend/api
```

## Autenticação
Todas as rotas (exceto login) requerem autenticação via token JWT no header:
```
Authorization: Bearer {token}
```

---

## 🔐 Autenticação

### POST /auth/login
Login do usuário

**Body:**
```json
{
    "username": "admin",
    "password": "123456"
}
```

**Response Success:**
```json
{
    "status": "success",
    "message": "Login realizado com sucesso",
    "data": {
        "user": {
            "id": 1,
            "username": "admin",
            "firstname": "Admin",
            "lastname": "Sistema",
            "type": 1
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
    }
}
```

---

## 📦 Produtos

### GET /products
Listar todos os produtos

**Query Params:**
- `search` - Buscar por nome/descrição
- `category` - Filtrar por categoria

### GET /products/{id}
Obter produto específico

### POST /products
Criar novo produto

**Body:**
```json
{
    "name": "Óleo Motor 5W30",
    "description": "Óleo sintético para motor",
    "category": "Lubrificantes",
    "price": 45.90,
    "image": "produto.jpg"
}
```

### PUT /products/{id}
Atualizar produto

### DELETE /products/{id}
Deletar produto

---

## 🔧 Serviços

### GET /services
Listar todos os serviços

### GET /services/{id}
Obter serviço específico

### POST /services
Criar novo serviço

**Body:**
```json
{
    "service": "Troca de Óleo",
    "description": "Troca completa do óleo do motor",
    "price": 120.00
}
```

### PUT /services/{id}
Atualizar serviço

### DELETE /services/{id}
Deletar serviço

---

## 📋 Estoque

### GET /inventory
Listar todos os itens do estoque

### GET /inventory/{id}
Obter item específico do estoque

### POST /inventory
Adicionar item ao estoque

**Body:**
```json
{
    "product_id": 1,
    "quantity": 50,
    "stock_in": 50,
    "stock_out": 0
}
```

### PUT /inventory/{id}
Atualizar item do estoque

### DELETE /inventory/{id}
Remover item do estoque

---

## 👨‍🔧 Mecânicos

### GET /mechanics
Listar todos os mecânicos

### GET /mechanics/{id}
Obter mecânico específico

### POST /mechanics
Cadastrar novo mecânico

**Body:**
```json
{
    "name": "João Silva",
    "contact": "(11) 99999-9999",
    "email": "joao@oficina.com",
    "specialization": "Motor"
}
```

### PUT /mechanics/{id}
Atualizar mecânico

### DELETE /mechanics/{id}
Deletar mecânico

---

## 👥 Usuários

### GET /users
Listar todos os usuários

### GET /users/{id}
Obter usuário específico

### POST /users
Criar novo usuário

**Body:**
```json
{
    "firstname": "João",
    "lastname": "Silva",
    "username": "joao",
    "password": "123456",
    "type": 2,
    "avatar": "avatar.jpg"
}
```

### PUT /users/{id}
Atualizar usuário

### DELETE /users/{id}
Deletar usuário

---

## 💰 Transações

### GET /transactions
Listar todas as transações

**Query Params:**
- `type` - Filtrar por tipo (sale/service)
- `start_date` - Data início
- `end_date` - Data fim

### GET /transactions/{id}
Obter transação específica

### POST /transactions
Criar nova transação

**Body:**
```json
{
    "type": "sale",
    "amount": 250.00,
    "description": "Venda de peças",
    "client_name": "Carlos Santos",
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "price": 45.90
        }
    ]
}
```

### PUT /transactions/{id}
Atualizar transação

### DELETE /transactions/{id}
Deletar transação

---

## ⚙️ Configurações do Sistema

### GET /system-settings
Obter todas as configurações

### GET /system-settings/{key}
Obter configuração específica

### POST /system-settings
Atualizar configuração

**Body:**
```json
{
    "meta_field": "system_name",
    "meta_value": "Oficina AutoTech"
}
```

---

## 📊 Relatórios

### GET /reports/daily-sales?date=2024-01-15
Relatório de vendas diário

### GET /reports/daily-service?date=2024-01-15
Relatório de serviços diário

### GET /reports/sales-period?start_date=2024-01-01&end_date=2024-01-31
Relatório de vendas por período

### GET /reports/top-products?limit=10&start_date=2024-01-01&end_date=2024-01-31
Produtos mais vendidos

### GET /reports/top-services?limit=10&start_date=2024-01-01&end_date=2024-01-31
Serviços mais solicitados

### GET /reports/mechanics-performance?start_date=2024-01-01&end_date=2024-01-31
Performance dos mecânicos

### GET /reports/low-stock?limit_quantity=10
Relatório de estoque baixo

### GET /reports/monthly-financial?year=2024&month=1
Relatório financeiro mensal

### GET /reports/dashboard
Estatísticas para dashboard

**Response Example:**
```json
{
    "status": "success",
    "data": {
        "today_sales": {
            "total_amount": 1250.00,
            "total_transactions": 8,
            "transactions": [...]
        },
        "top_products": [...],
        "top_services": [...],
        "low_stock": [...],
        "mechanics_performance": [...]
    }
}
```

---

## 📸 Upload de Imagens

### POST /upload/product-image
Upload de imagem de produto

**Form Data:**
- `image` - Arquivo de imagem (JPG, PNG, GIF, WebP, máx 5MB)

### POST /upload/user-avatar
Upload de avatar do usuário

**Form Data:**
- `avatar` - Arquivo de imagem (JPG, PNG, GIF, WebP, máx 5MB)

### POST /upload/system-banner
Upload de banner do sistema (apenas admin)

**Form Data:**
- `banner` - Arquivo de imagem (JPG, PNG, GIF, WebP, máx 5MB)

### POST /upload/system-logo
Upload de logo do sistema (apenas admin)

**Form Data:**
- `logo` - Arquivo de imagem (JPG, PNG, GIF, WebP, máx 5MB)

### DELETE /upload/{type}/{filename}
Deletar imagem

**Tipos:** products, avatars, banner

**Response Example:**
```json
{
    "status": "success",
    "message": "Imagem enviada com sucesso",
    "data": {
        "filename": "product_12345.jpg",
        "path": "uploads/products/product_12345.jpg",
        "size": 245760
    }
}
```

---

## 📁 Estrutura de Arquivos

```
backend/
├── api/
│   └── index.php           # Router principal
├── auth/
│   └── Auth.php            # Sistema de autenticação JWT
├── config/
│   └── db.php              # Configuração do banco
├── controllers/
│   ├── ProductController.php
│   ├── ServiceController.php
│   ├── InventoryController.php
│   ├── MechanicController.php
│   ├── UserController.php
│   ├── TransactionController.php
│   ├── SystemSettingsController.php
│   ├── ReportsController.php
│   └── ImageUploadController.php
└── models/
    ├── Product.php
    ├── Service.php
    ├── Inventory.php
    ├── Mechanic.php
    ├── User.php
    ├── Transaction.php
    ├── SystemSettings.php
    └── Reports.php
```

---

## 🚀 Funcionalidades Avançadas

### 1. Autenticação JWT
- Tokens com expiração de 24 horas
- Middleware de autenticação automático
- Diferentes níveis de acesso (admin/usuário)

### 2. Upload e Processamento de Imagens
- Redimensionamento automático
- Validação de tipos de arquivo
- Organização em diretórios
- Otimização para web

### 3. Relatórios Avançados
- Análise de vendas e serviços
- Performance de mecânicos
- Controle de estoque
- Estatísticas financeiras

### 4. Sistema de Configurações
- Personalização do sistema
- Upload de logo e banner
- Configurações flexíveis

### 5. Tratamento de Erros
- Respostas padronizadas
- Logs de erro
- Validação de dados

---

## 📋 Códigos de Status

- `200` - Sucesso
- `400` - Dados inválidos
- `401` - Não autorizado
- `404` - Não encontrado
- `405` - Método não permitido
- `500` - Erro interno

---

## 🔧 Configuração

1. Configure o banco de dados em `config/db.php`
2. Ajuste as permissões da pasta `uploads/`
3. Configure o arquivo `.htaccess` para rewrite URLs
4. Certifique-se que a extensão GD está habilitada para processamento de imagens

---

## 🧪 Testes

Use ferramentas como Postman ou Insomnia para testar os endpoints. Exemplos de cURL:

```bash
# Login
curl -X POST http://localhost/system/backend/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"123456"}'

# Listar produtos (com token)
curl -X GET http://localhost/system/backend/api/products \
  -H "Authorization: Bearer {seu_token}"

# Upload de imagem
curl -X POST http://localhost/system/backend/api/upload/product-image \
  -H "Authorization: Bearer {seu_token}" \
  -F "image=@produto.jpg"
```

Esta API fornece uma base completa e moderna para o sistema de oficina mecânica, com todas as funcionalidades necessárias para operação e gestão eficiente.
