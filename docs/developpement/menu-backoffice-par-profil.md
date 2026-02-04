# Proposition de menu backoffice par profil

Document basé sur `docs/developpement/epics-et-user-stories.md` (Lot 1), structuré par profil utilisateur.

---

## Profils et principes

- **Super administrateur** : accès complet, pilotage des rôles, sécurité, données sensibles.
- **Administrateur métier** : configuration de l'offre (catalogue, règles, tarification).
- **Gestionnaire administratif** : traitement opérationnel (réservations, devis, planning, usagers).
- **Superviseur** : pilotage.
- **Usager (backoffice)** : pas d'accès backoffice (espace usager séparé).

---

## Menu par profil (Lot 1)

Le menu est défini dans `config/packages/backoffice_menu.yaml`.

### Super administrateur

- **Accueil**
- **Catalogue** : Salles, Services, Sites, Usages autorisés
- **Documents** : Pièces jointes réservation, Documents de salle, Documents de site, Types de documents
- **Paramétrage** : Configurations de salle, Habilitations, Pays, Rapports, Règles de réservation, Tarification, Types d’équipement, Types d’événement, Types de salle
- **Administration générale** : Journaux d’authentification, Journaux des tâches, Journaux de réinitialisation, Utilisateurs

### Administrateur métier

- **Accueil**
- **Catalogue** : Salles, Services, Sites, Usages autorisés
- **Documents** : Pièces jointes réservation, Documents de salle, Documents de site, Types de documents
- **Paramétrage** : Configurations de salle, Habilitations, Pays, Rapports, Règles de réservation, Tarification, Types d’équipement, Types d’événement, Types de salle
- **Administration métier** : section réservée au profil (actuellement vide)

### Gestionnaire administratif

- **Accueil**
- **Catalogue** : Salles, Services, Sites, Usages autorisés
- **Gestion** : Devis, Demandes à valider, Réservations, Usagers
- **Planning** : Vue salle, Vue site
- **Documents** : Pièces jointes réservation, Documents de salle, Documents de site, Types de documents

### Superviseur

- **Accueil**
- **Pilotage** : Analyse de la demande, Annulations / no-show, Exports / rapports, Performance des sites/salles, Prévisions, Rapports de synthèse, Recettes des locations, Statistiques d’usage, Tableau de bord KPI, Taux d’occupation

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
- Les actions **Ajouter / Éditer / Supprimer** des entrées du **Catalogue** sont réservées au rôle **Administrateur métier**.
