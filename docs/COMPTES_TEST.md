# Comptes de test

Apres import de `database/scolar_sys.sql`, utiliser ces comptes.

## Administrateur

```text
Email : admin@scolarsys.test
Mot de passe : admin123
```
## Autres roles
Les comptes gestionnaire, enseignant et etudiant utilisent tous :
```text
Mot de passe : 123
```
Exemples :
```text
Gestionnaire : gestionnaire1@gmail.com / 123
Enseignant   : enseignant1@demo.local / 123
Etudiant     : etudiantild1@demo.local / 123
```

La connexion accepte aussi le matricule a la place de l'email, par exemple :

```text
AD-0001 / admin123
GE-0001 / 123
ES-0002 / 123
ET-0002 / 123
```

Redirections attendues apres connexion :

```text
AD-0001 -> admin/dashboard.php
GE-0001 -> gestionnaire/dashboard.php
ES-0002 -> enseignant/dashboard.php
ET-0002 -> etudiant/dashboard.php
```
