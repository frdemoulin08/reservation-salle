---
title: "MCD Sites (Venue)"
last_updated: "2026-02-04"
---

# MCD — Site (Venue)

Ce document liste les champs modélisés pour l’entité **Site (Venue)**, leur type, leur caractère obligatoire et leur présence dans le formulaire de création/édition.

> Statut **Formulaire** : oui = présent dans `VenueType` (création + édition), non = non exposé en saisie.

## Champs principaux (Venue)

| Champ (entité) | Type (BDD) | Obligatoire | Formulaire | Commentaire |
|---|---|---|---|---|
| `id` | INT (auto) | Oui (technique) | Non | Identifiant interne. |
| `publicIdentifier` | VARCHAR(36) | Oui | Non | UUID public généré automatiquement. |
| `name` | VARCHAR(255) | Oui | Oui | Nom du site. |
| `description` | LONGTEXT | Oui (métier) | Oui | Texte descriptif (max 500 caractères). |
| `referenceContactUser` | FK `app_user` (nullable) | Non | Oui | Référent SPSL (filtré sur rôles admin métier / gestionnaire applicatif). |
| `publicTransportAccess` | LONGTEXT | Non | Oui | Proximité des transports en commun. |
| `parkingType` | VARCHAR(50) | Non | Oui | Valeurs: gratuit / payant. |
| `parkingCapacity` | INT | Non | Oui | Nombre de places. |
| `deliveryAccess` | LONGTEXT | Non | Oui | Accès livraison. |
| `accessMapUrl` | VARCHAR(255) | Non | Non | Lien vers plan/indications d’accès (non exposé actuellement). |
| `houseRules` | LONGTEXT | Non | Non | Règlement intérieur (non exposé actuellement). |
| `createdAt` | DATETIME | Oui (technique) | Non | Date de création (auto). |
| `updatedAt` | DATETIME | Oui (technique) | Non | Date de mise à jour (auto). |

## Adresse (Address embeddable)

| Champ (entité) | Type (BDD) | Obligatoire | Formulaire | Commentaire |
|---|---|---|---|---|
| `address.line1` | VARCHAR(255) | Oui | Oui | Adresse principale. |
| `address.line2` | VARCHAR(255) | Non | Oui | Complément d’adresse. |
| `address.line3` | VARCHAR(255) | Non | Oui | Complément d’adresse (suite). |
| `address.postalCode` | VARCHAR(10) | Oui | Oui | Code postal. |
| `address.city` | VARCHAR(100) | Oui | Oui | Commune. |
| `address.country` | VARCHAR(2) | Oui | Oui | Pays (ISO2). |
| `address.source` | VARCHAR(50) | Non | Oui (hidden) | Source d’adresse (BAN / manual). |
| `address.externalId` | VARCHAR(255) | Non | Oui (hidden) | Identifiant externe BAN. |
| `address.latitude` | DOUBLE | Non | Oui (hidden) | Latitude. |
| `address.longitude` | DOUBLE | Non | Oui (hidden) | Longitude. |

## Relations principales

| Relation | Cardinalité | Obligatoire | Commentaire |
|---|---|---|---|
| `rooms` | 1 Site → N Salles | Non | Salles rattachées au site. |
| `documents` | 1 Site → N Documents | Non | Photos + documents associés. |
| `venueEquipments` | 1 Site → N Équipements | Non | Équipements du site. |
