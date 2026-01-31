# Contribuer au projet

Merci de contribuer à ce projet ! Voici un guide rapide pour garder une base propre et cohérente.

## Workflow

1. Créer une branche depuis `main`/`master`.
2. Effectuer des commits petits et ciblés.
3. Ouvrir une PR et vérifier les checks CI.

## Conventions de commit

Le projet utilise la convention **Conventional Commits**.

Exemples :
- `feat: ajout de la page de logs`
- `fix: corrige la validation du formulaire`
- `chore: maj dépendances`

## Qualité locale

Avant d’ouvrir une PR :

```bash
./vendor/bin/php-cs-fixer fix
./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=512M
./vendor/bin/phpunit --testdox
npm run lint:js
```

## Scripts utiles

- Init rapide : `./scripts/init-dev.sh`
- Tests : `./vendor/bin/phpunit --testdox`
- Lint Twig : `php bin/console lint:twig templates --env=prod`

## Notes

- Les secrets ne doivent jamais être commités (`.env.local`).
- Les migrations doivent rester propres (idéalement regroupées pour l’initialisation).
