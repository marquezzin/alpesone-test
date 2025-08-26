<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

---

# 🚀 Configuração de Ambiente

Siga as etapas abaixo para configurar e rodar a aplicação corretamente:

### 1️⃣ Clonar e instalar dependências
- git clone https://github.com/marquezzin/alpesone-test.git
- cd https://github.com/marquezzin/alpesone-test.git
- npm install
- composer install
- php artisan key:generate

### 2️⃣ Configuração do `.env`
Crie um arquivo `.env` a partir do `.env.example` e configure as variáveis:

- Colocar suas credenciais do banco de dados  
- Colocar a minha URL da API :  
ALPESONE_EXPORT_URL="https://hub.alpes.one/api/v1/integrator/export/1902"

### 3️⃣ Rodar as migrações
Isso irá criar a estrutura do banco de dados no seu ambiente local
- php artisan migrate


### 4️⃣ Importar dados da API
Execute o comando para buscar o JSON da URL e inserir/atualizar os registros no banco:
- php artisan alpesone:fetch

### 5️⃣ Testes
Rodar testes locais com:
- php artisan test

Caso queira testar a api, inicie o servidor:
- composer run dev

Depois importe a collection do Postman localizada em:
postman/api.postman_collection.json
e realize os testes.

---

