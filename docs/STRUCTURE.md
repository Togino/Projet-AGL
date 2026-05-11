# Structure du projet

Le projet garde une structure simple par espace utilisateur.

## Dossiers metier

Chaque espace garde seulement son `dashboard.php` a la racine. Les autres pages sont rangees par module.

### Admin

- `admin/utilisateurs/` : administrateurs, gestionnaires et comptes utilisateurs.
- `admin/etudiants/` : liste, ajout, modification et fiche etudiant.
- `admin/enseignants/` : liste, ajout, modification et suppression enseignant.
- `admin/classes/` : classes, details, ajout, modification et suppression.
- `admin/modules/` : modules.
- `admin/affectations/` : affectations professeur/module/classe.
- `admin/pedagogie/` : notes et emploi du temps.
- `admin/workflow/` : alertes, archive, centre d'attente et logs.
- `admin/compte/` : profil et parametres.

### Gestionnaire

- `gestionnaire/etudiants/` : etudiants, inscriptions, fiche, modification et suppression.
- `gestionnaire/enseignants/` : enseignants, modification et suppression.
- `gestionnaire/classes/` : classes, details, ajout, modification et suppression.
- `gestionnaire/affectations/` : affectations professeur/module/classe.
- `gestionnaire/pedagogie/` : notes et emploi du temps.
- `gestionnaire/reclamations/` : traitement des reclamations etudiantes.
- `gestionnaire/workflow/` : alertes, archive et centre d'attente.
- `gestionnaire/compte/` : profil.

### Enseignant

- `enseignant/academique/` : classes et notes.
- `enseignant/compte/` : profil.

### Etudiant

- `etudiant/etudes/` : notes et emploi du temps.
- `etudiant/reclamations/` : creation et suivi des reclamations.
- `etudiant/compte/` : profil.

## Dossiers techniques

- `app/config/database.php` centralise la connexion PDO.
- `app/helpers/functions.php` contient les fonctions communes : auth, redirections, icones, notifications.
- `app/helpers/grades.php` contient la logique des notes et moyennes.
- `app/helpers/reclamations.php` contient la logique des reclamations.
- `app/includes/` contient les sidebars et le topbar partages.
- `public/assets/css/style.css` contient le style principal.
- `public/assets/js/app.js` contient le JavaScript public.

## Base de donnees

- `database/scolar_sys_dump.sql` est l'export MySQL a importer pour reproduire l'environnement.

## Note importante

Les menus partages acceptent maintenant un prefixe de navigation pour fonctionner depuis les sous-dossiers.
