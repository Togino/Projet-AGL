# Tests API Backend

## Connexion

```http
POST /login
Content-Type: application/json

{
  "email": "admin@scolarsys.test",
  "motdepasse": "Admin1234!"
}
```

## Session courante

```http
GET /me
```

## Creation utilisateur

```http
POST /users
Content-Type: application/json
X-CSRF-Token: <csrf_token>

{
  "matricule": "GE-0002",
  "nom": "Traore",
  "prenom": "Awa",
  "date_de_naissance": "1998-03-10",
  "email": "awa.traore@test.local",
  "motdepasse": "Secure123",
  "role_id": 2,
  "statut": 1
}
```

## Permissions et roles

```http
GET /admin/roles
GET /admin/permissions
GET /admin/roles/2/permissions
```

```http
PUT /admin/roles/2/permissions
Content-Type: application/json
X-CSRF-Token: <csrf_token>

{
  "permission_ids": [1, 2, 3, 4, 5]
}
```

## Logs securite

```http
GET /admin/security-logs?limit=20
```

## Notes
- active le cookie jar si tu testes avec Postman
- recupere le `csrf_token` depuis `/login` ou `/me`
- envoie le cookie de session et le header `X-CSRF-Token` sur les routes protegees
