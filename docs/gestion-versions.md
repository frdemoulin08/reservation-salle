# Versioning

Ce projet utilise `semantic-release` pour gérer les versions à partir de Conventional Commits.

## Calcul des versions

- `fix:` incrémente la version patch (ex. 1.2.3 -> 1.2.4)
- `feat:` incrémente la version mineure (ex. 1.2.3 -> 1.3.0)
- `feat!:` ou `fix!:` incrémente la version majeure (ex. 1.2.3 -> 2.0.0)
- `BREAKING CHANGE:` dans le corps du commit déclenche aussi une version majeure

## Format des messages de commit

Utilise le format Conventional Commits :

```
<type>(scope optionnel) : <description>

[corps optionnel]

[footer optionnel]
```

Types courants :
- `feat:` pour les nouvelles fonctionnalités
- `fix:` pour les corrections de bugs
- `chore:` pour la maintenance
- `docs:` pour la documentation
- `refactor:` pour les refactorings
- `test:` pour les tests

Exemples :
- `feat: add room availability endpoint`
- `fix: prevent double booking`
- `feat!: remove legacy booking flow`
- `docs: document release process`

## Processus de release

Les releases sont automatisées par GitHub Actions sur la branche `main`.

Workflow :
- Push des commits sur `main`
- GitHub Actions exécute `semantic-release`
- Création d’un tag Git et d’une release GitHub
- `CHANGELOG.md` est mis à jour et committé par le job de release

## Exécution locale

Tu peux lancer la release en local (utile pour un dry-run) :

```
npm run release
```

Note : l’exécution locale fonctionne uniquement si ton token GitHub est configuré
et a les permissions nécessaires.

## TODO (sécurité)

- Refaire un audit `npm audit` avant la livraison de fin février et réévaluer les dépendances `@semantic-release/*` si des correctifs sont publiés.
