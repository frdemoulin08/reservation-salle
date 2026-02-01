# Epics et user stories (par lot)

## Lot 1 — Mars 2026 (réservation ponctuelle)

### 1) Gestion du catalogue (salles, capacités, matériels, prestations)
- En tant qu’administrateur métier, je crée/modifie une salle avec ses caractéristiques pour référencer l’offre.
- En tant qu’administrateur métier, je crée/modifie des matériels associés aux sites et leurs caractéristiques pour référencer l’offre.
- En tant qu’administrateur métier, je peux référencer des matériels associés aux salles et leurs caractéristiques (nombre disponible en fonction des capacités de la salle, pris dans le stock complet du site non comptabilisé dans l’application).
- En tant qu’administratif, je consulte la fiche d’une salle pour vérifier les conditions.

### 2) Gestion des usagers (SIRET, fiches, statuts)
- En tant qu’administratif ou superadministrateur, je consulte la liste des comptes utilisateurs avec la possibilité de les désactiver.
- En tant qu’administratif, je retrouve un usager via SIRET/nom pour éviter les doublons.
En tant qu’administrateur métier, je vérifie et modifie au besoin des statuts/profils d’usager pour adapter la tarification.
- En tant que Superadministrateur, je peux supprimer les données personnelles relatives à un compte usager.

### 3) Réservation ponctuelle (demande, validation, règles bloquantes)
- En tant qu’usager connecté, je dépose une demande de pré réservation ponctuelle.
- En tant qu’administratif, je valide/refuse une demande selon les règles.
- En tant qu’administrateur métier, je définis des règles bloquantes imposant une rencontre physique.
- En tant qu’usager, je peux recevoir une alerte lors du dépôt de ma demande
- En tant qu’usager, je peux recevoir mon devis par email
- En tant qu’usager, je peux recevoir une alerte à l’approche de l’échéance de la fin de validité du devis non retourné (règle de gestion à définir).
- En tant qu’administratif, je peux recevoir une alerte lors du retour du devis signé
- En tant qu’usager, je peux recevoir une alerte lors de la validation finale de ma réservation
- En tant qu’usager, je peux ajouter différentes prestation lors de ma pré réservation, consultable sous forme d’un panier affichant chaque élément réservé.

### 4) Tarification et devis (règles, modification, validation)
- En tant qu’administrateur métier, je définis des règles tarifaires par salle/prestation/profil.
- En tant qu’administratif, je valide un devis à partir d’une demande de réservation.
- En tant qu’administratif, je modifie un devis avant validation.

### 5) Plannings et disponibilité
- En tant qu’administratif, je visualise le planning par site/salle.
- En tant qu’usager, je consulte les disponibilités avant demande.
- En tant qu’usager, je suis informé de l’indisponibilité d’une salle sur un créneau souhaité.
- En tant qu’usager, je peux réserver un créneau d’une demi-journée au minimum. Pour la V2, je pourrai réserver un créneau horaire.

### 6) Conventions et documents
- Dans la V2, en tant qu’administratif, je génère une convention depuis une réservation validée.
- Dans la v2, en tant qu’administratif, j’archive la convention associée à l’usager.

### 7) Sécurité et habilitations
- En tant que superadministrateur, je crée des rôles (admin, gestionnaire SPSL, conseiller technique, usager).
- En tant que superadministrateur, j’assigne des permissions par rôle.
- En tant que tout utilisateur de l’application, j’accède de façon sécurisée à mon espace dédié.

### 8) Données et reprise
- En tant qu’administratif, je peux bloquer un créneau déjà réservé.

## Lot 2 — Septembre 2026 (réservation récurrente et facturation)

### 9) Réservation récurrente
- En tant qu’agent SPSL, je crée une réservation récurrente avec règles d’exception.
- En tant qu’agent SPSL, je modifie/suspends une série sans casser l’historique.
- En tant qu’usager externe, je consulte mon calendrier récurrent.

### 10) Facturation et suivi financier
- En tant qu’agent SPSL, je génère une facture interne à partir d’un devis accepté.
- En tant qu’agent SPSL, je trace acomptes et cautions dans la réservation.
- En tant qu’agent SPSL, je consulte l’état de paiement et d’encaissement.

### 11) Reporting et statistiques
- En tant qu’admin, je consulte un tableau de bord d’activité par site.
- En tant qu’agent SPSL, je génère un rapport annuel consolidé.
- En tant qu’admin, j’exporte les données pour analyse.

### 12) Administration et paramétrage avancé
- En tant qu’admin, je gère des règles de gratuité exceptionnelle.
- En tant qu’admin, je versionne les grilles tarifaires dans le temps.
- En tant qu’admin, je paramètre des modèles de documents.
