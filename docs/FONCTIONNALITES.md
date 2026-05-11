# Fonctionnalites

## Gestion pedagogique

- Classes et details de classe.
- Semestres par classe.
- Modules rattaches aux semestres.
- Affectations des enseignants aux modules des classes.
- Emploi du temps par classe.

## Notes

La saisie se fait par module du semestre.

Champs saisis :

- Devoir 1
- Devoir 2
- Devoir 3
- Note examen

Calculs :

```text
note_classe = (devoir_1 + devoir_2 + devoir_3) / 3
note_finale = (note_classe + note_examen * 2) / 3
```

Regles :

- La note de classe n'est calculee que si les trois devoirs sont saisis.
- La note finale n'est calculee que si la note de classe et la note examen existent.
- La moyenne generale d'un semestre n'est affichee que lorsque toutes les notes finales des modules du semestre existent.

## Reclamations

- L'etudiant cree une reclamation simple : sujet + message.
- Le gestionnaire consulte les reclamations.
- Le gestionnaire approuve ou rejette une reclamation.
- L'etudiant voit le statut dans `Mes reclamations`.

## Roles

- Administrateur : gestion globale.
- Gestionnaire : gestion pedagogique quotidienne.
- Enseignant : classes et saisie des notes.
- Etudiant : consultation des notes, emploi du temps, reclamations.
