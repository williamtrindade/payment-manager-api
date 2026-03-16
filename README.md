# 🚀 Multi-Gateway Payment System

Este projeto é um orquestrador de pagamentos que garante a continuidade da venda através de um sistema de Failover.

## 🛠️ Padrões e Arquitetura Pensados

* Strategy Pattern: Cada gateway possui seu próprio Provider, isolando regras de payload, endpoints e headers customizados.
* Failover Automático: O sistema percorre os gateways ativos por ordem de prioridade até obter sucesso, garantindo a conversão da venda.
* RBAC (Role-Based Access Control): Controle de acesso granular (ADMIN, FINANCE, MANAGER, OPERATOR) via Middleware customizado.
* Data Transfer Objects (DTO): Uso de CardDTO para garantir tráfego tipado e seguro de dados sensíveis de cartões.
* Resiliência de Token: Lógica de retentativa que detecta `jwt expired` no Gateway 1.
* Uso de cache para salvar o token entregue pelo gateway 1.

---
## ⚙️ Instalação e Execução (Docker)

### 1. Clonar o Repositório
> git clone git@github.com:williamtrindade/payment-manager-api.git  

> cd payment-manager-api

### 2. Configuração de Ambiente
Crie o arquivo .env e configure as credenciais para autenticar nos mocks:
cp .env.example .env

### 3. Subir os Containers
Suba a aplicação junto com os mocks dos gateways:
docker-compose up -d --build

### 4. Instalar Dependências e Configurar Banco
Execute os comandos dentro do container da aplicação para preparar o projeto:
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed

*O Seeder configurará os usuários de teste e os gateways dinamicamente com base no seu .env.*

---

## 🛣️ Detalhamento de Rotas

| Coleção / Recurso | Método | Endpoint | Ação                                        |
| :--- | :---: | :--- |:--------------------------------------------|
| **Auth** | `POST` | `/api/login` | Autenticação e geração de token             |
| **Checkout** | `POST` | `/api/buy` | Processa uma nova compra (Failover ativo)   |
| **Product** | `GET` | `/api/products` | Lista todos os produtos                     |
| | `GET` | `/api/products/{id}` | Exibe os detalhes de um produto             |
| | `POST` | `/api/products` | Cria um novo produto                        |
| | `PUT` | `/api/products/{id}` | Atualiza um produto existente               |
| | `DELETE` | `/api/products/{id}` | Remove um produto                           |
| **Transactions**| `GET` | `/api/transactions` | Lista o histórico de transações             |
| | `GET` | `/api/transactions/{id}` | Exibe os detalhes de uma transação          |
| | `POST` | `/api/transactions/{id}/refund` | Solicita o reembolso de uma transação       |
| **Gateway** | `PATCH` | `/api/gateways/{id}/toggle` | Ativa ou desativa um gateway                |
| | `PATCH` | `/api/gateways/{id}/priority` | Altera a ordem de prioridade (failover)     |
| **User** | `GET` | `/api/users` | Lista os usuários administrativos           |
| | `GET` | `/api/users/{id}` | Exibe os detalhes de um usuário             |
| | `POST` | `/api/users` | Cria um novo usuário                        |
| | `PUT` | `/api/users/{id}` | Atualiza os dados de um usuário             |
| | `DELETE` | `/api/users/{id}` | Remove um usuário                           |
| **Clients** | `GET` | `/api/clients` | Lista os clientes que já compraram          |
| | `GET` | `/api/clients/{id}` | Exibe os detalhes do cliente e suas compras |

---

## 🔌 Como adicionar um novo Gateway?

O sistema foi desenhado respeitando o princípio Open/Closed (SOLID). Para adicionar o "Gateway 3":

1. Criar o Provider: Crie app/Gateways/Gateway3Provider.php implementando a PaymentGatewayInterface.
2. Configurar Payload: Dentro da classe, defina se o gateway usa Headers customizados ou Bearer Token, e mapeie o JSON.
3. Registrar no Service: No PaymentService.php, adicione o novo gateway no método getProvider().
4. Adicionar ao Banco: Insira uma linha na tabela gateways.
5. Resultado: O sistema de Failover agora incluirá o Gateway 3 automaticamente no loop de tentativas.

---

## 🧪 Usuários de Teste (Padrão)
* Admin: admin@betalent.tech / password
* Finance: finance@betalent.tech / password
