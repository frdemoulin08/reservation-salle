# Gestion des rôles applicatifs en base de données  
**Application de réservation et de facturation des salles**  
*Documentation technique – Implémentation & gouvernance des habilitations*

---

## 1. Objectifs

La gestion des rôles applicatifs vise à :

- disposer d’un modèle d’habilitation **structuré, lisible et interrogeable** ;
- faciliter les **requêtes métier et les extractions** (audit, contrôle, pilotage) ;
- garantir la **traçabilité et la justification des droits** ;
- répondre aux exigences de **sécurité** et de **conformité RGPD / CNIL**.

---

## 2. Principe général retenu

Les rôles applicatifs sont :

- **persistés en base de données** dans une entité dédiée ;
- **associés aux utilisateurs via une relation Many-to-Many** ;
- exposés à Symfony sous forme de **tableau de chaînes (`string[]`)** pour la gestion de la sécurité.

Ce choix se substitue volontairement au stockage des rôles dans un champ JSON directement intégré à l’entité `User`.

---

## 3. Modélisation des données

### 3.1 Entité `Role`

L’entité `Role` représente un rôle applicatif documenté.

Champs recommandés :

| Champ | Type | Description |
|------|------|-------------|
| id | integer | Identifiant technique |
| code | string | Code technique du rôle (ex. `ROLE_APP_MANAGER`) |
| label | string | Intitulé fonctionnel lisible (ex. *Application Manager*) |
| description | text | Description fonctionnelle du rôle |
| isActive | boolean | Activation / désactivation du rôle |

Le champ `code` est utilisé comme référence unique dans la sécurité Symfony.

---

### 3.2 Entité `User`

L’entité `User` est liée à l’entité `Role` par une relation **Many-to-Many**.

Principe :

- un utilisateur peut disposer de **plusieurs rôles** ;
- un rôle peut être attribué à **plusieurs utilisateurs** ;
- la relation est matérialisée par une table de jointure (ex. `user_role`).

---

## 4. Exposition des rôles à Symfony

Bien que les rôles soient stockés en base de données sous forme d’entités, Symfony nécessite un tableau de chaînes (`string[]`) pour la gestion des accès.

Le mécanisme repose sur la méthode `getRoles()` de l’entité `User`.

### Principe

- Les codes des rôles associés à l’utilisateur sont extraits depuis la relation Many-to-Many.
- Le rôle technique `ROLE_USER` est systématiquement ajouté pour tout utilisateur authentifié.
- Les doublons sont supprimés.

Ce mécanisme garantit une compatibilité complète avec :
- `isGranted()`
- `access_control`
- `role_hierarchy`

---

## 5. Rôles applicatifs définis

| Intitulé fonctionnel (EN) | Code rôle |
|---------------------------|----------|
| Super Administrator | `ROLE_SUPER_ADMIN` |
| Business Administrator | `ROLE_BUSINESS_ADMIN` |
| Application Manager | `ROLE_APP_MANAGER` |
| Supervisor | `ROLE_SUPERVISOR` |
| Authenticated User | `ROLE_USER` |
| Public Access | `PUBLIC_ACCESS` |

Remarques :
- `ROLE_USER` est un rôle technique ajouté automatiquement à l’authentification.
- `PUBLIC_ACCESS` correspond à un accès sans authentification et ne fait pas l’objet d’un stockage en base.

---

## 6. Cumul et relations entre rôles

### Cumul de rôles

- Un utilisateur peut cumuler plusieurs rôles applicatifs.
- Chaque attribution est **explicite et justifiée**.
- Les cumuls sont documentés et traçables.

### Relation fonctionnelle spécifique

- Le rôle `ROLE_BUSINESS_ADMIN` inclut l’ensemble des prérogatives du rôle `ROLE_APP_MANAGER`.

Cette relation peut être :
- documentée uniquement ;
- ou implémentée techniquement via la hiérarchie des rôles Symfony.

---

## 7. Gouvernance et sécurité

### Attribution des rôles

- L’attribution, la modification ou la révocation des rôles est strictement réservée au rôle :
  - `ROLE_SUPER_ADMIN`.

### Principe de moindre privilège

- Chaque utilisateur ne dispose que des rôles nécessaires à ses missions.
- Aucun rôle n’est attribué par défaut en dehors de `ROLE_USER`.

### Désactivation

- Les rôles peuvent être désactivés sans suppression physique.
- Les comptes utilisateurs peuvent être désactivés indépendamment des rôles.

---

## 8. Avantages du modèle retenu

Le stockage des rôles en base via une relation Many-to-Many permet :

- des **requêtes métier simples et lisibles** (recherche par rôle) ;
- une **documentation fonctionnelle des rôles** ;
- une meilleure **auditabilité** ;
- une évolution facilitée du modèle d’habilitation ;
- une séparation claire entre **sécurité applicative** et **modèle métier**.

---

## 9. Conformité RGPD / CNIL

Ce modèle contribue à la conformité réglementaire par :

- la minimisation des droits attribués ;
- la traçabilité des habilitations ;
- la documentation explicite des finalités d’accès ;
- la capacité à produire des extractions en cas de contrôle.

---

## 10. Synthèse

- Les rôles sont des objets métier persistés.
- Les utilisateurs peuvent cumuler plusieurs rôles.
- Symfony consomme uniquement les codes de rôles.
- Le modèle est lisible, auditable et durable.
