# Spécification du schéma de données – Lot 1 (pour Codex)

---

## 0. Contexte et périmètre

* Projet : **Réservation de salles**
* Lot concerné : **Lot 1 – Réservation ponctuelle (mars 2026)**
* Stack cible : **Symfony 8 + Doctrine ORM + MySQL**
* Langue technique : **anglais** (entités, champs)
* Langue métier / UI : **français** (via `messages.fr.yaml`)

Le modèle doit :

* couvrir les besoins fonctionnels du Lot 1,
* être extensible pour le Lot 2 (réservation récurrente, facturation),
* rester simple et maintenable.

---

## 1. Conventions générales

### 1.1. Nommage

* **Entités Doctrine** : `PascalCase`

  * `Organization`, `Venue`, `Room`, `Reservation`, …
* **Tables SQL** : `snake_case` au pluriel

  * `organizations`, `venues`, `rooms`, …
* **Champs PHP** : `camelCase`
* **Champs SQL** : `snake_case`
* **Embeddables** : objets sans table dédiée (`Address`)

### 1.2. Mapping métier → technique

| Métier                 | Entité technique                                 |
| ---------------------- | ------------------------------------------------ |
| Usager                 | `Organization`                                   |
| Contact usager         | `OrganizationContact`                            |
| Site                   | `Venue`                                          |
| Salle                  | `Room`                                           |
| Type de salle          | `RoomType`                                       |
| Configuration de salle | `RoomLayout`                                     |
| Équipement             | `EquipmentType` / `RoomEquipment`                |
| Service                | `ServiceType` / `RoomService`                    |
| Usage autorisé         | `UsageType` / `RoomUsage`                        |
| Document de salle      | `RoomDocument`                                   |
| Type d’événement       | `EventType`                                      |
| Réservation            | `Reservation`                                    |
| Tarification           | `RoomPricing`                                    |
| Frais annexes          | `AdditionalFeeType` / `ReservationAdditionalFee` |
| Utilisateur applicatif | `User`                                           |

---

## 2. Adressage – Embeddable `Address`

### 2.1. Principe

* Les adresses sont **factorisées** via un **Embeddable Doctrine**.
* Il **n’existe pas d’entité `Address` autonome**.
* Les champs d’adresse sont **dépliés dans la table** de l’entité porteuse.

Ce choix permet :

* d’éviter une table supplémentaire,
* de gérer plusieurs adresses dans une même entité (siège / facturation),
* de rester compatible avec la **BAN** (France) et des adresses libres (Belgique).

### 2.2. Spécification de l’Embeddable

```php
#[ORM\Embeddable]
class Address
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $line1 = null;       // n° + voie

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $line2 = null;       // complément

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $line3 = null;       // complément (suite)

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $country = 'FR';     // ISO 3166-1 (FR, BE, …)

    // Données techniques d’adressage
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $source = null;      // BAN | MANUAL | OTHER

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalId = null;  // id BAN ou autre référentiel

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;
}
```

### 2.3. Règles d’usage

* `country = FR` → auto-complétion possible via **BAN**
* `country ≠ FR` → saisie libre (Belgique notamment)
* `source = BAN` uniquement pour les adresses françaises

---

## 3. Entités principales (Lot 1)

### 3.1. Organization (Usager)

* Porte **deux adresses** :

  * `headOfficeAddress`
  * `billingAddress`
* Les deux adresses peuvent avoir **les mêmes valeurs**, sans être liées techniquement.

Champs clés :

* `siret`
* `legalName`
* `displayName`
* `legalNature`
* `billingSameAsHeadOffice` (booléen)

Relations :

* `Organization` 1—N `OrganizationContact`
* `Organization` 1—N `Reservation`

---

### 3.2. OrganizationContact

* Rattaché à une `Organization`
* Rôle : `REQUESTER` ou `PAYER`
* Informations de contact (nom, prénom, email, téléphone)

---

### 3.3. Venue (Site)

* Porte **une adresse unique** (`Address` embeddable)
* Les salles héritent implicitement de l’adresse du site

Champs clés :

* `name`
* `address`
* `description`
* `publicTransportAccess`
* `parkingType`, `parkingCapacity`

Relations :

* `Venue` 1—N `Room`

---

### 3.4. Room (Salle)

* Rattachée à un `Venue`
* **Pas d’adresse propre** (héritage via le site)

Champs clés :

* capacités (`seatedCapacity`, `standingCapacity`)
* accessibilité / sécurité (PMR, ERP, issues, etc.)
* conditions d’utilisation (durées, autorisations)

Relations :

* M2M `RoomType`
* M2M `RoomLayout`
* 1—N `RoomEquipment`
* 1—N `RoomService`
* M2M `UsageType`
* 1—N `RoomDocument`
* 1—N `Reservation`
* 1—N `RoomPricing`

---

### 3.5. Référentiels

Entités simples avec `code` + `label` :

* `RoomType`
* `RoomLayout`
* `EquipmentType` (avec `category`)
* `ServiceType`
* `UsageType`
* `AdditionalFeeType`

Ces entités sont **administrables** et utilisées en relations.

---

### 3.6. Reservation

* Rattache :

  * une `Room`
  * une `Organization`
  * optionnellement un `OrganizationContact`

Champs clés :

* `startDate`, `endDate`
* `status` (DRAFT, PENDING, CONFIRMED, CANCELLED, CLOSED)
* `ticketingType` (NONE, UNDER_5, OVER_5)
* `securityDeposit`

Relations :

* 1—N `ReservationAdditionalFee`

---

### 3.7. Tarification et frais annexes

* `RoomPricing` : tarifs par type d’usager
* `AdditionalFeeType` : référentiel
* `ReservationAdditionalFee` : montant appliqué à une réservation

---

## 4. Règles importantes pour Codex

1. **Ne pas créer d’entité `Address` autonome**.
2. Utiliser `Address` uniquement comme **Embeddable**.
3. Les adresses françaises peuvent être liées à la **BAN**.
4. Les adresses belges sont stockées sans référentiel externe.
5. Les libellés UI doivent utiliser `messages.fr.yaml`.
6. Le schéma doit être extensible pour le Lot 2 (facturation).

---

## 5. Résumé exécutif

* Modèle volontairement **sobre et robuste**.
* Factorisation de l’adresse sans sur‑normalisation.
* Compatibilité BAN + international.
* Alignement clair entre métier, données et UI.
