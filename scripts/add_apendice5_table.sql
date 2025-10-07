CREATE TYPE status_apendice5 AS ENUM ('pendente', 'aprovado', 'rejeitado');

CREATE TABLE IF NOT EXISTS apendice5_submissions (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL,
  bimestre_ref VARCHAR(10) NOT NULL, -- Referência ao bimestre, ex: 2024-1
  file_path VARCHAR(255) NOT NULL,
  original_filename VARCHAR(255) NOT NULL,
  status status_apendice5 NOT NULL DEFAULT 'pendente',
  submission_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  validation_date TIMESTAMP NULL DEFAULT NULL,
  admin_notes TEXT DEFAULT NULL,
  CONSTRAINT apendice5_submissions_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_user_id_apendice5 ON apendice5_submissions (user_id);