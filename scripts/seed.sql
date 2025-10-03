-- Script para popular o banco de dados com dados iniciais (seeding)
-- Cria um usuário administrador padrão.

-- A senha é 'Admin@123'. O hash foi gerado usando password_hash('Admin@123', PASSWORD_BCRYPT).
-- É FUNDAMENTAL que o administrador troque esta senha no primeiro acesso.

INSERT INTO users (nome, rm, senha_hash, serie, role, active)
VALUES (
    'Administrador',
    'admin',
    '$2y$10$2.5/b5i3v29L9VRIeD0hA.uIu0RzLp2j.iN.Yx/vj0k5CVxEa3w/e',
    0, -- Série 0 para administradores
    'admin',
    true
)
ON CONFLICT (rm) DO NOTHING; -- Não faz nada se o usuário 'admin' já existir

