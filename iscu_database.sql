-- ============================================================
--  iscu_db  –  Schéma Social Recruiting
--  Tables : campaigns | booking_requests | questionnaire_responses
--           contact_messages | admin_users
--  Version : 2.0  –  repositionnement Social Recruiting
-- ============================================================

CREATE DATABASE IF NOT EXISTS iscu_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE iscu_db;

-- ============================================================
-- 1. CAMPAIGNS  –  Campagnes Social Recruiting par client
-- ============================================================
CREATE TABLE IF NOT EXISTS campaigns (
  id               INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Client
  company_name     VARCHAR(150) NOT NULL,
  contact_name     VARCHAR(150) DEFAULT NULL,
  email            VARCHAR(150) DEFAULT NULL,
  phone            VARCHAR(50)  DEFAULT NULL,

  -- Poste à pourvoir
  position_title   VARCHAR(200) NOT NULL,
  location         VARCHAR(100) DEFAULT NULL,
  industry         VARCHAR(100) DEFAULT NULL,   -- Handwerk | IT | Pflege | Gastronomie | Industrie | Andere

  -- Plateformes actives (valeurs séparées par virgule ou JSON)
  platforms        VARCHAR(255) DEFAULT NULL,   -- Instagram,TikTok,Facebook,LinkedIn

  -- Statut et budget
  status           ENUM('draft','active','paused','completed') NOT NULL DEFAULT 'draft',
  budget_chf       DECIMAL(10,2) DEFAULT NULL,
  start_date       DATE         DEFAULT NULL,
  end_date         DATE         DEFAULT NULL,

  -- Métriques (mis à jour par l'admin)
  impressions      INT(11)      UNSIGNED DEFAULT 0,
  clicks           INT(11)      UNSIGNED DEFAULT 0,
  applications     INT(11)      UNSIGNED DEFAULT 0,

  notes            TEXT         DEFAULT NULL,
  created_at       TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  updated_at       TIMESTAMP    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),

  PRIMARY KEY (id),
  INDEX idx_status (status),
  INDEX idx_company (company_name),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de démo
INSERT INTO campaigns (company_name, contact_name, email, position_title, location, industry, platforms, status, budget_chf, start_date, end_date, impressions, clicks, applications) VALUES
('Müller Metallbau AG',   'Stefan Müller',   'stefan@mueller-metallbau.ch', 'Polymechaniker EFZ (m/w)',        'Zürich',  'Handwerk',    'Instagram,Facebook,TikTok',          'active',    2500.00, '2025-03-01', '2025-03-31', 84200, 1230, 18),
('Spital Biel',           'Anna Gerber',     'a.gerber@spital-biel.ch',    'Pflegefachperson HF (m/w)',       'Biel',    'Pflege',       'Instagram,Facebook,LinkedIn',         'active',    3200.00, '2025-03-10', '2025-04-10', 61500,  980, 12),
('TechStart GmbH',        'Marc Leuthold',   'marc@techstart.ch',          'Full-Stack Developer (m/w)',       'Bern',    'IT',           'LinkedIn,Instagram',                  'completed', 1800.00, '2025-01-15', '2025-02-15', 42000,  860, 22),
('Gastronomie Luzern AG', 'Rita Kälin',      'rita@gastronomie-luzern.ch', 'Chef de Cuisine (m/w)',           'Luzern',  'Gastronomie', 'Instagram,TikTok,Facebook',          'paused',    1500.00, '2025-02-20', '2025-03-20', 38900,  540,  7),
('Elektro Suter & Co.',   'Beat Suter',      'beat@suter-elektro.ch',      'Elektroinstallateur EFZ (m/w)',   'Basel',   'Handwerk',    'Instagram,Facebook',                  'draft',     2000.00, '2025-04-01', '2025-04-30',     0,    0,  0);

-- ============================================================
-- 2. BOOKING_REQUESTS  –  Demandes de rendez-vous découverte
-- ============================================================
CREATE TABLE IF NOT EXISTS booking_requests (
  id               INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,

  name             VARCHAR(150) NOT NULL,
  company          VARCHAR(150) DEFAULT NULL,
  email            VARCHAR(150) NOT NULL,
  phone            VARCHAR(50)  DEFAULT NULL,

  -- Contexte
  positions_count  TINYINT      UNSIGNED DEFAULT NULL,   -- Nombre de postes à pourvoir
  industry         VARCHAR(100) DEFAULT NULL,
  message          TEXT         DEFAULT NULL,

  -- Statut du suivi
  status           ENUM('new','contacted','qualified','lost') NOT NULL DEFAULT 'new',
  preferred_slot   VARCHAR(100) DEFAULT NULL,            -- créneau souhaité (texte libre ou depuis Calendly)
  booked_at        TIMESTAMP    NULL DEFAULT NULL,       -- date du rendez-vous confirmé

  created_at       TIMESTAMP    NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (id),
  INDEX idx_status (status),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. QUESTIONNAIRE_RESPONSES  –  Formulaire subscribe.html
--    Profil d'entreprise cherchant à recruter via Social Media
-- ============================================================
CREATE TABLE IF NOT EXISTS questionnaire_responses (
  id               INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Identité
  company_name     VARCHAR(150) DEFAULT NULL,
  contact_name     VARCHAR(150) DEFAULT NULL,
  email            VARCHAR(150) DEFAULT NULL,
  phone            VARCHAR(50)  DEFAULT NULL,

  -- Besoins en recrutement
  industry         VARCHAR(100) DEFAULT NULL,   -- Branche
  positions_count  TINYINT      UNSIGNED DEFAULT NULL,   -- Combien de postes
  positions_detail TEXT         DEFAULT NULL,            -- Description des postes

  -- Plateformes d'intérêt
  platforms        VARCHAR(255) DEFAULT NULL,   -- Instagram,TikTok,Facebook,LinkedIn

  -- Budget mensuel estimé
  budget_range     VARCHAR(50)  DEFAULT NULL,   -- <1000 | 1000-2500 | 2500-5000 | >5000

  -- Timing
  urgency          VARCHAR(50)  DEFAULT NULL,   -- sofort | 1-3 Monate | 3-6 Monate | kein Zeitdruck

  message          TEXT         DEFAULT NULL,
  created_at       TIMESTAMP    NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (id),
  INDEX idx_email (email),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. CONTACT_MESSAGES  –  Formulaire contact index.html
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(150) NOT NULL,
  company    VARCHAR(150) DEFAULT NULL,
  email      VARCHAR(150) NOT NULL,
  message    TEXT         NOT NULL,
  status     ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP    NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (id),
  INDEX idx_status (status),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ADMIN_USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS admin_users (
  id           INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
  username     VARCHAR(80)  NOT NULL UNIQUE,
  email        VARCHAR(150) NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,   -- bcrypt hash
  role         ENUM('superadmin','admin','viewer') NOT NULL DEFAULT 'admin',
  created_at   TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  last_login   TIMESTAMP    NULL DEFAULT NULL,

  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin par défaut  →  mot de passe : Admin1234!  (à changer !)
INSERT INTO admin_users (username, email, password, role) VALUES
('admin', 'admin@iscu.ch', '$2y$12$Dz5NqV5lHk9r8.Fv.3cFzeKdOy5B7oq/8f2WNWqBJqQv4tVtJ/Lui', 'superadmin');
