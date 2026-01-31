# Usage du JavaScript dans ce projet

## Principes

- **Vanilla JS par défaut** : pas de jQuery.
- **Pas de Stimulus** : uniquement si un besoin critique est identifié.
- **Webpack Encore** est la chaîne d'assets officielle.
- Le JavaScript applicatif est **factorisé** dans `assets/js/`.
- Les initialisations globales (theme, notifications, etc.) passent par des helpers réutilisables.

## Structure des fichiers

```
assets/js/
├── pages/
│   └── base_template.js # Entrée principale liée au template de base
├── components/          # Composants UI réutilisables
├── helpers/             # Utilitaires et fonctions partagées
└── tabulator/           # Tables avancées en vanilla (thème Flowbite)
```

## Entrée principale

Le point d'entrée unique est `assets/app.js` (Webpack Encore).  
Il charge `assets/js/pages/base_template.js`, qui appelle les helpers nécessaires.

## Règles d'utilisation

- **Écrire en vanilla JS** pour les interactions simples.
- **Factoriser** dans `assets/js/components` et `assets/js/helpers`.
- **Documenter** toute exception (bibliothèque imposée, dépendance legacy, etc.).

## Tabulator

- Utilisé pour les listings riches (pagination, tri multi, export).
- Initialisation centralisée via `assets/js/tabulator/init.js`.
