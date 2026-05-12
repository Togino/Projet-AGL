# Installation

## Pre-requis

- XAMPP avec Apache, PHP et MySQL.
- Une base MySQL locale accessible avec l'utilisateur `root` sans mot de passe, ou adapter `app/config/database.php`.

## Etapes

1. Placer le projet dans :

```text
C:\xampp\htdocs\Projet-AGL
```

2. Demarrer MySQL avec XAMPP.

3. Importer la base :

```powershell
mysql -uroot < database\scolar_sys.sql
```

4. Verifier la configuration :

```php
$host = "localhost";
$dbname = "scolar_sys";
$username = "root";
$password = "";
```

5. Ouvrir l'application :

```text
http://localhost/Projet-AGL/public/login.php
```

## Serveur PHP local

Il est aussi possible de lancer :

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t C:\xampp\htdocs\Projet-AGL
```

Puis ouvrir :

```text
http://127.0.0.1:8000/public/login.php
```
