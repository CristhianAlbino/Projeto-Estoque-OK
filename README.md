# AutoPeças — Catálogo e Controle de Estoque

Projeto original descompactado de `Projeto-Estoque-main.rar`, organizado e corrigido. Aplicação educacional em **PHP 8.1+**, **MySQL 8+** (ou MariaDB compatível), HTML, CSS e JavaScript, sem dependências de Node ou frameworks.

## Funcionalidades implementadas

- Cadastro e login com validação no servidor, sessão e senhas criptografadas via `password_hash`.
- Usuários comuns consultam o catálogo; somente funcionários conseguem cadastrar peças e ajustar quantidades.
- Pesquisa por nome/modelo, filtro de categoria e consulta de disponibilidade.
- Entrada e saída de estoque com operação atômica e bloqueio de saldo negativo.
- Seleção local (carrinho demonstrativo) guardada no navegador.
- Layout responsivo e arquivos organizados para manutenção.

**Limites atuais:** a seleção não gera pedidos reais e o projeto ainda não oferece pagamento, recuperação de senha, confirmação por e-mail, histórico/auditoria de movimentações nem proteção contra tentativas repetidas de login. **Não exponha em produção sem adicionar esses controles.**

## Instalação local

1. Tenha PHP 8.1+ com extensões `pdo_mysql` e `session` e um servidor MySQL/MariaDB.
2. Importe `database/schema.sql` no MySQL. Exemplo em terminal:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
3. Copie `includes/config.example.php` para `includes/config.local.php` e coloque as credenciais do seu banco. **Não versione esse arquivo!**
4. Inicie a aplicação a partir da raiz do projeto:
   ```bash
   php -S localhost:8000
   ```
5. Abra `http://localhost:8000`. Cadastre um usuário na página de login.

### Habilitar um funcionário

Por segurança, **o cadastro público sempre cria um usuário comum**. Depois de cadastrar a conta, um administrador de banco pode conceder acesso ao estoque:

```sql
UPDATE estoque.usuarios
SET tipo = 'funcionario'
WHERE email = 'funcionario@exemplo.com';
```

Saia e entre novamente para atualizar a sessão. Acesse `/estoque.php` para cadastrar produtos e registrar quantidades.

## Estrutura

```text
├── api/                 # Autenticação, sair e alterações de estoque
├── assets/
│   ├── css/app.css      # Estilos responsivos
│   ├── img/             # Imagens do projeto original
│   └── js/app.js        # Seleção demonstrativa no navegador
├── database/schema.sql  # Esquema não destrutivo
├── includes/            # Sessão, conexão PDO, navegação
├── index.php            # Página inicial
├── login.php            # Entrar/cadastrar
├── shopping.php         # Catálogo
├── estoque.php          # Área restrita
└── archive/             # Arquivo RAR original, mantido para referência
```

## Testes

O workflow GitHub Actions verifica sintaxe dos arquivos PHP e JavaScript. Um teste de ponta a ponta precisa de MySQL configurado; a execução da interface e do cadastro deve ser validada no ambiente local.

## Segurança e desenvolvimento

- Consultas ao banco utilizam instruções preparadas do PDO.
- Formulários que alteram estado exigem token CSRF.
- Sessões são renovadas após login; senhas não são guardadas em texto puro.
- `config.local.php` está no `.gitignore`.
- Para produção faltam HTTPS obrigatório, rate limiting, logs de auditoria e verificação de e-mail, entre outros recursos.
- O SQL antigo tinha chaves estrangeiras inválidas e `DROP DATABASE`; ele foi substituído por um esquema consistente e não destrutivo. Migração de dados de bancos preexistentes precisa ser planejada separadamente.

O código anterior foi mantido no arquivo `archive/Projeto-Estoque-main.rar` para rastreabilidade.
