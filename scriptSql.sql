CREATE DATABASE scolar_sys;
USE scolar_sys;

CREATE TABLE utilisateur(
    MAT VARCHAR(10),
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_de_naissance DATE NOT NULL,
    email VARCHAR(30) UNIQUE,
    motdepasse VARCHAR(255) not null,
    statut BOOLEAN DEFAULT TRUE, --designe si le compte est actif ou pas 

    CONSTRAINT pk_utilisateur PRIMARY KEY(MAT),

    CONSTRAINT ck_MAT CHECK (
        MAT LIKE 'AD-%' OR 
        MAT LIKE 'GE-%' OR 
        MAT LIKE 'ES-%' OR 
        MAT LIKE 'ET-%'
    )
);

CREATE TABLE PA(
    MAT VARCHAR(10),
    post VARCHAR(60) NOT NULL,

    CONSTRAINT pk_PA PRIMARY KEY(MAT),

    CONSTRAINT ck_post CHECK (
        post IN ('ADMIN','ENSEIGNENT','GESTIONNAIRE')
    ),

    CONSTRAINT fk_PA_utilisateur 
    FOREIGN KEY(MAT) REFERENCES utilisateur(MAT) ON DELETE CASCADE
);


CREATE TABLE classe (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    niveau VARCHAR(20)
);


CREATE TABLE etudiant(
    MAT VARCHAR(10),
    classe_id INT not null,
    annee_etude YEAR NOT NULL,

    CONSTRAINT pk_etudiant PRIMARY KEY(MAT),

    FOREIGN KEY (classe_id) REFERENCES classe(ID),

    CONSTRAINT fk_etudiant_utilisateur 
    FOREIGN KEY(MAT) REFERENCES utilisateur(MAT)
    ON DELETE CASCADE
);

CREATE TABLE inscription (
    MAT VARCHAR(10),
    annee YEAR,

    PRIMARY KEY(MAT, annee),

    FOREIGN KEY (MAT) REFERENCES etudiant(MAT) ON DELETE CASCADE
);


CREATE TABLE module (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE enseigner (
    MAT_PA VARCHAR(10) not null,
    module_id INT not null,

    PRIMARY KEY (MAT_PA, module_id),

    FOREIGN KEY (MAT_PA) REFERENCES PA(MAT) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES module(ID) ON DELETE CASCADE
);


CREATE TABLE note (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    MAT_ET VARCHAR(10) not null,
    module_id int NOT NULL,
    valeur DECIMAL(4,2) NOT NULL,
    poids INT CHECK (poids BETWEEN 0 AND 100), 
    penalite TINYINT,


    CONSTRAINT ck_valeur CHECK (valeur BETWEEN 0 AND 20),
    CONSTRAINT fk_note_module
    FOREIGN KEY (module_id) REFERENCES module(ID),

    CONSTRAINT fk_note_etudiant 
    FOREIGN KEY(MAT_ET) REFERENCES etudiant(MAT) ON DELETE CASCADE
);

CREATE TABLE gerer(
    ID_note INT not null,
    MAT_PA VARCHAR(10) not null,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_gerer PRIMARY KEY(ID_note, MAT_PA),

    CONSTRAINT fk_gere_PA 
    FOREIGN KEY(MAT_PA) REFERENCES PA(MAT),

    CONSTRAINT fk_gerer_notes 
    FOREIGN KEY(ID_note) REFERENCES note(ID)
    ON DELETE CASCADE
);