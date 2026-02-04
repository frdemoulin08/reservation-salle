---
title: "MCD Sites (métier)"
last_updated: "2026-02-04"
---

# MCD — Sites (métier)

Tableau métier des champs **Sites** avec libellés en français et types fonctionnels.
Les relations (adresse, photos, documents, équipements, salles) sont incluses dans le tableau.

| Champ (libellé métier) | Type (métier) | Obligatoire | Commentaire |
|---|---|---|---|
| Identifiant interne | Identifiant numérique | Oui (technique) | ID auto. |
| Identifiant public | Identifiant unique (UUID) | Oui | Utilisé dans les URLs et échanges. |
| Nom du site | Texte court | Oui | Nom d’affichage. |
| Texte descriptif | Texte long | Oui | 500 caractères max. |
| Référent SPSL | Référence utilisateur | Non | Utilisateur lié (profil admin métier / gestionnaire applicatif). |
| Accès transports en commun | Texte long | Non | Proximité / indications. |
| Type de parking | Liste de valeurs | Non | Gratuit / payant. |
| Nombre de places de parking | Nombre entier | Non | Capacité. |
| Accès livraison | Texte long | Non | Indications logistiques. |
| Plan / indication d’accès | Lien (URL) | Non | Pas encore saisi côté formulaire. |
| Règlement intérieur | Texte long | Non | Pas encore saisi côté formulaire. |
| Date de création | Date & heure | Oui (technique) | Auto. |
| Date de mise à jour | Date & heure | Oui (technique) | Auto. |
| Adresse – ligne 1 | Texte court | Oui | Adresse principale. |
| Adresse – ligne 2 | Texte court | Non | Complément. |
| Adresse – ligne 3 | Texte court | Non | Complément (suite). |
| Adresse – code postal | Texte court | Oui | |
| Adresse – ville | Texte court | Oui | |
| Adresse – pays | Code pays (ISO2) | Oui | |
| Adresse – source | Texte court | Non | BAN / saisie manuelle. |
| Adresse – identifiant externe | Texte court | Non | ID BAN. |
| Adresse – latitude | Coordonnée | Non | |
| Adresse – longitude | Coordonnée | Non | |
| Photos | Relation multiple | Non | 1 site → N photos. |
| Documents | Relation multiple | Non | 1 site → N documents. |
| Salles | Relation multiple | Non | 1 site → N salles. |
| Équipements | Relation multiple | Non | 1 site → N équipements. |
