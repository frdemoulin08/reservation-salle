# Proposition de menu backoffice par profil

Document basé sur `docs/developpement/epics-et-user-stories.md` (Lot 1), structuré par profil utilisateur.

---

## Profils et principes

- **Super administrateur** : accès complet, pilotage des rôles, sécurité, données sensibles.
- **Administrateur métier** : configuration de l'offre (catalogue, règles, tarification). Profil porté par un gestionnaire administratif.
- **Gestionnaire administratif** : traitement opérationnel (réservations, devis, planning, usagers).
- **Superviseur** : pilotage.
- **Usager (backoffice)** : pas d'accès backoffice (espace usager séparé).

---

## Menu par profil (Lot 1)

Chaque profil affiche une structure à 2 niveaux :
- **Niveau 1** = entrée principale
- **Niveau 2** = sous-entrées rattachées

### Super administrateur

- **Tableau de bord**
  - Vue d'ensemble
- **Catalogue**
  - Sites
  - Salles
  - Équipements
  - Services
  - Types de salle
  - Configurations
  - Usages autorisés
- **Réservations**
  - Demandes
  - Devis
  - Réservations
  - Usagers (liste + recherche)
  - Contacts usager
- **Planning**
  - Vue site
  - Vue salle
- **Tarification**
  - Grilles tarifaires
  - Frais annexes
- **Documents**
  - Documents de site
  - Documents de salle
- **Sécurité**
  - Utilisateurs
  - Rôles et permissions
  - Journaux
- **Données**
  - Blocages de créneaux
- **Pilotage**
  - Statistiques d’usage
  - Rapports de synthèse

### Administrateur métier (gestionnaire administratif avec droits métier)

Ce profil **partage le menu du gestionnaire administratif** et peut disposer d'une **section dédiée** non redondante :
- **Administration métier**
  - Catalogue (édition)
    - Sites
    - Salles
    - Équipements
    - Services
    - Types de salle
    - Configurations
    - Usages autorisés
  - Tarification (édition)
    - Grilles tarifaires
    - Frais annexes
  - Documents (édition)
    - Documents de site
    - Documents de salle
  - Règles de réservation
    - Règles bloquantes

### Gestionnaire administratif

- **Tableau de bord**
  - Vue d'ensemble
- **Gestion**
  - Demandes à valider
  - Devis (création/modif)
  - Réservations
  - Usagers (liste + recherche)
- **Planning**
  - Vue site
  - Vue salle
- **Documents**
  - Pièces jointes réservation
- **Pilotage**
  - Statistiques d’usage
  - Rapports de synthèse

### Superviseur

- **Tableau de bord**
  - Vue d'ensemble
- **Pilotage**
  - Statistiques d’usage
  - Rapports de synthèse

---

## Notes d'évolution (Lot 2)

- Réservations récurrentes
- Facturation et suivi financier
- Reporting et statistiques
- Paramétrage avancé (gratuité exceptionnelle, versions de grilles, modèles de documents)

---

## Remarques

- Les libellés de menu sont à valider côté métier.
- Certaines rubriques (Documents, Tarification) peuvent être masquées pour les profils non concernés.
- Les espaces usagers restent séparés du backoffice.
