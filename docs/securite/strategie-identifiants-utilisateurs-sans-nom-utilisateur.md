# Stratégie d’identifiants utilisateurs (sans `username`)
**Application de réservation et de facturation des salles**

---

## 1. Objectifs

Cette stratégie définit une gestion des identifiants utilisateurs volontairement **simplifiée**, **robuste** et **audit-proof**, visant à :

- garantir une identification fiable et stable des utilisateurs ;
- limiter la multiplication des identifiants redondants ;
- répondre aux exigences de sécurité (DSI) et de protection des données (RGPD) ;
- faciliter l’exploitation technique et la maintenance applicative.

---

## 2. Principes généraux

L’application distingue clairement les différents usages des identifiants :

- identification technique interne ;
- authentification (connexion) ;
- exposition éventuelle dans les URLs ;
- lisibilité dans les journaux et interfaces d’administration.

Dans ce cadre, aucun champ `username` persistant n’est introduit dans le modèle de données.

---

## 3. Identifiants retenus

### 3.1 Identifiant technique interne (`id`)

- Il s’agit de la clé primaire de l’entité `User`.
- Cet identifiant est :
  - unique ;
  - stable dans le temps ;
  - non modifiable.
- Il constitue la référence technique de base dans :
  - les relations entre entités ;
  - les journaux techniques ;
  - les exports d’administration.

Pour améliorer la lisibilité humaine (logs, backoffice), un identifiant lisible peut être dérivé à la volée de cet `id`, par exemple :

- `user-000123` pour un utilisateur ayant l’ID `123`.

Cet identifiant lisible n’est pas stocké en base.

---

### 3.2 Adresse email (identifiant de connexion)

- L’adresse email est utilisée comme identifiant de connexion.
- Elle est :
  - unique au sein de l’application ;
  - stockée sous forme normalisée (minuscule) ;
  - modifiable au cours de la vie du compte.
- L’email constitue également une coordonnée de contact.

L’email n’est pas utilisé comme clé technique interne.

---

### 3.3 Identifiant public (`publicIdentifier`)

Un identifiant public optionnel peut être associé à l’utilisateur :

- type recommandé : UUID ou ULID ;
- unique et non devinable ;
- indépendant de l’identifiant interne (`id`).

Cet identifiant est destiné aux cas suivants :

- URLs exposées à des profils non administrateurs ;
- échanges ou liens externes ;
- prévention des risques de manipulation d’URL (IDOR).

Exemple d’usage :

- `/u/{publicIdentifier}`

L’identifiant interne numérique (`id`) n’est jamais exposé dans ces contextes.

---

## 4. Absence volontaire de `username`

Le champ `username` n’est pas retenu dans le modèle pour les raisons suivantes :

- redondance fonctionnelle avec l’identifiant technique (`id`) ;
- confusion possible avec l’adresse email (identifiant de connexion) ;
- complexité inutile dans la documentation et les flux ;
- absence de besoin métier ou inter-SI identifié à ce stade.

Si un besoin futur apparaît (interopérabilité, SSO, annuaire), un champ dédié pourra être introduit (`externalIdentifier`, `matricule`, etc.) sans remettre en cause cette stratégie.

---

## 5. Utilisation dans les URLs

- Backoffice d’administration :
  - routes de type `/admin/users/{id}` ;
  - accès strictement réservé aux profils habilités ;
  - contrôles d’accès systématiques côté serveur.

- Front ou espaces usagers :
  - absence d’identifiant dans l’URL lorsque possible (ex. `/mon-compte`) ;
  - utilisation de `publicIdentifier` lorsque l’identification par URL est nécessaire.

---

## 6. Journalisation et traçabilité

- Les journaux techniques référencent :
  - l’utilisateur via sa clé technique (`id`) lorsque disponible ;
  - l’identifiant saisi (email) lors des tentatives de connexion.
- Les noms, prénoms et libellés lisibles sont :
  - affichés à la volée dans les interfaces d’administration ;
  - non dupliqués inutilement dans les tables de logs.

Cette approche limite la diffusion de données personnelles tout en conservant une bonne lisibilité opérationnelle.

---

## 7. Synthèse

- `id` : identifiant technique interne de référence.
- `email` : identifiant de connexion, unique et modifiable.
- `publicIdentifier` : identifiant opaque pour les usages exposés.
- aucun `username` persistant.
- identifiants lisibles générés à la volée pour l’exploitation.

Cette stratégie garantit un modèle d’identification simple, cohérent, sécurisé et durable.
