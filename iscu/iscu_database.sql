-- ============================================================
--  iscu_db  –  Schéma complet
--  Tables : jobs | questionnaire_responses | contact_messages | admin_users
-- ============================================================

CREATE DATABASE IF NOT EXISTS iscu_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE iscu_db;

-- ------------------------------------------------------------
-- 1. JOBS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS jobs (
  id          INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(150) NOT NULL,
  location    VARCHAR(100) DEFAULT NULL,
  job_type    VARCHAR(50)  DEFAULT NULL,   -- Vollzeit / Teilzeit
  description TEXT         DEFAULT NULL,
  posted_at   TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Données de démo
INSERT INTO jobs (title, location, job_type, description) VALUES
('Projektleiter Bau (m/w)',          'Zürich', 'Vollzeit', 'Leitung von Bauprojekten im Hochbau.'),
('Servicetechniker Industrie (m/w)', 'Basel',  'Teilzeit', 'Wartung und Reparatur von Industrieanlagen.');

-- ------------------------------------------------------------
-- 2. QUESTIONNAIRE_RESPONSES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS questionnaire_responses (
  id         INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
  role       VARCHAR(100) DEFAULT NULL,
  goal       VARCHAR(150) DEFAULT NULL,
  message    TEXT         DEFAULT NULL,
  firstname  VARCHAR(100) DEFAULT NULL,
  lastname   VARCHAR(100) DEFAULT NULL,
  email      VARCHAR(150) DEFAULT NULL,
  phone      VARCHAR(50)  DEFAULT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 3. CONTACT_MESSAGES  (formulaire contact index.html)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(150) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  message    TEXT         NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 4. ADMIN_USERS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id           INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
  username     VARCHAR(80)  NOT NULL UNIQUE,
  email        VARCHAR(150) NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,   -- bcrypt hash
  created_at   TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  last_login   TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Admin par défaut  →  mot de passe : Admin1234!  (à changer !)
-- Hash bcrypt généré avec password_hash('Admin1234!', PASSWORD_BCRYPT)
INSERT INTO admin_users (username, email, password) VALUES
('admin', 'admin@iscu.ch', '$2y$12$Dz5NqV5lHk9r8.Fv.3cFzeKdOy5B7oq/8f2WNWqBJqQv4tVtJ/Lui');
