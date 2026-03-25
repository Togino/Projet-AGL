CREATE DATABASE scolar_sys;
use scolar_sys;

-- une date de naissance a été rajouter dans la table utilisateur

CREATE table utilisateur(
    MAT VARCHAR(10),
    nom VARCHAR (50)not null not null,
    prenom VARCHAR(50) not null,
    date_de_naissance date not null,
    email VARCHAR(30),
    statut boolean default 1, -- true pour un compte actif et faulse pour l'invers
    constraint ck_MAT check (MAT like AD-% or MAT like GE-% or MAT like ES-% or MAT like ET-%)

)
CREATE TABLE PA(
    MAT VARCHAR(10),
    post VARCHAR(60)not null,

    constraint ck_post check (post in ('ADMIN','ENSEIGNENT','GESTIONNAIRE')),
    constraint pk_PA primary key(MAT),
    constraint fk_PA_utilisateur foreign key(MAT)
);

CREATE TABLE etudiant(
    MAT varchar(10),
    annee_etude year not null,
    filiere VARCHAR(20) not null,
    niveau VARCHAR(20) not null,

    constraint pk_etudiant primary key(MAT),
    constraint fk_etudiant_utilisateur foreign key(MAT)
);

-- le poids désigne la valeur d'une note par raport aux autres notes du modul
-- ex: les notes d'exame valent plus que les notes de devoir avec un poid de 60
-- donc 60% de la note final

CREATE TABLE note (
    ID INT AUTO_INCREMENT,
    MAT VARCHAR(10),
    module VARCHAR(30)not null,
    valeur decimal(4, 2) not null,
    poids INT, 
    penalite INT(2),

    constraint ck_valeur check (valeur between 0 and 20),
    constraint pk_note primary key(ID),
    constraint fk_note_etudiant foreign key(MAT) references(etudiant)
);
CREATE TABLE gerer(
    ID_note int,
    MAT_PA VARCHAR(10),
    date_creation timestamp default current_timestamp,

    constraint fk_gere_PA foreign key(MAT_PA) references(personnel_admin),
    constraint fk_gerer_notes foreign key(ID_note) references(note),
    constraint pk_gerer primary key(ID,MAT_PA)
);

