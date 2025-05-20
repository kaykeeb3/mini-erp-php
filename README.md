# 📦 Mini ERP em PHP (MVC + MySQL)

Este projeto é um mini ERP desenvolvido em **PHP puro**, utilizando o padrão **MVC**. Ele oferece funcionalidades de gestão de produtos, variações de estoque, cupons de desconto, carrinho de compras, pedidos e integração via webhook.

## 📦 Funcionalidades

- Cadastro e listagem de produtos com variações de tamanho/cor e controle de estoque
- Aplicação de cupons de desconto (valor fixo ou percentual)
- Carrinho de compras com cálculo de frete baseado no subtotal
- Finalização de pedido com simulação de checkout
- Webhook para atualização de status do pedido
- Notificações por e-mail para confirmação de pedidos e atualizações de status

---

## 🚀 Como rodar o projeto

1. Clone o repositório:
   ```bash
   git clone https://github.com/kaykeeb3/mini-erp-php.git
   cd mini-erp-php
   ```

2. Instale as dependências do projeto usando o Composer:
   ```bash
   composer install
   ```

3. Configure seu banco de dados e credenciais de e-mail no arquivo `.env`:

   ```
   DB_HOST=localhost
   DB_NAME=mini_erp
   DB_USER=root
   DB_PASS=

   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=seu_username_mailtrap
   MAIL_PASSWORD=sua_senha_mailtrap
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@mini-erp.test
   MAIL_FROM_NAME="Mini ERP"
   ```

4. Execute o script SQL para criar as tabelas (em `database/schema.sql`).

5. Inicie o servidor embutido do PHP:

   ```bash
   php -S localhost:8000 -t public
   ```

6. Acesse o sistema no navegador ou teste via Postman/Insomnia:

   ```
   http://localhost:8000
   ```

---

## 📧 Configuração do Mailtrap para Testes de E-mail

O sistema utiliza o serviço [Mailtrap.io](https://mailtrap.io) para testes de envio de e-mail. Para utilizar esta funcionalidade:

1. Crie uma conta gratuita no [Mailtrap.io](https://mailtrap.io)
2. Acesse sua caixa de entrada de demonstração (Demo inbox)
3. Copie as credenciais SMTP fornecidas para seu arquivo `.env`:
   - `MAIL_USERNAME`
   - `MAIL_PASSWORD`

**Importante**: Sem a configuração correta das credenciais do Mailtrap, você não receberá as notificações por e-mail enviadas pelo sistema, como confirmações de pedidos e atualizações de status.

---

## 📬 Rotas da API e Exemplos

---

### 🔹 Criar Produto

**POST** `/api/products`

**Body:**

```json
{
  "name": "Camiseta Estampada",
  "price": 49.90,
  "variations": [
    { "name": "Preta - M", "stock": 25 },
    { "name": "Branca - G", "stock": 12 },
    { "name": "Cinza - P", "stock": 30 }
  ]
}
```

**Resposta:**

```json
{
  "message": "Product created",
  "id": "8"
}
```

---

### 🔹 Listar Produtos

**GET** `/api/products`

**Resposta:**

```json
[
  {
    "id": 2,
    "name": "Jaqueta Corta-Vento",
    "price": "129.90",
    "variations": [
      { "name": "Verde Militar - M", "stock": 2 },
      { "name": "Preta - G", "stock": 7 }
    ]
  }
]
```

---

### 🔹 Ver Produto por ID

**GET** `/api/products/2`

**Resposta:**

```json
{
  "id": 2,
  "name": "Jaqueta Corta-Vento",
  "price": "129.90",
  "variations": [
    { "name": "Verde Militar - M", "stock": 3 },
    { "name": "Preta - G", "stock": 7 }
  ]
}
```

---

### 🔹 Criar Cupom

**POST** `/api/coupons`

**Body:**

```json
{
  "code": "DESCONTO101",
  "type": "percentage",
  "value": 10,
  "min_amount": 100,
  "expires_at": "2025-12-31 23:59:59",
  "active": 1
}
```

**Resposta:**

```json
{
  "message": "Cupom criado com sucesso"
}
```

---

### 🔹 Validar Cupom

**POST** `/api/coupons/validate`

**Body:**

```json
{
  "code": "DESCONTO10",
  "subtotal": 150
}
```

**Resposta:**

```json
{
  "code": "DESCONTO10",
  "type": "percentage",
  "value": "10.00",
  "discount": 15
}
```

---

### 🔹 Listar Cupons

**GET** `/api/coupons`

**Resposta:**

```json
[
  {
    "id": 3,
    "code": "DESCONTO10",
    "type": "percentage",
    "value": "10.00",
    "min_amount": "100.00",
    "expires_at": "2025-12-31 23:59:59",
    "active": 1
  }
]
```

---

### 🔹 Adicionar Produto ao Carrinho

**POST** `/api/cart/add`

**Body:**

```json
{
  "product": 2,
  "variation": "Verde Militar - M",
  "quantity": 1
}
```

**Resposta:**

```json
{
  "message": "Produto adicionado ao carrinho",
  "cart": {
    "2-Verde Militar - M": {
      "product_id": 2,
      "variation": "Verde Militar - M",
      "quantity": 1,
      "name": "Jaqueta Corta-Vento",
      "price": "129.90"
    }
  }
}
```

---

### 🔹 Ver Carrinho

**GET** `/api/cart`

**Resposta com itens:**

```json
{
  "items": [
    { "product": 3, "variation": "Mescla - P", "quantity": 1, "price": 89.9 },
    { "product": 2, "variation": "Verde Militar - M", "quantity": 1, "price": 129.9 }
  ],
  "subtotal": 219.8,
  "coupon": null,
  "shipping": 0,
  "total": 219.8
}
```

**Resposta com carrinho vazio:**

```json
{
  "items": [],
  "subtotal": 0,
  "coupon": null,
  "shipping": 15,
  "total": 15
}
```

---

### 🔹 Carrinho Falso para Teste

**GET** `/api/cart/fake`

**Resposta:**

```json
{
  "message": "Carrinho preenchido para testes"
}
```

---

### 🔹 Finalizar Pedido

**POST** `/api/order/checkout`
**Tipo:** `multipart/form-data`

**Campos:**

* `cep`: 62900025
* `email`: teste@gmail.com
* `coupon`: PROMO20 *(opcional)*

**Resposta:**

```json
{
  "message": "Pedido realizado com sucesso",
  "order_id": 19,
  "total": 175.84
}
```

---

### 🔹 Webhook de Pedido

**POST** `/api/webhook/receive`

**Body:**

```json
{
  "order_id": 19,
  "status": "shipped"
}
```

**Resposta:**

```json
{
  "message": "Status do pedido #19 atualizado para 'shipped'"
}
```

*Ou:*

```json
{
  "message": "Status do pedido #19 atualizado para 'cancelled'"
}
```

---

## 🧪 Testes

Você pode utilizar ferramentas como [Postman](https://www.postman.com/) ou [Insomnia](https://insomnia.rest/) para testar as rotas da API com os exemplos acima.

---

## 📂 Estrutura do Projeto

```
mini-erp/
├── app/
│   ├── Controllers/
│   │   ├── CartController.php
│   │   ├── CouponController.php
│   │   ├── OrderController.php
│   │   ├── ProductController.php
│   │   └── WebhookController.php
│   └── Models/
│       ├── Coupon.php
│       ├── Order.php
│       ├── Product.php
│       ├── Stock.php
│       └── Variation.php
├── core/
│   ├── Database.php
│   └── Router.php
├── database/
│   └── schema.sql
├── public/
│   └── index.php
├── routes/
│   └── web.php
├── Services/
│   └── EmailService.php
├── vendor/
├── .env
├── .env.example
├── .gitignore
├── .htaccess
├── composer.json
└── composer.lock
```