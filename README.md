# Reservation Salle

## Présentation
Application de réservation de salles et d’administration (backoffice SPSL), avec gestion des sites, utilisateurs, journaux d’authentification et tâches planifiées.

## Stack technique
- **Backend** : Symfony 8 (PHP 8.4)
- **DB** : MySQL 8 (dev/prod), SQLite pour les tests
- **Front** : Twig + Tailwind 4 + Flowbite 4
- **Tooling** : Webpack Encore, PHPUnit

## Mise en route rapide

Quickstart (script) :
```
./scripts/init-dev.sh
```

Ce script suffit pour une première installation : il installe les dépendances (PHP + front), build les assets (dev), applique les migrations et charge les fixtures (purge DB). Il n’est donc pas nécessaire d’exécuter les étapes 1 à 5 si tu utilises ce script.

1) Préparer l’environnement
```
cp .env.example .env.local
```

2) Installer les dépendances (PHP + front)
```
composer install
nvm use
npm ci
```

Astuce : la commande `nvm use` force la version Node/NPM du projet (`.nvmrc`) pour éviter les réécritures de `package-lock.json`.

3) Configurer les variables clés (ex. `.env.local`)
```
APP_SECRET=change-me
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/reservation_salle?serverVersion=8.4.0&charset=utf8mb4"
MAILER_DSN="smtp://user:pass@localhost:1025"
MAILER_FROM="no-reply@example.com"
```

4) Migrations
```
php bin/console doctrine:migrations:migrate
```

5) Fixtures (⚠️ purge la base)
```
php bin/console doctrine:fixtures:load
```

Alternative rapide : `./scripts/fixtures-dev.sh` (fixtures + purge).

## Commandes utiles

### Tests
```
./vendor/bin/phpunit --testdox
```

### Qualité / Lint
Exécuter l’ensemble des checks (lint + static analysis + tests) :
```
composer qa
```

Checks individuellement :
```
composer lint:php
composer lint:twig
composer lint:phpstan
composer lint:cs
npm run lint:js
```

### Build assets
```
npm run watch
npm run build
```

### Cache
```
php bin/console cache:clear
```

## Documentation

Index centralisé : `docs/index.md`

### Tests
- Index : `docs/tests.md`
- Guide dev : `docs/tests/guide-debutant.md`
- Stratégie : `docs/tests/strategie.md`

### UI / Design
- Couleurs CD08 : `docs/interface/couleurs.md`
- Flowbite + Tailwind : `docs/interface/integration-flowbite-tailwind.md`
- Guidelines Flowbite + Tailwind : `docs/interface/recommandations-flowbite-tailwind.md`
- Icônes Flowbite : `docs/interface/icones.md`

### Dev / Process
- Contribuer : `CONTRIBUTING.md`
- Versioning : `docs/gestion-versions.md`
- Epics & user stories : `docs/epics-et-user-stories.md`
- Conventions : `docs/conventions.md`
- Glossaire métier : `docs/glossaire.md`
- Règles métier (Organisation, Site/Salle) : `docs/metier/regles-organisation-site-salle.md`
- Usage JavaScript : `docs/technique/usage-javascript.md`
- PHPMyAdmin : `docs/technique/phpmyadmin.md`
- Tâches CRON : `docs/cron.md`
- Stratégie tableaux : `docs/technique/tableaux/strategie-gestion-tableaux-symfony-flowbite.md`
- Guide dev tableaux : `docs/technique/tableaux/guide-implementation-tableaux.md`

### Sécurité / RGPD
- Journalisation RGPD : `docs/securite/rgpd-journalisation.md`
- Politique mots de passe : `docs/securite/politique-mots-de-passe.md`
- En-têtes de sécurité : `docs/securite/en-tetes-securite.md`
