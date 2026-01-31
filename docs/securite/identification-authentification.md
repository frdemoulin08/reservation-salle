# Stratégie d’identification et d’authentification des utilisateurs  
**Application de réservation et de facturation des salles**  
*Document de cadrage – Sécurité / RGPD / Audit CNIL*

---

## 1. Objectifs

La stratégie d’identification et d’authentification vise à :

- garantir un accès sécurisé à l’application ;
- identifier de manière fiable chaque utilisateur ;
- assurer la traçabilité des connexions et des actions ;
- respecter les principes de protection des données personnelles (RGPD / CNIL) ;
- permettre une évolution future du dispositif (SSO, interconnexion SI).

---

## 2. Principe général

L’application distingue clairement :

- **l’identification** : manière dont un utilisateur est identifié de façon unique ;
- **l’authentification** : mécanisme permettant de vérifier l’identité déclarée.

Ces deux notions sont volontairement dissociées afin d’assurer la robustesse et la pérennité du système.

---

## 3. Stratégie d’identification des utilisateurs

### 3.1 Identifiant principal : l’adresse email

L’adresse email constitue l’identifiant principal de connexion à l’application.

Caractéristiques :

- une adresse email est **obligatoirement associée à chaque utilisateur** ;
- l’email est **unique** au sein de l’application ;
- l’email est stocké en base sous une forme **normalisée** (minuscule) ;
- l’email est utilisé comme **identifiant de connexion** côté Symfony.

Ce choix permet :
- une identification simple et compréhensible par les utilisateurs ;
- une unicité naturelle, y compris pour les utilisateurs externes (associations, partenaires).

---

### 3.2 Identifiant technique interne (`id`)

L’identifiant technique interne est la clé primaire `id` de l’entité `User`.

Caractéristiques :

- identifiant **unique et stable dans le temps** ;
- indépendant de l’adresse email ;
- non exposé en dehors des zones d’administration.

Un identifiant lisible peut être dérivé à la volée (ex. `user-000123`) sans stockage en base.

---

### 3.3 Identifiant public (`publicIdentifier`)

Un identifiant public optionnel est associé à l’utilisateur :

- type recommandé : UUID/ULID ;
- unique et non devinable ;
- utilisé pour les URLs exposées.

---

### 3.4 Contrainte d’unicité

Les règles suivantes sont appliquées en base de données :

- unicité de l’adresse email ;
- unicité de l’identifiant public (`publicIdentifier`) lorsqu’il est utilisé.

Ces contraintes garantissent l’unicité et l’intégrité des comptes utilisateurs.

---

## 4. Stratégie d’authentification

### 4.1 Authentification locale

L’authentification repose sur les mécanismes standards du framework Symfony.

Principes :

- authentification par couple **email / secret d’authentification** ;
- stockage sécurisé des secrets (hachage fort) ;
- aucune conservation des secrets en clair.

---

### 4.2 Accès public

- Certaines pages de l’application sont accessibles sans authentification.
- Ces accès sont gérés via le mécanisme Symfony `PUBLIC_ACCESS`.
- Aucun compte utilisateur n’est associé à ces accès.

---

### 4.3 Utilisateur authentifié

- Toute personne authentifiée est considérée comme **utilisateur connecté**.
- Le rôle technique `ROLE_USER` est automatiquement attribué.
- Les droits fonctionnels sont ensuite déterminés par les rôles applicatifs associés au compte.

---

## 5. Gestion du cycle de vie des comptes

### Création

- Les comptes utilisateurs sont créés par un acteur habilité (Super Administrateur).
- Chaque création est justifiée par une finalité fonctionnelle.

### Modification

- Les données d’identification peuvent être mises à jour (ex. changement d’email).
- L’identifiant technique interne demeure inchangé.

### Désactivation

- Un compte peut être désactivé sans suppression immédiate.
- La désactivation empêche toute authentification.

### Suppression

- La suppression définitive respecte les règles de conservation des données.
- Les journaux applicatifs sont conservés conformément aux exigences réglementaires.

---

## 6. Traçabilité et sécurité

- Les connexions peuvent être journalisées (date, utilisateur, contexte).
- Les événements sensibles (création, modification, désactivation de comptes) sont traçables.
- Les accès non authentifiés sont strictement limités aux ressources publiques.

---

## 7. Conformité RGPD / CNIL

Le dispositif respecte les principes suivants :

- **Finalité** : identification nécessaire à l’accès à l’application.
- **Minimisation** : seules les données strictement nécessaires sont collectées.
- **Sécurité** : authentification sécurisée, contrôle d’accès, journalisation.
- **Responsabilité** : stratégie documentée et contrôlable en cas d’audit.

---

## 8. Synthèse

- L’email est l’identifiant principal de connexion.
- Un identifiant technique interne garantit la stabilité des références.
- L’authentification repose sur des mécanismes standards et sécurisés.
- Le modèle est évolutif, auditable et conforme aux exigences réglementaires.
