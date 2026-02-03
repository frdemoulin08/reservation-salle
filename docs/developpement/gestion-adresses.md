# Stratégie UX de gestion des adresses

## 1. Objectif du document

Ce document définit la **stratégie UX de gestion des adresses** dans l’application de réservation de salles.

Il a pour objectifs :
- d’assurer une **expérience utilisateur simple et fluide**,
- de tirer parti de la **Base Adresse Nationale (BAN)** pour les adresses françaises,
- de permettre la **saisie d’adresses étrangères** (notamment belges) sans friction,
- de fournir à Codex un **cadre clair et non ambigu** pour l’implémentation frontend et backend.

Ce document constitue une **référence de conception** et doit être respecté lors des développements.

---

## 2. Principe UX fondamental

> **Le pays pilote le mode de saisie de l’adresse.**

- 🇫🇷 **France** → aide à la saisie via auto-complétion BAN
- 🌍 **Étranger** → saisie libre, sans dépendance à un référentiel externe

L’utilisateur ne doit **jamais être bloqué** dans la saisie de son adresse.

---

## 3. Structure standard d’un formulaire d’adresse

### 3.1. Ordre des champs (obligatoire)

1. **Pays** (liste déroulante)
2. **Adresse (ligne principale)**
3. Complément d’adresse (facultatif)
4. Code postal
5. Ville

> Le champ **Pays doit toujours être visible et modifiable**, même si une valeur par défaut (France) est proposée.

---

## 4. Cas n°1 – Adresse en France (pays = FR)

### 4.1. Comportement UX attendu

- Le champ **Adresse** propose une **auto-complétion** basée sur la BAN.
- L’utilisateur commence à saisir son adresse (ex. *"12 rue de la République"*).
- Une liste de suggestions est affichée.
- La sélection d’une suggestion remplit automatiquement :
  - la ligne d’adresse,
  - le code postal,
  - la ville.

### 4.2. Liberté utilisateur

- Les champs restent **éditables après sélection**.
- L’utilisateur peut corriger ou compléter l’adresse (bâtiment, entrée, lieu-dit, etc.).

### 4.3. Données techniques associées (transparentes pour l’utilisateur)

- `source = BAN`
- `externalId = identifiant BAN`
- `latitude / longitude` si disponibles

---

## 5. Cas n°2 – Adresse à l’étranger (pays ≠ FR)

### 5.1. Comportement UX attendu

- L’auto-complétion BAN est **désactivée**.
- Tous les champs sont en **saisie libre**.
- Les libellés et placeholders sont adaptés (ex. *"Adresse complète"*).

### 5.2. Messages à l’utilisateur

- Aucun message d’erreur ou d’alerte n’est affiché.
- Une aide discrète peut être proposée :
  > *"Saisie manuelle pour les adresses hors France."*

### 5.3. Données techniques

- `source = MANUAL`
- `externalId = null`
- `latitude / longitude = null`

---

## 6. Changement de pays en cours de saisie

### 6.1. Règle UX

Un changement de pays **ne doit jamais entraîner une perte de données sans confirmation**.

### 6.2. Comportement recommandé

Lorsque l’utilisateur change le pays après avoir commencé la saisie :

- Les champs existants sont conservés par défaut.
- Une information douce peut être affichée :
  > *"Le mode de saisie change pour les adresses hors France."*

Optionnellement, une confirmation peut être proposée :
- **Conserver les informations saisies**
- **Effacer et recommencer**

---

## 7. Cas des adresses multiples (siège / facturation)

### 7.1. Principe

Certaines entités (ex. usagers) peuvent disposer de **plusieurs adresses logiques** :
- adresse de siège,
- adresse de facturation.

### 7.2. UX recommandé

- Une case à cocher est proposée :
  > ☑ *Adresse de facturation identique à l’adresse du siège*

### 7.3. Comportement

- Case cochée :
  - la section "Adresse de facturation" est masquée,
  - l’adresse du siège est réutilisée côté backend.
- Case décochée :
  - un **second formulaire d’adresse** est affiché,
  - avec les **mêmes règles UX** (France / étranger).

---

## 8. Composant d’adresse réutilisable

### 8.1. Principe

- Un **composant d’adresse unique** doit être implémenté.
- Ce composant gère :
  - la sélection du pays,
  - le mode auto-complétion ou saisie libre,
  - la cohérence des champs.

Il est réutilisé pour :
- adresse de site,
- adresse d’usager,
- adresse de facturation.

---

## 9. Bonnes pratiques UX

- Toujours privilégier l’aide à la saisie sans la rendre obligatoire.
- Éviter toute terminologie technique (BAN, API, identifiants).
- Laisser l’utilisateur maître de ses données.
- Assurer une cohérence visuelle et comportementale sur tous les formulaires.

---

## 10. Anti-patterns à éviter

- Forcer la validation BAN pour toutes les adresses.
- Bloquer une adresse étrangère pour non-conformité.
- Cacher le champ Pays.
- Rendre un champ non modifiable après auto-complétion.
- Afficher des messages techniques ou anxiogènes.

---

## 11. Résumé exécutif

- Le **pays détermine le mode de saisie**.
- BAN utilisée uniquement pour la France.
- Saisie libre pour les adresses étrangères.
- Un composant d’adresse unique et réutilisable.
- Aucune perte de données sans action explicite de l’utilisateur.

👉 Ce document fait partie intégrante de la **base documentaire de l’application** et sert de référence UX pour Codex et les développeurs.

---

## TODO (Paramétrage)

- Prévoir une **gestion des pays autorisés** dans la section **Paramétrage** de l’application (liste configurable), afin d’éviter un hardcode côté formulaire.
- Une entité `Country` est désormais en place côté modèle (code ISO, libellé, indicatif), mais l’UI de paramétrage reste à implémenter.
