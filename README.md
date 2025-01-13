# worklab-simple

## Criação de um sistema em PHP do Worklab.

O sistema será dividido em três partes principais:

 - Backend (API): Gerencia as operações de CRUD para pacientes e exames, e vincula exames a pacientes.
 - Banco de Dados SQLite: Armazena os dados dos pacientes e exames, com um exemplo populado.
 - Frontend: Uma interface simples para interagir com a API.

Segue uma descrição detalhada do que será implementado:

### Estrutura do sistema

#### Banco de Dados SQLite

##### Tabela pacientes:

- id (chave primária)
- numero_atendimento (único, gerado aleatoriamente)
- nome_completo
- sexo
- email
- celular
  
##### Tabela exames:

- id (chave primária)
- codigo (único)
- descricao
- valor
  
##### Tabela paciente_exames:

- id (chave primária)
- paciente_id (chave estrangeira para pacientes)
- exame_id (chave estrangeira para exames)

#### Endpoints da API

##### 1. Cadastrar Paciente

POST /pacientes
 - Exemplo de entrada:
```json
{
  "nome_completo": "Sandro Silva Lima",
  "sexo": "M",
  "email": "sandrosilvalima6@gmail.com",
  "celular": "11944859367"
}
```

#### 2. Cadastrar Exame

POST /exames
 - Exemplo de entrada:
```json
{
  "codigo": "US",
  "descricao": "Ultrassom",
  "valor": 10.1
}
```
#### 3. Vincular Exames a um Paciente

POST /pacientes/{id}/exames
 - Exemplo de entrada:
```json
{
  "exames": ["HEMO", "GLI", "US"]
}
```
#### 4. Gerar Relatório de Paciente com Exames

GET /pacientes/{id}/relatorio
 - Exemplo de resposta:
```json
{
  "numero_atendimento": "102030",
  "nome_completo": "Jane Doe",
  "sexo": "F",
  "email": "jane.doe@example.com",
  "celular": "123456789",
  "exames": [
    {
      "codigo": "HEMO",
      "descricao": "Hemograma",
      "valor": "10.20"
    },
    {
      "codigo": "GLI",
      "descricao": "Glicose",
      "valor": "20.00"
    }
  ]
}
```
### Passos para Implementação

##### 1. Configuração do Banco de Dados

- Criação do banco sistema_saude.sqlite.
- Script SQL para inicializar as tabelas.
  
##### 2. Implementação da API

- Usaremos o framework Slim ou PHP nativo para simplicidade.
- Rotas para os endpoints definidos.
  
##### 3.Exemplo de Banco de Dados

- Inserir registros de pacientes e exames para teste.
  
##### 4.Documentação

Detalhar como rodar o sistema e testar os endpoints.


### Passos para Executar a Aplicação
##### 1. Requisitos

- PHP instalado (versão 7.4 ou superior).
- Composer instalado.
- Extensão SQLite habilitada no PHP.
Ambiente para rodar aplicações PHP (como Apache ou CLI).

##### 2. Configuração do Projeto

- Crie uma pasta para o projeto e coloque o código fornecido no arquivo index.php.
- Abra um terminal na pasta do projeto e inicialize o Composer:
```bash

composer require slim/slim:^4.0 slim/psr7
````
##### 3. Inicialize o Banco de Dados

- Inicie o servidor embutido do PHP:
````bash

php -S localhost:8080
````
- No navegador, acesse: http://localhost:8080/setup para criar as tabelas no banco de dados SQLite.
  
##### 4. Testar os Endpoints

- Utilize uma ferramenta como Postman ou cURL para testar os endpoints.
  
### Testes com cURL
##### 1. Cadastrar Paciente

```bash

curl -X POST http://localhost:8080/pacientes \
-H "Content-Type: application/json" \
-d '{
  "nome_completo": "Jane Doe",
  "sexo": "F",
  "email": "jane.doe@example.com",
  "celular": "123456789"
}'
```
##### 2. Cadastrar Exame

```bash
Copiar código
curl -X POST http://localhost:8080/exames \
-H "Content-Type: application/json" \
-d '{
  "codigo": "HEMO",
  "descricao": "Hemograma",
  "valor": 10.2
}'
```
##### 3. Vincular Exames a um Paciente

```bash

curl -X POST http://localhost:8080/pacientes/1/exames \
-H "Content-Type: application/json" \
-d '{
  "exames": ["HEMO"]
}'
```
##### 4. Gerar Relatório do Paciente

```bash

curl -X GET http://localhost:8080/pacientes/1/relatorio
```
### Resultados Esperados
Ao seguir os passos acima, você verá:

- Pacientes cadastrados com um `numero_atendimento` único.
- Exames vinculados a pacientes.
- Relatórios em JSON listando o paciente e seus exames.
