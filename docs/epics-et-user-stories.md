# Epics et user stories (par lot)

## Lot 1 — Mars 2026 (réservation ponctuelle)

### 1) Gestion du catalogue (salles, capacités, matériels, prestations)
- En tant qu’admin, je crée/modifie une salle avec capacité et contraintes pour référencer l’offre.
- En tant qu’admin, j’associe des matériels et prestations à une salle pour préparer la tarification.
- En tant qu’agent SPSL, je consulte la fiche d’une salle pour vérifier les conditions.

### 2) Gestion des usagers (SIRET, fiches, statuts)
- En tant qu’agent SPSL, je crée un usager avec SIRET et coordonnées pour initier une réservation.
- En tant qu’agent SPSL, je retrouve un usager via SIRET/nom pour éviter les doublons.
- En tant qu’admin, je définis des statuts/profils d’usager pour adapter la tarification.

### 3) Réservation ponctuelle (demande, validation, règles bloquantes)
- En tant qu’usager externe, je dépose une demande de réservation ponctuelle.
- En tant qu’agent SPSL, je valide/refuse une demande selon les règles.
- En tant qu’agent SPSL, je crée une réservation au nom d’un usager.
- En tant qu’admin, je paramètre des règles bloquantes imposant une rencontre physique.

### 4) Tarification et devis (règles, modification, validation)
- En tant qu’admin, je définis des règles tarifaires par salle/prestation/profil.
- En tant qu’agent SPSL, je génère un devis à partir d’une réservation.
- En tant qu’agent SPSL, je modifie un devis avant validation.

### 5) Plannings et disponibilité
- En tant qu’agent SPSL, je visualise le planning par site/salle.
- En tant qu’usager externe, je consulte les disponibilités avant demande.
- En tant qu’agent SPSL, je détecte les conflits de réservation.

### 6) Conventions et documents
- En tant qu’agent SPSL, je génère une convention depuis une réservation validée.
- En tant qu’agent SPSL, j’archive la convention associée à l’usager.

### 7) Sécurité et habilitations
- En tant qu’admin, je crée des rôles (admin, gestionnaire SPSL, conseiller technique, usager).
- En tant qu’admin, j’assigne des permissions par rôle.
- En tant qu’usager externe, j’accède de façon sécurisée à mon espace.

### 8) Données et reprise
- En tant qu’agent SPSL, j’importe des réservations à venir depuis un fichier.
- En tant qu’admin, je vérifie la qualité des données importées (doublons, manquants).

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
