# Schéma de données détaillé — Lot 1

Ce document présente le schéma détaillé (entités, champs, relations) pour le lot 1 du projet de réservation de salles.
Il est conforme aux règles du document de référence `docs/donnees/modelisation-donnees.md`.

---

## Embeddable Address (aucune entité Address)

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| line1 | string(255) | yes | Numéro + voie |
| line2 | string(255) | yes | Complément |
| line3 | string(255) | yes | Complément (suite) |
| postalCode | string(10) | yes |  |
| city | string(100) | yes |  |
| country | string(2) | yes | ISO 3166-1, défaut FR |
| source | string(50) | yes | BAN / MANUAL / OTHER |
| externalId | string(255) | yes | ID BAN ou autre référentiel |
| latitude | float | yes |  |
| longitude | float | yes |  |

---

## Organization

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| siret | string | yes | unique recommandé |
| legalName | string | no |  |
| displayName | string | no |  |
| legalNature | string | yes |  |
| organizationType | string | yes | public / private |
| billingSameAsHeadOffice | bool | no | défaut false |
| headOfficeAddress | Address | yes | embeddable |
| billingAddress | Address | yes | embeddable |

Relations
- Organization 1—N OrganizationContact
- Organization 1—N Reservation

---

## OrganizationContact

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| organization | FK | no | ManyToOne Organization |
| role | string | no | REQUESTER / PAYER |
| title | string | yes | civilité |
| jobTitle | string | yes | fonction |
| firstName | string | no |  |
| lastName | string | no |  |
| email | string | no |  |
| phone | string | yes |  |

---

## Venue

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| name | string | no |  |
| description | text | yes |  |
| publicTransportAccess | text | yes |  |
| parkingType | string | yes |  |
| parkingCapacity | int | yes |  |
| contactDetails | text | yes |  |
| referenceContactName | string | yes |  |
| deliveryAccess | text | yes |  |
| accessMapUrl | string | yes |  |
| houseRules | text | yes | règlement intérieur |
| address | Address | yes | embeddable |

Relations
- Venue 1—N Room
- Venue 1—N VenueDocument
- Venue 1—N VenueEquipment

---

## VenueDocument

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| venue | FK | no | ManyToOne Venue |
| label | string | no |  |
| filePath | string | no |  |
| mimeType | string | yes |  |
| type | string | yes | photo / plan / other |

---

## VenueEquipment

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| venue | FK | no | ManyToOne Venue |
| equipmentType | FK | no | ManyToOne EquipmentType |
| maxQuantity | int | yes |  |
| isIncluded | bool | no | défaut true |

---

## Room

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| venue | FK | no | ManyToOne Venue |
| name | string | no |  |
| description | text | yes |  |
| surfaceArea | decimal(10,2) | yes | m² |
| seatedCapacity | int | yes |  |
| standingCapacity | int | yes |  |
| isPmrAccessible | bool | no | défaut false |
| hasElevator | bool | no | défaut false |
| hasPmrRestrooms | bool | no | défaut false |
| hasEmergencyExits | bool | no | défaut false |
| isErpCompliant | bool | no | défaut false |
| erpType | string | yes |  |
| erpCategory | string | yes |  |
| securityStaffRequired | bool | no | défaut false |
| openingHoursSchema | text | yes |  |
| minRentalDurationMinutes | int | yes |  |
| maxRentalDurationMinutes | int | yes |  |
| bookingLeadTimeDays | int | yes |  |
| cateringAllowed | bool | no | défaut false |
| alcoholAllowed | bool | no | défaut false |
| alcoholLegalNotice | text | yes |  |
| musicAllowed | bool | no | défaut false |
| sacemRequired | bool | no | défaut false |

Relations
- Room M2M RoomType
- Room M2M RoomLayout
- Room 1—N RoomEquipment
- Room 1—N RoomService
- Room M2M UsageType (via RoomUsage)
- Room 1—N RoomDocument
- Room 1—N RoomPricing
- Room 1—N Reservation

---

## RoomDocument

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| room | FK | no | ManyToOne Room |
| label | string | no |  |
| filePath | string | no |  |
| mimeType | string | yes |  |
| type | string | yes | photo / plan / other |

---

## RoomEquipment

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| room | FK | no | ManyToOne Room |
| equipmentType | FK | no | ManyToOne EquipmentType |
| maxQuantity | int | yes |  |
| exclusiveToRoom | bool | no | défaut false |
| isIncluded | bool | no | défaut true |

---

## RoomService

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| room | FK | no | ManyToOne Room |
| serviceType | FK | no | ManyToOne ServiceType |
| isIncluded | bool | no | défaut true |

---

## RoomUsage

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| room | FK | no | ManyToOne Room |
| usageType | FK | no | ManyToOne UsageType |

---

## RoomPricing

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| room | FK | no | ManyToOne Room |
| priceCategory | string | no |  |
| hourlyRate | decimal(10,2) | yes |  |
| dailyRate | decimal(10,2) | yes |  |
| currency | string(3) | no | défaut EUR |

---

## Reservation

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| room | FK | no | ManyToOne Room |
| organization | FK | no | ManyToOne Organization |
| organizationContact | FK | yes | ManyToOne OrganizationContact |
| eventType | FK | yes | ManyToOne EventType |
| startDate | datetime | no |  |
| endDate | datetime | no |  |
| status | string | no | DRAFT, PENDING, CONFIRMED, CANCELLED, CLOSED |
| ticketingType | string | no | NONE, UNDER_5, OVER_5 |
| securityDeposit | decimal(10,2) | yes |  |
| comment | text | yes |  |

Relations
- Reservation 1—N ReservationAdditionalFee

---

## ReservationAdditionalFee

| Champ | Type | Nullable | Notes |
| --- | --- | --- | --- |
| id | PK | no |  |
| reservation | FK | no | ManyToOne Reservation |
| additionalFeeType | FK | no | ManyToOne AdditionalFeeType |
| amount | decimal(10,2) | no |  |
| label | string | yes |  |

---

## Référentiels (code + label)

| Entité | Champs | Notes |
| --- | --- | --- |
| RoomType | code (unique), label |  |
| RoomLayout | code (unique), label |  |
| EquipmentType | code (unique), label, category |  |
| ServiceType | code (unique), label |  |
| UsageType | code (unique), label |  |
| AdditionalFeeType | code (unique), label |  |
| EventType | code (unique), label |  |
