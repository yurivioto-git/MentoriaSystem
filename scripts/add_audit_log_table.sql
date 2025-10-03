-- Script para adicionar a tabela de log de auditoria
-- Banco de Dados: PostgreSQL

CREATE TABLE audit_log (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Índices para otimizar consultas na tabela de auditoria
CREATE INDEX idx_audit_log_user_id ON audit_log(user_id);
CREATE INDEX idx_audit_log_action ON audit_log(action);
CREATE INDEX idx_audit_log_created_at ON audit_log(created_at);

-- Comentários sobre a tabela e colunas
COMMENT ON TABLE audit_log IS 'Registra eventos críticos de auditoria no sistema.';
COMMENT ON COLUMN audit_log.user_id IS 'O usuário que realizou a ação. Nulo se a ação não for atrelada a um usuário (ex: falha de login).';
COMMENT ON COLUMN audit_log.action IS 'A ação que foi realizada (ex: login_success, create_user, delete_hora).';
COMMENT ON COLUMN audit_log.details IS 'Detalhes adicionais sobre a ação, como o ID do recurso afetado.';
COMMENT ON COLUMN audit_log.ip_address IS 'Endereço IP de onde a requisição se originou.';
COMMENT ON COLUMN audit_log.user_agent IS 'User agent do cliente que fez a requisição.';

