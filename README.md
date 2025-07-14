![Gosat](https://www.gosat.org/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2024/09/gosat_marca_toposite.png.webp)


# Sistema de Consulta e Solicitação de Empréstimos
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/license/mit/) [![PHP Version](https://img.shields.io/badge/php-8.x-blue.svg)](https://www.php.net/) [![Vue.js Version](https://img.shields.io/badge/vue-3.x-blue.svg)](https://vuejs.org/) [![Docker](https://img.shields.io/badge/docker-enabled-blue.svg)](https://www.docker.com/) [![MySQL Version](https://img.shields.io/badge/mysql-5.7-blue.svg)](https://www.mysql.com/)

---

## Teste o projeto online

Você pode testar o projeto Gosat online através do seguinte link: 
- Frontend: [Consulta de ofertas gosat](http://192.109.11.77:7000/)
- Backend: [Gosat API](http://192.109.11.77:7001/)
- Documentação da API: [Swagger UI](http://192.109.11.77:7001/docs)

## Descrição

O projeto Gosat é uma aplicação fullstack para consulta de CPF e ofertas financeiras, além de permitir o registro e gerenciamento de solicitações de empréstimos.

Ele é composto por um backend em PHP (Lumen), um frontend em Vue 3 e um banco de dados MySQL. Toda a infraestrutura pode ser facilmente executada via Docker.

---

## Tecnologias Utilizadas

- **Backend:** PHP 8.x com Lumen Framework
- **Frontend:** Vue.js 3 com Vite
- **Gerenciamento de Dependências:** Composer (PHP) e NPM (Node.js)
- **Autenticação:** JWT (JSON Web Tokens)
- **Validação de Dados:** Laravel Validation
- **Testes:** PHPUnit (backend), Jest (frontend)
- **Banco de Dados:** MySQL 5.7
- **Containerização:** Docker e Docker Compose
- **Documentação da API:** OpenAPI (Swagger)
- **HTTP Client:** GuzzleHttp (PHP)
- **Bibliotecas auxiliares:** Bootstrap 5, Chart.JS, SweetAlert e Axios

---

## Arquitetura do Projeto

├── backend/ # Código PHP (Lumen)
├── frontend/ # Código Vue.js
├── docker-compose.yml # Configuração dos containers Docker
└── README.md # Este arquivo

---

## Detalhes do Frontend

- Desenvolvido em Vue.js 3 com Bootstrap 5 para estilos e componentes.
- Comunicação via API REST com o backend, através do proxy configurado no Vite, ``/api`` e Axios.
- Exibe ofertas financeiras, permite seleção e envio de solicitação.
- Utiliza SweetAlert2 para diálogos amigáveis.
- Atualização dinâmica dos dados e gráficos com Chart.js.
- O frontend inclui suporte a tema claro e escuro para melhor experiência do usuário.

---

## Detalhes Backend

- PHP com Lumen Framework para APIs REST.
- Validações robustas dos dados de entrada.
- Integração com APIs externas para consulta de CPF e ofertas.
- Modelagem e persistência de solicitações no banco MySQL.
- Respostas padronizadas em JSON para facilitar consumo no frontend.

## Setup com Docker

O projeto está configurado para rodar via Docker com três serviços:

- **MySQL:** Container com MySQL 5.7, exposto na porta 3307 local.
- **Backend:** Servidor PHP Lumen rodando na porta 7001.
- **Frontend:** Servidor de desenvolvimento Vue rodando na porta 7000.

### Como iniciar

```bash
docker-compose up --build
```

Aguarde o build e inicialização dos containers. A aplicação estará disponível em:

Frontend: http://localhost:7000
Backend API: http://localhost:7001

Ou no IP fornecido via terminal após o comando `docker-compose up` (depende de cada Setup).

**Importante:** Cerifique-se de ter conferido as variáveis de ambiente no arquivo `.env` do backend, especialmente as relacionadas ao banco de dados.

## API

### Base URL

``http://localhost:7001``

### Endpoints principais

| Método | Endpoint                      | Descrição                                                      |
| ------- | ----------------------------- | ---------------------------------------------------------------- |
| GET     | `/`                         | Status da API / versão                                          |
| GET     | `/consultarCpf/{cpf}`       | Consulta CPF (valida e busca dados externos)                     |
| POST    | `/consultarOfertas`         | Consulta ofertas financeiras via CPF, instituição e modalidade |
| POST    | `/solicitarEmprestimo`      | Registrar nova solicitação de empréstimo                      |
| GET     | `/solicitacoesPorCpf/{cpf}` | Listar solicitações feitas para um CPF                         |
| DELETE  | `/solicitacoes/{id}`        | Remover solicitação pelo ID                                    |

### Detalhes importantes:

- **Consulta CPF:** valida o CPF antes de chamar a API externa.
- **Consulta Ofertas:** requer um JSON com `cpf`, `instituicao_id` e `codModalidade`.
- **Registrar Solicitação:** espera payload com dados da solicitação (`cpf`, `instituição`, `modalidade`, `valor`, `juros`, `parcelas`).
- **Listar/Remover Solicitações:** permitem gerenciar as solicitações associadas a um CPF.

### Documentação da API (Swagger / OAS)

A API está documentada seguindo o padrão OpenAPI 3.0.

A documentação inclui:

- Descrição dos endpoints, parâmetros, requestBody e respostas.
- Exemplos de sucesso e erros.
- Status HTTP esperados para cada rota.

Você pode importar o arquivo JSON do Swagger para ferramentas como Swagger UI ou Insomnia para visualizar e testar a API interativamente. O arquivo Swagger está localizado em `backend/public/docs/swagger.json`.

Veja a documentação completa em: [Swagger UI](http://localhost:7001/docs) (após iniciar o backend).
