-- Script de criação de tabelas para o Sistema de Controle de Horas de Mentoria
-- Banco de Dados: PostgreSQL

-- Remove tabelas existentes (se necessário) para uma nova instalação limpa
DROP TABLE IF EXISTS horas;
DROP TABLE IF EXISTS users;

-- Tabela de usuários (alunos e administradores)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    rm VARCHAR(50) UNIQUE NOT NULL, -- Registro de Matrícula
    senha_hash VARCHAR(255) NOT NULL,
    serie INTEGER NOT NULL CHECK (serie >= 0), -- 0 para admin, 1-3 para alunos
    ano_ingresso INTEGER,
    role VARCHAR(20) NOT NULL DEFAULT 'aluno' CHECK (role IN ('aluno', 'admin')),
    active BOOLEAN DEFAULT true, -- true = ativo, false = inativo/egresso
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Tabela para registrar as horas de atividades complementares
CREATE TABLE horas (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('Palestras', 'Visitas Técnicas', 'Mentoria', 'Eventos Científicos', 'Estágios', 'Outros')),
    data DATE NOT NULL,
    quantidade_horas NUMERIC(5, 2) NOT NULL CHECK (quantidade_horas > 0),
    bimestre INTEGER NOT NULL CHECK (bimestre BETWEEN 1 AND 4),
    ano INTEGER NOT NULL,
    descricao TEXT, -- Descrição adicional, especialmente para 'Outros'
    comprovante_path TEXT NULL, -- Caminho para o arquivo de comprovante (para tipo 'Outros')
    comprovante_filename TEXT NULL, -- Nome original do arquivo
    status VARCHAR(50) NOT NULL DEFAULT 'Pendente' CHECK (status IN ('Pendente', 'Aprovado', 'Rejeitado')),
    feedback_admin TEXT, -- Feedback do admin em caso de rejeição
    created_by_admin BOOLEAN DEFAULT false, -- true se o lançamento foi feito por um admin (ex: Estágios)
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Índices para otimizar consultas
CREATE INDEX idx_users_rm ON users(rm);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_active ON users(active);

CREATE INDEX idx_horas_user_id ON horas(user_id);
CREATE INDEX idx_horas_tipo ON horas(tipo);
CREATE INDEX idx_horas_ano ON horas(ano);
CREATE INDEX idx_horas_bimestre ON horas(bimestre);

-- Função para atualizar o campo 'updated_at' automaticamente
CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger para a tabela 'horas'
CREATE TRIGGER set_timestamp_horas
BEFORE UPDATE ON horas
FOR EACH ROW
EXECUTE PROCEDURE trigger_set_timestamp();


-- Comentários sobre as tabelas e colunas
COMMENT ON TABLE users IS 'Armazena informações sobre os usuários do sistema (alunos e administradores).';
COMMENT ON COLUMN users.rm IS 'Registro de Matrícula, usado como login.';
COMMENT ON COLUMN users.serie IS 'Série do aluno (1, 2, 3). 0 é usado para administradores.';
COMMENT ON COLUMN users.active IS 'Indica se a conta do usuário está ativa. Usado para a lógica de virada de ano.';

COMMENT ON TABLE horas IS 'Registros de horas de atividades complementares dos alunos.';
COMMENT ON COLUMN horas.user_id IS 'Chave estrangeira para a tabela de usuários.';
COMMENT ON COLUMN horas.comprovante_path IS 'Caminho do arquivo de comprovante armazenado no servidor (para o tipo Outros).';
COMMENT ON COLUMN horas.status IS 'Status da submissão da hora (Pendente, Aprovado, Rejeitado).';


