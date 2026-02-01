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
  - Configurations
  - Équipements
  - Salles
  - Services
  - Sites
  - Types de salle
  - Usages autorisés
- **Réservations**
  - Contacts usager
  - Demandes
  - Devis
  - Réservations
  - Usagers
- **Planning**
  - Vue salle
  - Vue site
- **Tarification**
  - Frais annexes
  - Grilles tarifaires
- **Documents**
  - Documents de salle
  - Documents de site
- **Sécurité**
  - Journaux
  - Rôles et permissions
  - Utilisateurs
- **Données**
  - Blocages de créneaux
- **Pilotage**
  - Rapports de synthèse
  - Statistiques d’usage

### Administrateur métier (gestionnaire administratif avec droits métier)

Ce profil **partage le menu du gestionnaire administratif** et peut disposer d'une **section dédiée** non redondante :
- **Administration métier**
  - Catalogue (édition)
    - Configurations
    - Équipements
    - Salles
    - Services
    - Sites
    - Types de salle
    - Usages autorisés
  - Tarification (édition)
    - Frais annexes
    - Grilles tarifaires
  - Documents (édition)
    - Documents de salle
    - Documents de site
  - Règles de réservation
    - Règles bloquantes

### Gestionnaire administratif

- **Tableau de bord**
  - Vue d'ensemble
- **Gestion**
  - Demandes à valider
  - Devis (création/modif)
  - Réservations
  - Usagers
- **Planning**
  - Vue salle
  - Vue site
- **Documents**
  - Pièces jointes réservation
- **Pilotage**
  - Rapports de synthèse
  - Statistiques d’usage

### Superviseur

- **Tableau de bord**
  - Vue d'ensemble
- **Pilotage**
  - Rapports de synthèse
  - Statistiques d’usage

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
