# Documentation technique – Implémentation de la journalisation des connexions
**Application de réservation et de facturation des salles**  

---

## 1. Objectifs techniques

Cette documentation décrit l’implémentation technique de la journalisation des connexions afin de :

- tracer les connexions réussies, échouées et les déconnexions ;
- fournir une base exploitable pour l’audit de sécurité ;
- respecter les exigences RGPD (minimisation, durée de conservation, purge) ;
- assurer une implémentation robuste, maintenable et conforme aux standards Symfony 8.

---

## 2. Architecture générale

Le dispositif repose sur les composants suivants :

- une entité dédiée `AuthenticationLog` persistée en base ;
- un *event subscriber* Symfony chargé d’écouter les événements de sécurité ;
- une commande Symfony permettant la purge automatique des journaux ;
- une configuration de sécurité compatible avec l’authentification applicative existante.

---

## 3. Modèle de données

### 3.1 Entité `AuthenticationLog`

L’entité `AuthenticationLog` représente un événement de sécurité lié à l’authentification.

Champs recommandés :

| Champ | Type | Nullable | Description |
|------|------|----------|-------------|
| id | integer | non | Identifiant technique |
| user | ManyToOne(User) | oui | Utilisateur associé, s’il existe |
| identifier | string | non | Identifiant saisi (email) |
| eventType | string | non | LOGIN_SUCCESS / LOGIN_FAILURE / LOGOUT |
| occurredAt | datetime_immutable | non | Date et heure de l’événement |
| ipAddress | string | oui | Adresse IP |
| userAgent | text | oui | User-agent |
| failureReason | string | oui | Motif d’échec éventuel |

Le champ `user` est volontairement nullable afin de permettre la journalisation des échecs de connexion lorsque l’utilisateur n’existe pas.

---

## 4. Journalisation des événements de sécurité

### 4.1 Événements écoutés

L’implémentation repose sur l’écoute des événements Symfony suivants :

- succès d’authentification ;
- échec d’authentification ;
- déconnexion.

Ces événements sont interceptés via un *event subscriber* dédié.

---

### 4.2 Subscriber `AuthenticationLogSubscriber`

Responsabilités principales :

- intercepter les événements de sécurité ;
- collecter le contexte minimal (identifiant, IP, user-agent) ;
- persister un enregistrement `AuthenticationLog`.

Principes d’implémentation :

- aucune logique métier complexe ;
- aucune levée d’exception bloquante ;
- échec de journalisation non bloquant pour l’utilisateur.

---

### 4.3 Cas des échecs d’authentification

Lors d’un échec :

- si un utilisateur correspondant à l’identifiant existe, il est associé au log ;
- sinon, le champ `user` reste null ;
- l’identifiant saisi est toujours conservé.

Ce comportement garantit une journalisation complète des tentatives de connexion.

---

## 5. Sécurité et performance

- aucune donnée sensible (mot de passe) n’est journalisée ;
- les écritures sont simples et atomiques ;
- les logs sont stockés dans une table dédiée, isolée du métier ;
- le volume est maîtrisé via la purge automatique.

---

## 6. Purge automatique des journaux

### 6.1 Commande Symfony

Une commande dédiée permet de supprimer les journaux anciens :

```
php bin/console app:log-user:purge --days=365
```

Fonctionnement :

- suppression des entrées dont `occurredAt` est antérieur au seuil défini ;
- exécution non interactive ;
- journalisation éventuelle du nombre d’entrées supprimées.

---

### 6.2 Planification

La commande est exécutée :

- via une tâche CRON ;
- avec une fréquence hebdomadaire ;
- conformément à la durée de conservation définie dans la documentation RGPD.

---

## 7. Gestion des erreurs

- toute erreur de journalisation est capturée et ignorée ;
- aucune erreur de log ne doit empêcher une authentification ou une déconnexion ;
- les erreurs techniques peuvent être consignées dans les logs applicatifs standards.

---

## 8. Conformité RGPD et auditabilité

L’implémentation garantit :

- la traçabilité des accès ;
- la minimisation des données ;
- la limitation de la durée de conservation ;
- la possibilité d’extraction ciblée en cas d’audit DSI ou CNIL.

---

## 9. Points d’extension possibles

- ajout d’un verrouillage automatique après N échecs consécutifs ;
- détection d’adresses IP suspectes ;
- anonymisation différée des journaux ;
- intégration avec un SIEM ou un outil de supervision.

---

## 10. Synthèse

- la journalisation est centralisée et cohérente ;
- les échecs d’authentification sont correctement tracés ;
- l’implémentation est non intrusive et maintenable ;
- le dispositif est prêt pour un audit de sécurité.
