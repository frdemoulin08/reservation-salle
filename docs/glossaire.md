---
title: "Glossaire métier – Application de réservation de salles"
last_updated: "2026-02-07"
---

# Glossaire métier

Ce glossaire définit les principaux **concepts métier** de l’application de réservation et de facturation de salles.  
Il a pour objectifs de :

- stabiliser le **vocabulaire** utilisé dans le code, les écrans et la documentation ;
- faciliter la **communication** entre métier, développeurs et DSI ;
- servir de **référence** lors des évolutions et des audits.

Les libellés UI sont gérés via les fichiers de traduction (notamment `translations/messages.fr.yaml`).

---

## Résumé rapide (concepts clés)

| Concept | Libellé UI (ex.) | Entité / Classe | Clés de traduction principales |
| --- | --- | --- | --- |
| Organisation (Usager) | Usager / Usagers | `App\Entity\Organization` | `organization.*` |
| Contact usager | Contact usager / Contacts usager | `App\Entity\OrganizationContact` | `organization_contact.*` |
| Réservation | Réservation / Réservations | `App\Entity\Reservation` | `reservation.*` |
| Site | Site / Sites | `App\Entity\Venue` | `venue.*` |
| Salle | Salle / Salles | `App\Entity\Room` | `room.*` |
| Type de salle | Type de salle / Types de salle | `App\Entity\RoomType` | `room_type.*` |
| Configuration de salle | Configuration de salle / Configurations de salle | `App\Entity\RoomLayout` | `room_layout.*` |
| Tarification de salle | Tarification / Tarifications | `App\Entity\RoomPricing` | `room_pricing.*` |
| Type d’équipement | Type d’équipement / Types d’équipement | `App\Entity\EquipmentType` | `equipment_type.*` |
| Type d’événement | Type d’événement / Types d’événement | `App\Entity\EventType` | `event_type.*` |
| Type de service | Type de service / Types de service | `App\Entity\ServiceType` | `service_type.*` |
| Document de site | Document de site / Documents de site | `App\Entity\VenueDocument` | `venue_document.*` |
| Document de salle | Document de salle / Documents de salle | `App\Entity\RoomDocument` | `room_document.*` |
| Type de document (site) | Type de document / Types de documents | `App\Entity\SiteDocumentType` | `site_document_type.*` |
| Rôle | Super administrateur, etc. | `App\Entity\Role` | `roles.*` |
| Utilisateur | Utilisateur / Utilisateurs | `App\Entity\User` | N/A (libellés non centralisés) |

---

## 1. Organisation (Usager)

**Définition**

Structure cliente de l’application, pouvant effectuer des réservations et être facturée.

**Règles métier**

- Si **pays = France** et que l’organisation est de type **entreprise** ou **collectivité**, un **SIRET** est obligatoire.
- Si **pays = France** et que l’organisation est une **association immatriculée**, le **SIRET** est obligatoire.
- L’organisation peut disposer :
  - d’une **adresse de siège**,
  - d’une **adresse de facturation** (parfois identique),
  - de **contacts** (demandeur, payeur, etc.).

**Correspondance technique (actuelle)**

- Entité Symfony : `App\Entity\Organization`
- Clés de traduction : `organization.*`

---

## 2. Usager (concept)

**Définition**

Terme fonctionnel utilisé dans l’UI pour désigner l’organisation cliente.  
Il ne correspond pas à un **compte applicatif**.

**Correspondance technique (actuelle)**

- L’UI utilise le libellé **Usager** pour l’entité `Organization`.
- Il n’existe pas d’entité dédiée “Usager” distincte à ce stade.

---

## 3. Contact usager (demandeur / payeur)

**Définition**

Personne rattachée à un usager, identifiée comme **demandeur** ou **payeur**.

**Correspondance technique (actuelle)**

- Entité : `App\Entity\OrganizationContact`
- Rôles : `organization_contact.role.requester`, `organization_contact.role.payer`
- Clés de traduction : `organization_contact.*`

---

## 4. Site (Venue)

**Définition**

Lieu physique principal dans lequel se trouvent une ou plusieurs salles.

**Correspondance technique (actuelle)**

- Entité : `App\Entity\Venue`
- Clés de traduction : `venue.*`

---

## 5. Salle (Room)

**Définition**

Unité de réservation principale. Une salle appartient à un site et possède des capacités, équipements et règles.

**Correspondance technique (actuelle)**

- Entité : `App\Entity\Room`
- Clés de traduction : `room.*`

---

## 6. Réservation

**Définition**

Demande d’utilisation d’une salle par une organisation, sur une période donnée.

**Correspondance technique (actuelle)**

- Entité : `App\Entity\Reservation`
- Clés de traduction : `reservation.*`

---

## 7. Créneau (horaire)

**Définition**

Intervalle de temps associé à une réservation (date/heure de début et de fin).

**Statut d’implémentation**

- Pas d’entité dédiée à ce stade : les créneaux sont portés par la réservation (champs de dates).

---

## 8. Dossier de réservation

**Définition**

Regroupement logique de réservations et de documents pour une même demande.

**Statut d’implémentation**

- Non implémenté à ce stade.

---

## 9. Service associé (prestation)

**Définition**

Prestation complémentaire liée à une salle (restauration, support technique, etc.).

**Correspondance technique (actuelle)**

- Référentiel : `App\Entity\ServiceType` (`service_type.*`)
- Association salle ↔ service : `App\Entity\RoomService`

---

## 10. Facture

**Définition**

Document comptable émis à destination de l’organisation.

**Statut d’implémentation**

- Non implémenté à ce stade.

---

## 11. Devis

**Définition**

Proposition de tarification non comptable, préalable à une réservation.

**Statut d’implémentation**

- Non implémenté à ce stade.

---

## 12. Utilisateur (applicatif)

**Définition**

Compte applicatif authentifié, avec rôles et habilitations.

**Correspondance technique (actuelle)**

- Entité : `App\Entity\User`
- Rôles : `App\Entity\Role`

---

## 13. Profil / rôle

**Définition**

Jeu d’autorisations attribué à un utilisateur (ex. super administrateur, gestionnaire applicatif).

**Correspondance technique (actuelle)**

- Entité : `App\Entity\Role`
- Clés de traduction : `roles.*`

---

## 14. Journalisation (trace)

**Définition**

Enregistrement d’événements importants pour la traçabilité et la conformité.

**Correspondance technique (actuelle)**

- Entités : `AuthenticationLog`, `ResetPasswordLog`, `CronTaskRun`

---

## Référentiels et documents (compléments)

| Référentiel / Document | Entité / Classe | Clés de traduction |
| --- | --- | --- |
| Type de frais annexe | `App\Entity\AdditionalFeeType` | `additional_fee_type.*` |
| Frais annexe appliqué | `App\Entity\ReservationAdditionalFee` | `reservation_additional_fee.*` |
| Type d’équipement | `App\Entity\EquipmentType` | `equipment_type.*` |
| Équipement de site | `App\Entity\VenueEquipment` | `venue_equipment.*` |
| Équipement de salle | `App\Entity\RoomEquipment` | `room_equipment.*` |
| Type d’événement | `App\Entity\EventType` | `event_type.*` |
| Type de salle | `App\Entity\RoomType` | `room_type.*` |
| Configuration de salle | `App\Entity\RoomLayout` | `room_layout.*` |
| Tarification de salle | `App\Entity\RoomPricing` | `room_pricing.*` |
| Document de site | `App\Entity\VenueDocument` | `venue_document.*` |
| Document de salle | `App\Entity\RoomDocument` | `room_document.*` |
| Type de document (site) | `App\Entity\SiteDocumentType` | `site_document_type.*` |

---

## Utilisation attendue du glossaire

- Servir de **référence** lors de la création ou la modification :
  - d’entités,
  - de services,
  - de formulaires,
  - de templates.
- Vérifier que :
  - les noms de classes et propriétés restent **alignés** avec ces définitions ;
  - les fichiers de traduction reflètent bien ces concepts, sans changer leur sens.
- Être mis à jour à chaque **évolution métier significative**.
