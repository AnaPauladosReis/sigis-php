-- Tarefa 1.4 do plano de trabalho: limitar tentativas de login.
CREATE TABLE login_tentativas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_tentativas_email (email),
  INDEX idx_login_tentativas_ip (ip)
) ENGINE=InnoDB;
