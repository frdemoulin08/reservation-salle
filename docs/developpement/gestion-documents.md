# Stratégie de gestion des documents
Application de réservation de salles – Symfony 8

## 1. Objectifs

Cette documentation décrit la stratégie retenue pour la gestion des documents
associés aux entités de l’application (en particulier les *sites*), notamment :

- plans d’accès,
- photos,
- plans techniques,
- conventions,
- devis, factures,
- tout autre document métier.

Les objectifs sont :
- garantir une gestion **sécurisée** des fichiers (audit sécurité),
- proposer une **UX claire** côté back-office et front-office,
- assurer une **évolutivité** sans refonte technique,
- fournir un cadre clair pour l’implémentation (Codex).

---

## 2. Principes structurants

### 2.1. Séparation stricte public / privé

Deux catégories de documents sont distinguées :

- **Documents publics**
  - visibles côté front-office (ex. photos, plan d’accès public),
  - stockés sous `public/`,
  - accessibles via URL directe.

- **Documents privés**
  - usage interne uniquement (ex. conventions, plans techniques),
  - stockés hors de `public/`,
  - accessibles uniquement via un contrôleur sécurisé.

Aucun document privé ne doit être exposé directement par un chemin public.

---

### 2.2. Abstraction du stockage via Flysystem

L’accès au système de fichiers est réalisé via **Flysystem** (OneupFlysystemBundle),
afin de :

- découpler le code du backend du support de stockage,
- permettre une évolution ultérieure (S3, MinIO, etc.),
- centraliser les opérations (upload, lecture, suppression).

Deux filesystems sont définis :
- `site_public_filesystem`
- `site_private_filesystem`

---

### 2.3. Nommage technique des fichiers

Les fichiers sont stockés avec un **nom technique opaque** :

- UUID + extension,
- éventuellement répartis dans des sous-dossiers par entité (ex. par site).

Le nom de fichier d’origine :
- n’est **jamais utilisé comme clé technique**,
- peut être conservé comme **métadonnée informative**.

---

## 3. Modélisation des données

### 3.1. Entité `SiteDocument`

Chaque document est représenté par une entité dédiée.

Champs principaux :

- `site` : site auquel le document est rattaché,
- `type` : type fonctionnel du document (relation),
- `label` : libellé libre, lisible côté UI (optionnel),
- `originalFilename` : nom du fichier téléversé (optionnel),
- `storagePath` : chemin technique dans le filesystem,
- `mimeType` : type MIME,
- `size` : taille en octets,
- `public` : visibilité publique ou privée,
- `createdAt` : date d’ajout.

Le document est **indépendant du nom du fichier physique**.

---

### 3.2. Gestion des types de documents en base

Les types de documents sont **paramétrés en base de données**
et administrés via une section dédiée de l’application.

#### Entité `SiteDocumentType`

Cette entité permet de définir dynamiquement les catégories de documents.

Champs recommandés :

- `code` (unique, non modifiable)  
  Exemples : `PLAN_ACCES`, `PHOTO`, `CONVENTION`, `FACTURE`
- `label` (libellé affiché)
- `description` (aide contextuelle)
- `public` (document visible côté front)
- `required` (obligatoire pour un site)
- `multipleAllowed` (plusieurs documents autorisés ou non)
- `active` (activation/désactivation sans suppression)
- `position` (ordre d’affichage)

L’entité `SiteDocument` référence obligatoirement un `SiteDocumentType`.

---

## 4. Administration (Back-office)

### 4.1. Section Paramétrage

Une sous-section dédiée est prévue :

**Paramétrage > Types de documents**

Fonctionnalités attendues :
- création / édition des types,
- activation / désactivation,
- gestion de l’ordre d’affichage,
- impossibilité de supprimer un type déjà utilisé.

Le champ `code` est figé après création.

---

### 4.2. Gestion des documents d’un site

Dans l’interface d’administration d’un site :

- liste des documents existants, regroupés par type,
- formulaire d’ajout :
  - sélection du type (parmi les types actifs),
  - téléversement du fichier,
  - saisie optionnelle du label,
- respect des règles du type :
  - unicité si `multipleAllowed = false`,
  - présence obligatoire si `required = true`.

---

## 5. Exposition et accès aux documents

### 5.1. Documents publics

- stockés sous `public/uploads/...`,
- accessibles via URL directe,
- affichés dans le front-office (images, liens).

### 5.2. Documents privés

- stockés sous `var/uploads/...`,
- accessibles uniquement via un contrôleur Symfony,
- contrôle des droits avant envoi du fichier,
- téléchargement via réponse streamée.

---

## 6. Sécurité et validation

Les règles suivantes s’appliquent systématiquement :

- validation Symfony sur les fichiers uploadés :
  - taille maximale,
  - types MIME autorisés par type de document,
- refus de tout fichier exécutable,
- pas d’exécution ni d’inclusion de fichiers uploadés,
- contrôle d’accès avant toute lecture de document privé.

Des extensions ultérieures peuvent être prévues :
- antivirus (ex. ClamAV),
- journalisation des téléchargements.

---

## 7. Bonnes pratiques d’implémentation

- centraliser la logique d’upload dans un service dédié,
- ne jamais manipuler directement le filesystem dans les contrôleurs,
- ne jamais exposer le `storagePath` côté front,
- utiliser le `label` ou le libellé du type pour l’affichage utilisateur,
- conserver le `originalFilename` uniquement à des fins informatives.

---

## 8. Évolutions possibles

La stratégie permet sans refonte :

- d’ajouter de nouveaux types de documents,
- d’étendre la gestion documentaire à d’autres entités
  (réservations, factures, contrats),
- de changer de backend de stockage,
- de renforcer les contrôles de conformité et d’audit.

---

**Document de référence pour l’implémentation – à destination de Codex.**
