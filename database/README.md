# Base de donnees

Ce dossier contient l'export MySQL du projet.

Fichier principal :

```text
scolar_sys.sql
```

Import :

```powershell
mysql -uroot < database\scolar_sys.sql
```

Le script recrée automatiquement la base `scolar_sys`. Il faut donc importer ce fichier depuis la racine du projet, sans selectionner une autre base.

La configuration de connexion est dans :

```text
app/config/database.php
```
