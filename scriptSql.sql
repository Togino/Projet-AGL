CREATE DATABASE IF NOT EXISTS scolar_sys CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scolar_sys;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE utilisateur(
    MAT VARCHAR(10),
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_de_naissance DATE NOT NULL,
    email VARCHAR(80) UNIQUE,
    motdepasse VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    statut BOOLEAN DEFAULT TRUE,
    deleted_at DATETIME NULL,
    created_by VARCHAR(10) NULL,
    updated_by VARCHAR(10) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_utilisateur PRIMARY KEY(MAT),
    CONSTRAINT ck_MAT CHECK (
        MAT LIKE 'AD-%' OR
        MAT LIKE 'GE-%' OR
        MAT LIKE 'ES-%' OR
        MAT LIKE 'ET-%'
    ),
    CONSTRAINT fk_utilisateur_role FOREIGN KEY(role_id) REFERENCES roles(id)
);

CREATE TABLE security_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    mat_user VARCHAR(10) NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_security_logs_user FOREIGN KEY(mat_user) REFERENCES utilisateur(MAT) ON DELETE SET NULL
);

CREATE TABLE backup_jobs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    payload JSON NOT NULL,
    status ENUM('pending', 'processed', 'failed') NOT NULL DEFAULT 'pending',
    scheduled_for DATETIME NOT NULL,
    processed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE PA(
    MAT VARCHAR(10),
    post VARCHAR(60) NOT NULL,
    CONSTRAINT pk_PA PRIMARY KEY(MAT),
    CONSTRAINT ck_post CHECK (
        post IN ('ADMIN', 'ENSEIGNANT', 'GESTIONNAIRE')
    ),
    CONSTRAINT fk_PA_utilisateur FOREIGN KEY(MAT) REFERENCES utilisateur(MAT) ON DELETE CASCADE
);

CREATE TABLE classe (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    niveau VARCHAR(20)
);

CREATE TABLE etudiant(
    MAT VARCHAR(10),
    classe_id INT NOT NULL,
    annee_etude YEAR NOT NULL,
    CONSTRAINT pk_etudiant PRIMARY KEY(MAT),
    CONSTRAINT fk_etudiant_classe FOREIGN KEY (classe_id) REFERENCES classe(ID),
    CONSTRAINT fk_etudiant_utilisateur FOREIGN KEY(MAT) REFERENCES utilisateur(MAT) ON DELETE CASCADE
);

CREATE TABLE inscription (
    MAT VARCHAR(10),
    annee YEAR,
    PRIMARY KEY(MAT, annee),
    CONSTRAINT fk_inscription_etudiant FOREIGN KEY (MAT) REFERENCES etudiant(MAT) ON DELETE CASCADE
);

CREATE TABLE module (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE enseigner (
    MAT_PA VARCHAR(10) NOT NULL,
    module_id INT NOT NULL,
    PRIMARY KEY (MAT_PA, module_id),
    CONSTRAINT fk_enseigner_pa FOREIGN KEY (MAT_PA) REFERENCES PA(MAT) ON DELETE CASCADE,
    CONSTRAINT fk_enseigner_module FOREIGN KEY (module_id) REFERENCES module(ID) ON DELETE CASCADE
);

CREATE TABLE note (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    MAT_ET VARCHAR(10) NOT NULL,
    module_id INT NOT NULL,
    valeur DECIMAL(4,2) NOT NULL,
    poids INT CHECK (poids BETWEEN 0 AND 100),
    penalite TINYINT,
    CONSTRAINT ck_valeur CHECK (valeur BETWEEN 0 AND 20),
    CONSTRAINT fk_note_module FOREIGN KEY (module_id) REFERENCES module(ID),
    CONSTRAINT fk_note_etudiant FOREIGN KEY(MAT_ET) REFERENCES etudiant(MAT) ON DELETE CASCADE
);

CREATE TABLE gerer(
    ID_note INT NOT NULL,
    MAT_PA VARCHAR(10) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_gerer PRIMARY KEY(ID_note, MAT_PA),
    CONSTRAINT fk_gere_PA FOREIGN KEY(MAT_PA) REFERENCES PA(MAT),
    CONSTRAINT fk_gerer_notes FOREIGN KEY(ID_note) REFERENCES note(ID) ON DELETE CASCADE
);

INSERT INTO roles (name, description) VALUES
('SUPER_ADMIN', 'Gestionnaire principal avec tous les droits'),
('ADMIN', 'Administrateur fonctionnel'),
('ENSEIGNANT', 'Personnel enseignant'),
('GESTIONNAIRE', 'Gestionnaire scolaire'),
('ETUDIANT', 'Compte etudiant');

INSERT INTO permissions (code, description) VALUES
('dashboard.view', 'Acceder au tableau de bord'),
('users.create', 'Creer un utilisateur'),
('users.read', 'Consulter les utilisateurs'),²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²²
('users.update', 'Modifier un utilisateur'),
('users.delete', 'Supprimer logiquement un utilisateur'),
('roles.manage', 'Gerer les roles et permissions'),
('security.logs.read', 'Consulter les journaux de securite');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.name = 'SUPER_ADMIN';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.name = 'ADMIN'
  AND p.code IN ('dashboard.view', 'users.create', 'users.read', 'users.update', 'users.delete');

INSERT INTO utilisateur (
    MAT, nom, prenom, date_de_naissance, email, motdepasse, role_id, statut
)
SELECT
    'AD-0001',
    'Super',
    'Admin',
    '1990-01-01',
    'admin@scolarsys.test',
    '$2y$12$TReM05U1G29YCnDPpqwOfOw8AURXkVWN5b9FSP.pIR2cT8Ts0R7cm',
    r.id,
    TRUE
FROM roles r
WHERE r.name = 'SUPER_ADMIN';

INSERT INTO PA (MAT, post) VALUES
('AD-0001', 'ADMIN');
