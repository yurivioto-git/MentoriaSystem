-- Tabela para armazenar os cursos
CREATE TABLE cursos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Tabela de junção para associar usuários a cursos (relação muitos-para-muitos)
-- Um usuário pode ser coordenador de um curso e aluno de outro, por exemplo.
CREATE TABLE user_cursos (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    curso_id INTEGER NOT NULL REFERENCES cursos(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, curso_id)
);

-- Adicionar uma coluna 'is_superadmin' na tabela 'users'
-- para diferenciar o administrador geral dos coordenadores de curso.
ALTER TABLE users ADD COLUMN is_superadmin BOOLEAN DEFAULT false;

-- Adicionar o papel 'coordenador' na tabela 'users'
ALTER TABLE users DROP CONSTRAINT users_role_check;
ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('aluno', 'admin', 'coordenador'));
