# Règles métier — Organisation et Site/Salle

Ce document synthétise les règles métier **actuelles** des modules :
- Organisation (Usager)
- Site (Venue)
- Salle (Room)

Il complète le référentiel central des règles : `docs/developpement/regles-gestion.md`.

---

## 1. Organisation (Usager)

### RG-ORG-001 — SIRET obligatoire (France)
**Règle**
Le SIRET est obligatoire si :
- Pays du siège = France
- ET Type de structure = Entreprise ou Collectivité
- OU Type de structure = Association **immatriculée**

**Validation**
- 14 chiffres + contrôle Luhn
- Front + back

**Implémentation**
- `src/Entity/Organization.php`
- `assets/js/helpers/company-lookup.js`
- `templates/admin/organizations/_siret_field.html.twig`

---

### RG-ORG-002 — Compatibilité Type / Nature juridique
**Règle**
La nature juridique doit être compatible avec le type de structure.

**Référentiel**
- `src/Reference/OrganizationLegalNature.php`

**Implémentation**
- `src/Entity/Organization.php`
- `src/Form/OrganizationType.php`
- `assets/js/helpers/company-lookup.js`

---

### RG-ORG-003 — Enrichissement via SIRET
**Règle**
Si le SIRET est valide :
- appel AJAX à l’API d’enrichissement
- pré-remplissage des champs (modifiable)
- aucune synchronisation automatique ultérieure

**Implémentation**
- `src/Service/CompanyLookupService.php`
- `src/Controller/Api/CompanyLookupController.php`
- `assets/js/helpers/company-lookup.js`

---

### RG-ORG-004 — Adresse de facturation = siège
**Règle**
Si l’option est activée, l’adresse de facturation reprend l’adresse du siège.

**Implémentation**
- `src/Form/OrganizationType.php` (PRE_SUBMIT)

---

### RG-ORG-005 — Suppression d’un contact utilisé
**Règle**
Un contact d’organisation ne peut pas être supprimé s’il est lié à une réservation.

**Implémentation**
- `src/Controller/Administration/OrganizationContactController.php`

---

## 2. Site (Venue)

### RG-SITE-001 — Champs obligatoires
**Règle**
À la création d’un site, sont obligatoires :
- nom
- description (max 500)
- adresse : ligne 1, code postal, pays, commune

**Implémentation**
- `src/Form/VenueType.php`
- Tests : `tests/Functional/Administration/VenueValidationTest.php`

---

### RG-SITE-002 — Identifiant public
**Règle**
Chaque site possède un `publicIdentifier` au format UUID v4, utilisé dans les routes de détail.

**Implémentation**
- `src/Entity/Venue.php`
- `src/Controller/Administration/VenueController.php`

---

### RG-SITE-003 — Photos de site
**Règle**
- Upload protégé par CSRF
- Le libellé de photo est obligatoire

**Implémentation**
- `src/Controller/Administration/VenueController.php`
- Tests : `tests/Functional/Administration/VenuePhotoTest.php`

---

## 3. Salle (Room)

### RG-SALLE-001 — Rattachement à un site
**Règle**
Une salle doit être rattachée à un site.

**Implémentation**
- `src/Entity/Room.php` (relation `ManyToOne` non nullable)

---

### RG-SALLE-002 — Identifiant public
**Règle**
Chaque salle possède un `publicIdentifier` au format UUID v4, utilisé dans les routes de détail.

**Implémentation**
- `src/Entity/Room.php`
- `src/Controller/Administration/RoomController.php`

---

### RG-SALLE-003 — Photos de salle
**Règle**
- Upload protégé par CSRF
- Le libellé de photo est obligatoire

**Implémentation**
- `src/Controller/Administration/RoomController.php`
- Tests : `tests/Functional/Administration/RoomPhotoTest.php`

---

## 4. Référentiels associés (Site/Salle)

### RG-REF-001 — Code des référentiels
**Règle**
Les codes des référentiels (types, configurations) sont :
- obligatoires
- au format `UPPER_SNAKE_CASE`
- immuables après création

**Référentiels concernés**
- Type de salle (`RoomType`)
- Configuration de salle (`RoomLayout`)
- Type d’équipement (`EquipmentType`)

**Implémentation**
- Tests : `tests/Functional/Administration/RoomTypeValidationTest.php`, `tests/Functional/Administration/RoomLayoutValidationTest.php`, `tests/Functional/Administration/EquipmentTypeValidationTest.php`
