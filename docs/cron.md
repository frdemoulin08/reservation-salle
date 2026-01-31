# Tâches CRON du projet

## Objectif
Ce document centralise les tâches planifiées à exécuter sur l’environnement de production (ou pré‑production).

## Purge hebdomadaire des journaux de connexion
Conformément à la note RGPD, la purge des connexions est prévue **une fois par semaine**.

Commande :

```
php bin/console app:auth-log:purge --days=365
```

Exemple de CRON (exécution hebdomadaire, le lundi à 03h15) :

```
15 3 * * 1 /usr/bin/php /chemin/vers/le/projet/bin/console app:auth-log:purge --days=365
```

## Recommandations
- Exécuter la commande en environnement `prod`.
- Journaliser la sortie de la commande (stdout/stderr) pour audit.
- Vérifier que l’utilisateur système dispose des droits nécessaires.
