# Linea Labs — Sistema de Catálogo e Gestão

Sistema web para gerenciamento e exibição de catálogo de produtos em MDF personalizado, com painel administrativo completo.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.x (PDO, OOP) |
| Banco de dados | MySQL 8.x |
| Frontend | Bootstrap 5.3, JavaScript vanilla |
| Imagens | Conversão automática para WebP |
| Segurança | CSRF, sessão HTTPS-only, bcrypt |

## Funcionalidades

**Site público**
- Página inicial com animações
- Página sobre a empresa
- Catálogo com busca por texto, filtro por categoria e faixa de preço
- Paginação no catálogo
- Modal de detalhes do produto com carrossel de imagens
- Botão direto para orçamento via WhatsApp

**Painel administrativo** (`/admin`)
- Login seguro com proteção CSRF e timeout de sessão
- Dashboard com abas: Produtos e Orçamentos
- CRUD completo de produtos (nome, descrição, categoria, dimensões, preço, status)
- Upload de múltiplas imagens por produto com conversão automática para WebP
- Calculadora de orçamentos (área, tempo de máquina, custo de material, markup)
- Listagem e exclusão de orçamentos

## Estrutura do projeto

```
linealabs/
├── db/                          # Schemas SQL e migrations
│   ├── linea2_admins.sql
│   ├── linea2_produtos.sql
│   ├── linea2_produto_imagens.sql
│   └── migration_refactoring.sql
├── private/                     # Fora do webroot — não versionado
│   ├── config.php               # Constantes da aplicação e conexão PDO
│   ├── .env                     # Credenciais do banco (ver abaixo)
│   └── uploads/products/        # Imagens WebP geradas no upload
└── public_html/                 # Webroot
    ├── index.php                # Página inicial
    ├── sobre.php                # Página sobre
    ├── admin/                   # Painel administrativo
    │   ├── login.php
    │   ├── logout.php
    │   ├── index.php            # Dashboard
    │   ├── api/products.php     # Endpoint AJAX (listagem de produtos)
    │   ├── products/            # create · edit · delete · status · toggle
    │   └── quotes/              # delete
    ├── app/
    │   ├── Repository/          # ProductRepository · ImageRepository
    │   │                        # QuoteRepository · ConfigRepository
    │   └── Service/             # Auth · Image (WebP)
    ├── catalog/index.php        # Catálogo público
    ├── css/                     # Estilos globais e do admin
    ├── js/                      # Scripts do site e do dashboard
    ├── media/image.php          # Servidor seguro de imagens
    └── templates/partials/      # footer.php
```

## Instalação

### Pré-requisitos

- PHP >= 8.0 com extensões: `pdo_mysql`, `gd` (ou `imagick`)
- MySQL >= 8.0
- Servidor web (Apache/Nginx) apontando o webroot para `public_html/`

### Passos

**1. Clone o repositório**
```bash
git clone <url-do-repositorio>
cd linealabs
```

**2. Crie o banco de dados e importe os schemas**
```sql
CREATE DATABASE linea2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
```bash
mysql -u root -p linea2 < db/linea2_admins.sql
mysql -u root -p linea2 < db/linea2_produtos.sql
mysql -u root -p linea2 < db/linea2_produto_imagens.sql
```

**3. Configure o ambiente**

Crie o arquivo `private/.env` com base no exemplo:
```ini
DB_HOST=127.0.0.1
DB_NAME=linea2
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_CHARSET=utf8mb4
```

**4. Configure as constantes da aplicação**

Edite `private/config.php` e ajuste os dados da empresa e o timeout da sessão admin:
```php
define('APP_version', '1.0.0');
define('EMP_NOME_FANTASIA', 'Sua Empresa');
define('EMP_WHATSAPP', '5511999999999');   // DDD + número, sem espaços
define('ADMIN_SESSION_TIMEOUT', 600);      // segundos de inatividade
```

**5. Crie o primeiro usuário admin**

Insira diretamente no banco com senha hasheada (bcrypt):
```php
// Gere o hash em um script PHP temporário:
echo password_hash('sua_senha', PASSWORD_BCRYPT);
```
```sql
INSERT INTO admins (nome, email, senha)
VALUES ('Seu Nome', 'admin@exemplo.com', '$2y$12$...');
```

**6. Permissões de escrita** (Linux/macOS)
```bash
chmod 755 private/uploads/products
```

## Segurança

- Sessões com cookies `HttpOnly`, `Secure`, `SameSite=Strict`
- Token CSRF em todos os formulários POST (incluindo login)
- `session_regenerate_id(true)` na autenticação
- Senhas com `password_hash` / `password_verify` (bcrypt)
- Comparação de tokens com `hash_equals` (resistente a timing attack)
- Imagens servidas via proxy (`media/image.php`) com validação de MIME e nome
- `private/` fora do webroot; `.env` e `uploads/` no `.gitignore`
- Todas as queries via PDO com `bindValue` (sem interpolação de SQL)

## Autor

Eduardo Godoy
