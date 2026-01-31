# Modélisation des données

Ce document formalise la **modélisation des données** issue de l’atelier avec le groupe de travail (GT). Il constitue une **référence fonctionnelle et métier** destinée à alimenter la documentation du projet et à servir de base à la **mise à jour du schéma des entités** par Codex.

La modélisation est structurée **par lots** correspondant aux jalons du projet.

---

## Lot 1 — Mars 2026

### Réservation ponctuelle

Périmètre fonctionnel : gestion des **usagers**, **sites**, **salles**, **équipements**, **réservations ponctuelles** et **tarification associée**.

### Vue d’ensemble des objets métiers

* Usagers
* Sites
* Salles
* Équipements
* Réservations
* Tarification
* Documents et conventions
* Utilisateurs (comptes applicatifs)

---

## 1. Usagers

Représente l’entité juridique ou physique effectuant une demande de réservation.

### Identité de la structure

* SIRET
* Dénomination légale
* Dénomination personnalisée (nom d’usage)
* Nature juridique : établissement public / privé
* Adresse postale

### Demandeur

* Civilité
* Fonction
* Nom
* Prénom
* Email (obligatoire)
* Téléphone (obligatoire)

### Payeur

* Civilité
* Fonction
* Nom
* Prénom
* Email (obligatoire)
* Téléphone (obligatoire)

### Sécurité et inscription

* Voir cadrage spécifique sur la sécurisation de l’inscription

---

## 2. Sites

Un site regroupe une ou plusieurs salles.

### Informations générales

* Nom du site
* Adresse postale
* Coordonnées de contact
* Nom du référent SPSL

### Contenus et descriptifs

* Texte descriptif
* Photos du site

### Accessibilité et environnement

* Proximité des transports en commun
* Parking

  * Gratuit / payant
  * Nombre de places
* Accès livraison
* Plan ou indications d’accès

---

## 3. Salles

Une salle est toujours rattachée à un site.

### Informations générales

* Nom de la salle
* Type de salle (liaison M2M)
* Superficie (en m²)
* Capacité d’accueil

  * Assise
  * Debout
* Configurations possibles (liaison M2M)

### Équipements et services inclus

* Équipements inclus (liaison M2M)
* Services inclus (liaison M2M)

  * Référencés et administrables (voir document collaboratif)

### Accessibilité et sécurité

* Accès PMR
* Ascenseur
* Sanitaires (dont PMR si applicable)
* Issues de secours
* Conformité ERP

  * Type
  * Catégorie
* Présence d’un agent ou gardien si nécessaire

### Conditions d’utilisation

* Horaires disponibles

  * Demi-journées (Lot 1)
  * Nuitées prévues en V2 (hébergement)
* Durée minimale de location
* Durée maximale de location
* Règlement intérieur (porté par le site)

  * Bruit
  * Propreté
  * Utilisation du matériel

### Autorisations spécifiques (par salle)

* Restauration
* Alcool

  * Mention légale associée
* Musique

  * Déclaration SACEM

---

## 4. Réservation

Représente une réservation ponctuelle d’une salle.

### Données principales

* Créneau de réservation
* Salle
* Usager
* État de la réservation

### Type d’événement

* Type d’événement
* Mentions légales associées

  * Gestion des justificatifs (à cadrer)

### Billetterie

* Aucune
* Inférieure à 5 €
* Supérieure à 5 €

---

## 5. Tarification

### Grille tarifaire

* Voir grille de tarification dédiée

### Frais annexes

* Ménage
* Gardiennage
* Matériel supplémentaire

### Garanties financières

* Dépôt de garantie / caution

---

## 6. Publics et usages autorisés

Définis au niveau de la salle.

* Réunions professionnelles
* Formations
* Conférences
* Événements associatifs
* Spectacles / expositions
* Manifestations sportives

---

## 7. Documents et informations pratiques

* Photos de la salle (élément critique)
* Plan de la salle
* Modalités de réservation
* Modalités d’annulation

---

## 8. Équipements

Équipements rattachés à une salle.

### Attributs

* Type d’équipement

  * Technique
  * Restauration
  * Sportif
  * Scénique
* Propre à la salle (oui / non)
* Désignation
* Nombre maximum autorisé dans la salle

---

## 9. Référentiels

### 9.1 Types de salle (M2M)

* Réunion
* Conférence
* Restauration
* Réception
* Polyvalente
* Sportive
* Culturelle

### 9.2 Configurations de salle (M2M)

* Théâtre
* U
* Classe
* Cocktail
* Banquet
* Gradin
* Auditorium

### 9.3 Types d’équipement

* Voir document collaboratif de référence

### 9.4 Types de services

* Voir document collaboratif de référence

---

## 10. Conventions et documents

* TODO

---

## 11. Utilisateurs

Utilisateurs applicatifs.

* Voir modélisation des profils et rôles

---

## Lot 2 — Septembre 2026

### Réservation récurrente et facturation

Périmètre fonctionnel étendu.

### 1. Réservation récurrente

* TODO

### 2. Facturation et suivi financier

* TODO

---

## Notes de cadrage pour Codex

* Les relations M2M doivent être modélisées explicitement (tables de jointure).
* Les référentiels (types, configurations, services, équipements) sont administrables.
* Les règles métier (autorisation, capacité, usages) doivent être portées au niveau de la salle.
* La facturation est hors périmètre du Lot 1.
