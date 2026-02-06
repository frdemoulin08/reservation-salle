---
title: "Cadrage – Enrichissement des structures par SIRET (API gouvernementales)"
version: "1.0"
last_updated: "2026-02-06"
scope: "Application de réservation et facturation de salles"
---

# Cadrage – Enrichissement des structures par SIRET

## 1. Objectif du document

Ce document définit la **stratégie fonctionnelle et technique** permettant de :
- renseigner un **SIRET** lors de la création ou modification d’une structure (entreprise, association, collectivité) ;
- **enrichir automatiquement** la fiche structure à partir de référentiels administratifs officiels ;
- garantir une **implémentation robuste, évolutive et conforme** aux pratiques de l’administration française.

Ce cadrage s’applique uniquement aux **structures françaises** disposant d’un SIRET valide.

---

## 2. Périmètre fonctionnel

### 2.1. Structures concernées

- Entreprises privées
- Associations
- Établissements publics / collectivités
- Toute structure immatriculée dans le **répertoire Sirene**

### 2.2. Structures hors périmètre

- Structures étrangères (ex. Belgique)
- Personnes physiques
- Structures sans identifiant administratif français

Pour ces cas, la saisie reste **entièrement manuelle**, sans appel API.

---

## 3. Référentiels et API retenus

### 3.1. API principale (V1)

**API Recherche d’Entreprises – Annuaire des entreprises**

- Fournisseur : :contentReference[oaicite:0]{index=0}
- Accès : ouvert, sans authentification
- Usage : recherche par SIRET ou SIREN
- Données : dénomination, adresse, code NAF, statut, effectif, etc.
- Avantages :
  - simplicité d’intégration
  - aucun onboarding administratif
  - adaptée à un usage UX de préremplissage

### 3.2. API complémentaire (V2 – optionnelle)

**API Sirene**

- Fournisseur : :contentReference[oaicite:1]{index=1}
- Accès : authentifié (clé API)
- Usage : données exhaustives et historisées
- À envisager si :
  - besoin de champs avancés (catégorie juridique détaillée, historique, successions)
  - exigences fortes en matière de complétude des données

### 3.3. API non retenue à ce stade

**API Entreprise**

- Usage réservé à des démarches administratives complexes
- Accès soumis à habilitation
- Hors scope du besoin actuel (préremplissage de fiches)

---

## 4. Règles fonctionnelles

### 4.1. Activation de la logique SIRET

- Le champ **Pays** conditionne le comportement :
  - `Pays = France` :
    - champ SIRET visible
    - champ SIRET obligatoire (selon le type de structure)
    - appel API activé
  - `Pays ≠ France` :
    - champ SIRET désactivé ou facultatif
    - aucun appel API

### 4.2. Validation du SIRET

Avant tout appel API :
- longueur = 14 caractères
- chiffres uniquement
- contrôle de validité (algorithme de Luhn)

En cas d’échec :
- message d’erreur immédiat
- aucun appel réseau

---

## 5. Comportement UX attendu

### 5.1. Parcours utilisateur

1. L’utilisateur sélectionne **France** comme pays
2. Il renseigne un SIRET
3. À la sortie du champ ou via un bouton *Rechercher* :
   - validation locale
   - appel AJAX vers le back-end
4. Si une structure est trouvée :
   - préremplissage automatique des champs
   - indication visuelle :  
     *« Données issues des référentiels administratifs (Sirene / Annuaire des entreprises) »*
5. Tous les champs restent **éditables** par l’utilisateur

### 5.2. Gestion des erreurs

- SIRET introuvable :
  - message clair
  - bascule en saisie manuelle
- API indisponible :
  - message non bloquant
  - aucune perte de données saisies

---

## 6. Données récupérées et normalisées

À partir de l’API, les données suivantes peuvent être exploitées :

- SIRET
- SIREN
- Dénomination légale
- Nom usuel / enseigne
- Adresse :
  - ligne d’adresse
  - code postal
  - commune
  - pays = FR
- Code NAF / APE
- Type de structure (via catégorie juridique)
- Date de création (optionnel)

Les données sont **copiées** dans le modèle applicatif et **ne sont pas synchronisées** automatiquement par la suite.

---

## 7. Architecture technique cible

### 7.1. Principe général

- Une **couche d’abstraction unique** pour la recherche d’entreprises
- Aucun appel API direct depuis le front
- Toutes les réponses sont **normalisées** avant exposition au front

### 7.2. Service applicatif

Un service dédié est responsable :
- de la validation du SIRET
- de l’appel à l’API externe
- de la transformation de la réponse en DTO métier

Exemple conceptuel :

- `CompanyLookupService`
  - `findBySiret(string $siret): ?CompanyData`

### 7.3. Exposition API interne

- Endpoint interne :
  - `GET /api/companies/siret/{siret}`
- Réponse JSON normalisée, indépendante de l’API source

---

## 8. Évolutivité et maintenabilité

- Le choix de l’API externe est **configurable**
- Le front n’a aucune dépendance directe au fournisseur de données
- Possibilité future :
  - d’ajouter d’autres sources (API Sirene, API Entreprise)
  - de gérer plusieurs pays avec des connecteurs spécifiques

---

## 9. Sécurité et conformité

- Aucun stockage de clé API en front
- Timeout et gestion des erreurs réseau obligatoires
- Les données récupérées sont :
  - publiques
  - utilisées uniquement pour faciliter la saisie
- Aucune donnée n’est réutilisée à des fins statistiques sans anonymisation

---

## 10. Synthèse

Cette stratégie permet :
- une **expérience utilisateur fluide**
- un **socle technique simple et robuste**
- une **évolution maîtrisée** vers des API plus riches si nécessaire
- une parfaite compatibilité avec les contraintes métiers (structures étrangères, facturation, auditabilité)

---
