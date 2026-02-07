# Boutons – Conventions UI

Ce document formalise l’usage des `variant` du composant bouton (`templates/components/_button.html.twig`)
pour assurer une lecture cohérente des actions dans l’interface.

## Règles générales

- 1 action principale par écran maximum → `primary`.
- Les actions de navigation (retour, annuler, voir tout) → `tertiary`.
- Les actions d’édition/modification → `warning`.
- Les actions destructrices (suppression) → `danger`.
- Les actions secondaires utiles mais non critiques → `secondary` ou `ghost`.

## Mapping recommandé

- `primary` : action principale (ex. “Créer”, “Ajouter”, “Enregistrer” si action dominante).
- `warning` : action d’édition / modification (ex. “Éditer”).
- `danger` : suppression / action irréversible (ex. “Supprimer”).
- `tertiary` : navigation / annulation / retour (ex. “Retour à la liste”, “Annuler”, “Voir tous”).
- `secondary` : action alternative de faible priorité (ex. action outil non critique).
- `ghost` : action très discrète (ex. filtres secondaires, “Voir plus”).
- `success` : confirmation ponctuelle (rare, éviter de multiplier les couleurs).
- `default` : par défaut, à éviter si un autre `variant` exprime mieux l’intention.

| Variant | Intention principale | Exemples |
| --- | --- | --- |
| `primary` | Action principale de l’écran | “Créer”, “Ajouter”, “Enregistrer” |
| `warning` | Modifier/éditer | “Éditer” |
| `danger` | Action destructrice | “Supprimer”, “Désactiver” |
| `tertiary` | Navigation / annulation | “Retour”, “Annuler”, “Voir tous” |
| `secondary` | Action alternative | “Exporter”, “Dupliquer” |
| `ghost` | Action discrète | “Voir plus”, filtres secondaires |
| `success` | Confirmation ponctuelle | “Valider” (rare) |
| `default` | Fallback | À éviter si possible |

## Exemples observés

- `warning` pour “Éditer” : `templates/admin/organizations/show.html.twig`
- `tertiary` pour “Retour à la liste” : `templates/admin/usagers/show.html.twig`
- `primary` pour “Ajouter un usager” : `templates/admin/usagers/index.html.twig`

## Taille

Le `size` doit rester cohérent par écran :

- `sm` dans les headers d’index et actions secondaires.
- `base` pour l’action principale d’un formulaire.
