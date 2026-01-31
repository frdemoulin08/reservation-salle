# Stratégie de tests

## Objectifs

- Détecter les régressions sur les parcours critiques.
- Garantir la cohérence entre validation front et back.
- Sécuriser les tâches planifiées et la journalisation.

## Couverture minimale retenue

### 1) Sécurité
- Accès à l’admin protégé
- Login valide / invalide

### 2) CRUD admin
- Index pages (Sites, Users, Logs)
- Création / édition des entités majeures

### 3) Commandes
- Exécution des tâches CRON avec journalisation

## Types de tests

### Fonctionnels sans DB
- Scénarios simples : pages publiques, login, redirections
- Rapides, pas de fixtures

### Fonctionnels avec DB
- CRUD, validation backend, pagination, commandes
- Basés sur SQLite + fixtures

## Principes

- Priorité aux tests fonctionnels (comportement réel)
- Unit tests uniquement quand la logique est pure et isolable
- Tests écrits après bugfix pour éviter les régressions

## Évolution

À mesure que le projet grandit :
- Ajouter des tests d’édition/suppression pour les nouvelles entités
- Couvrir les scénarios d’erreur métier
- Vérifier les permissions par rôle

