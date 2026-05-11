# EduSystème - Projet AGL

Application web PHP/MySQL de gestion scolaire.

## Fonctionnalites principales

- Connexion par role : administrateur, gestionnaire, enseignant, etudiant.
- Gestion des etudiants, enseignants, classes, modules et semestres.
- Affectation des enseignants aux modules d'une classe.
- Gestion de l'emploi du temps par classe.
- Saisie des notes par module et par semestre.
- Calcul automatique :
  - note de classe = `(devoir 1 + devoir 2 + devoir 3) / 3`
  - note finale = `(note de classe + note examen * 2) / 3`
  - moyenne semestrielle affichee seulement quand toutes les notes finales du semestre existent.
- Reclamations etudiantes avec traitement par les gestionnaires.
- Centre d'attente et archive pour les actions sensibles.

## Structure rapide

- `admin/` : espace administrateur.
- `gestionnaire/` : espace gestionnaire.
- `enseignant/` : espace enseignant.
- `etudiant/` : espace etudiant.
- `app/config/` : configuration de la base de donnees.
- `app/helpers/` : fonctions partagees.
- `app/includes/` : menus, topbar et fragments partages.
- `public/` : pages publiques, CSS et JavaScript.
- `database/` : export SQL de la base.
- `docs/` : documentation de remise.

## Installation rapide

1. Copier le dossier dans `C:\xampp\htdocs\Projet-AGL`.
2. Importer `database/scolar_sys_dump.sql` dans MySQL.
3. Verifier la configuration dans `app/config/database.php`.
4. Lancer Apache/MySQL avec XAMPP, ou le serveur PHP local.
5. Ouvrir `http://localhost/Projet-AGL/public/login.php`.

## Comptes

Pour les comptes `ENSEIGNANT`, `GESTIONNAIRE` et `ETUDIANT`, le mot de passe est :

```text
123
```

Voir `docs/COMPTES_TEST.md` pour plus de details.
