# Sistema de Controle de Horas de Mentoria — Etec Jales

Este é um sistema web para controle e gerenciamento de horas de atividades complementares (mentorias, palestras, estágios, etc.) para os alunos da Etec Jales.

## Stack de Tecnologia

*   **Front-end:** HTML5, CSS3 (com [Bootstrap 5 via CDN](https://getbootstrap.com/))
*   **Back-end:** PHP 8.x
*   **Banco de Dados:** PostgreSQL
*   **Servidor:** PHP Built-in Web Server (para desenvolvimento)

## Funcionalidades

### Alunos
- Cadastro e Login.
- Lançamento de horas de atividades (Palestras, Visitas Técnicas, Mentoria, Eventos Científicos, Outros).
- Upload de comprovante para a categoria "Outros".
- Visualização de um relatório pessoal de horas lançadas, com filtros.

### Administradores
- CRUD completo de usuários (Alunos e outros Administradores).
- CRUD completo de lançamentos de horas de todos os alunos.
- Cadastro de horas de "Estágio" para os alunos.
- Visualização e download dos comprovantes enviados.
- Geração de relatórios completos com filtros avançados (por aluno, data, tipo, etc.).
- Exportação de relatórios para o formato CSV.

---

## Instalação e Execução

Siga os passos abaixo para configurar o ambiente de desenvolvimento local.

### 1. Pré-requisitos

- PHP 8.0 ou superior
- PostgreSQL
- [Composer](https://getcomposer.org/) (opcional, mas recomendado para autoload)

### 2. Clonar o Repositório

```bash
git clone <url-do-repositorio>
cd Mentoria-System
```

### 3. Configurar o Banco de Dados

1.  Acesse seu servidor PostgreSQL.
2.  Crie um novo banco de dados. O nome padrão é `etec_jales`, mas pode ser alterado no arquivo `.env`.

    ```sql
    CREATE DATABASE etec_jales;
    ```

3.  Execute o script de migração para criar as tabelas e os índices necessários.

    ```bash
    # Navegue até a pasta do projeto e execute:
    psql -h 192.168.1.60 -U default -d postgres -f scripts/migrate.sql
    ```

### 4. Configurar Variáveis de Ambiente

1.  Copie o arquivo de exemplo `.env.example` para um novo arquivo chamado `.env`.

    ```bash
    copy .env.example .env
    ```

2.  Abra o arquivo `.env` e atualize as credenciais do banco de dados (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) e outras configurações conforme necessário.

### 5. Instalar Dependências (Opcional)

Se o projeto utilizar `composer` para autoload, instale as dependências:

```bash
composer install
```

### 6. Popular o Banco de Dados (Seed)

Execute o script `seed.sql` para criar um usuário administrador padrão.

```bash
psql -h 192.168.1.60 -U default -d postgres -f scripts/seed.sql
```

- **Usuário:** `admin`
- **Senha:** `Admin@123`

**IMPORTANTE:** Altere a senha do administrador no primeiro login!

### 7. Iniciar o Servidor Local

Use o servidor embutido do PHP para iniciar a aplicação. O parâmetro `-t public` define o diretório `public` como a raiz do servidor, o que é uma prática de segurança importante.

```bash
php -S localhost:8000 -t public
```

Acesse [http://localhost:8000](http://localhost:8000) no seu navegador.

---

## Estrutura do Projeto

```
/project-root
├── /public                 # Raiz do servidor web
│   ├── index.php           # Roteador principal
│   ├── ...                 # Outros scripts (login, register, dashboards)
│   └── /assets             # Arquivos públicos (CSS, JS)
├── /src                    # Código-fonte da aplicação
│   ├── /Controllers        # Lógica de negócio e controle de fluxo
│   ├── /Models             # Interação com o banco de dados
│   ├── /Views              # Templates HTML (componentes)
│   └── bootstrap.php       # Autoload e inicialização
├── /uploads                # Arquivos de upload (protegido)
│   └── /comprovantes
├── /scripts                # Scripts de banco de dados e CLI
│   ├── migrate.sql
│   ├── seed.sql
│   └── rollover_year.php
├── .env                    # Variáveis de ambiente (NÃO versionar)
├── .env.example            # Exemplo de .env
├── composer.json           # Dependências PHP
└── README.md               # Este arquivo
```

---

## Scripts e Manutenção

### Virada de Ano (Year Rollover)

O sistema inclui um script para automatizar a progressão de série dos alunos no final do ano letivo.

- **Execução Manual:**

  ```bash
  php scripts/rollover_year.php
  ```

- **Simulação (Dry Run):** Para ver o que seria alterado sem modificar o banco de dados.

  ```bash
  php scripts/rollover_year.php --dry-run
  ```

- **Agendamento (Cron Job):** Para automatizar a execução, adicione a seguinte linha ao seu crontab (Linux/macOS). Isso executará o script todo dia 1º de janeiro às 02:00.

  ```
  0 2 1 1 * /usr/bin/php /path/to/your/project/scripts/rollover_year.php
  ```
  *(No Windows, use o Agendador de Tarefas.)*

#### Comportamento da Virada de Ano

O comportamento para alunos do 3º ano é configurável no arquivo `.env` através da variável `YEAR_ROLLOVER_BEHAVIOR`.
- `deactivate`: O aluno é marcado como inativo (`active = false`). Este é o padrão.
- `graduate`: (Funcionalidade futura) O aluno poderia ser movido para uma tabela de "egressos" ou ter um status de "graduado".

---

## Testando a API com cURL

Aqui estão alguns exemplos de como testar os endpoints principais.

**1. Login**
```bash
curl -X POST http://localhost:8000/login.php \
     -c cookie.txt \
     -d "rm=admin&senha=Admin@123"
```

**2. Lançar Hora (Aluno)**
```bash
curl -X POST http://localhost:8000/aluno_dashboard.php?action=create_hora \
     -b cookie.txt \
     -d "tipo=Palestras&data=2025-10-05&quantidade_horas=2&bimestre=4"
```

**3. Listar Horas (Admin)**
```bash
curl -X GET http://localhost:8000/admin_dashboard.php?view=horas \
     -b cookie.txt
```

**4. Deletar Usuário (Admin)**
```bash
curl -X POST http://localhost:8000/admin_dashboard.php?action=delete_user \
     -b cookie.txt \
     -d "user_id=ID_DO_USUARIO"
```

---

## Segurança

- **Senhas:** Armazenadas com `password_hash()` (Bcrypt).
- **SQL Injection:** Prevenção com `Prepared Statements` (PDO).
- **CSRF:** Implementação de tokens anti-CSRF em todos os formulários.
- **File Uploads:** Validação de tipo (MIME), extensão e tamanho. Os arquivos são armazenados fora da raiz pública e servidos via script PHP para garantir a autorização.
- **Controle de Acesso:** Middleware simples verifica a sessão e o `role` do usuário para proteger rotas de alunos e administradores.

## Melhorias Futuras

- Migrar para um framework MVC completo (ex: Laravel, Symfony) para melhor organização.
- Implementar um ORM (ex: Eloquent, Doctrine) para abstrair o acesso ao banco de dados.
- Adotar um sistema de roteamento mais robusto.
- Criar um sistema de logs de auditoria para ações críticas.
- Desenvolver testes automatizados (unitários e de integração).
