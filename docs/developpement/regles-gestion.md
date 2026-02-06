# Règles de gestion (RG)

Ce document centralise les règles de gestion appliquées dans l'application. Les RG sont **codées** dans l'application (pas stockées en base) et documentées ici pour garantir la cohérence fonctionnelle.

## RG-ORG-001 — Obligation du SIRET

**Règle**
Le SIRET est obligatoire si :
- Pays du siège = France
- ET Type de structure = Entreprise ou Collectivité
- OU Type de structure = Association **immatriculée**

**Conséquences**
- Validation côté front + côté back.
- Appel API d’enrichissement uniquement si SIRET valide (14 chiffres + Luhn).

**Implémentation**
- Validation serveur : `src/Entity/Organization.php`
- UI et validation front : `assets/js/helpers/company-lookup.js`
- Formulaire : `templates/admin/organizations/_siret_field.html.twig`

## RG-ORG-002 — Compatibilité Type / Nature juridique

**Règle**
La nature juridique doit être **compatible** avec le type de structure.

**Référentiel**
La liste des natures juridiques et leur compatibilité est définie dans :
- `src/Reference/OrganizationLegalNature.php`

**Implémentation**
- Validation serveur : `src/Entity/Organization.php`
- Filtrage des options côté front : `assets/js/helpers/company-lookup.js`
- Formulaire : `src/Form/OrganizationType.php`

## RG-ORG-003 — Enrichissement automatique via SIRET

**Règle**
Si le pays est France et que le SIRET est valide :
- appel AJAX vers l’API interne
- pré-remplissage des champs (modifiable)
- aucune synchronisation automatique ultérieure

**Références**
- Cadrage : `docs/developpement/gestion-enrichissement-entreprise.md`

## RG-ORG-004 — Adresse de facturation identique au siège

**Règle**
Si l’option “Adresse de facturation identique au siège” est activée :
- les champs de facturation reprennent automatiquement les valeurs du siège

**Implémentation**
- Formulaire : `src/Form/OrganizationType.php` (PRE_SUBMIT)

## RG-ORG-005 — Suppression d’un contact utilisé dans une réservation

**Règle**
Un contact d’organisation ne peut pas être supprimé s’il est déjà associé à une réservation.

**Implémentation**
- Contrôleur : `src/Controller/Administration/OrganizationContactController.php`

## RG-USR-001 — Définition d’un usager

**Règle**
Un “usager” est un compte **sans rôle d’administration**.

**Conséquences**
- Les listes “Usagers” excluent les comptes possédant un rôle admin.
- La création d’un usager ne définit aucun rôle d’administration.

**Implémentation**
- Filtrage liste : `src/Repository/UserRepository.php`
- Formulaire : `src/Form/UsagerType.php`

---

Pour ajouter une nouvelle RG :
- lui attribuer un identifiant (ex. RG-ORG-006)
- préciser la règle, l’impact UX et l’implémentation
