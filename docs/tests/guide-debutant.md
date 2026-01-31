# Guide de mise en place des tests (développeur)

Ce guide explique comment exécuter et ajouter des tests dans le projet.

## 1) Pré-requis

- PHP 8.4
- Dépendances installées :

```
composer install
```

## 2) Configuration test

Le projet utilise une base **SQLite** dédiée aux tests (voir `.env.test`).
Aucune configuration manuelle n’est requise.

## 3) Lancer les tests

### Tous les tests
```
./vendor/bin/phpunit
```

### Affichage lisible (nom + statut)
```
./vendor/bin/phpunit --testdox
```

### Cibler un test
```
./vendor/bin/phpunit --testdox tests/Functional/Administration/UserCrudTest.php
```

## 4) Types de tests utilisés

- **Fonctionnels sans DB** : navigation, accès, login (sans fixtures)
- **Fonctionnels avec DB** : CRUD, commandes, pagination, journalisation

Les tests DB se basent sur `DatabaseWebTestCase`, qui :
- recrée le schéma,
- recharge les fixtures,
- utilise SQLite (rapide et isolé).

## 5) Ajouter un test (exemple)

1. Créer un fichier dans `tests/Functional/...`.
2. Étendre `WebTestCase` (sans DB) ou `DatabaseWebTestCase` (avec DB).
3. Écrire un scénario minimal :

```php
public function testPageRenders(): void
{
    $client = self::createClient();
    $client->request('GET', '/');

    self::assertResponseIsSuccessful();
}
```

## 6) Bonnes pratiques

- Ajouter un test quand un bug est corrigé.
- Garder les messages d’erreur synchronisés (front/back + traductions).
- Privilégier des tests simples, lisibles et rapides.

