# Référentiels métier et conventions de codes techniques

## 1. Objectif

Cette section définit les règles de gestion des référentiels métier (types de salle, types de document, statuts, etc.) et la convention de nommage des codes techniques utilisés dans l’application.

L’objectif est de garantir :
- la stabilité du code applicatif ;
- la souplesse de paramétrage métier ;
- la lisibilité et la maintenabilité du système dans le temps ;
- la compatibilité avec des usages transverses (facturation, exports, règles métier, BI, API).

---

## 2. Principe général

Chaque référentiel métier repose sur une dissociation stricte entre :

- un code technique :
  - utilisé par le code applicatif ;
  - stable dans le temps ;
  - non traduit ;
  - non modifiable par défaut via l’interface d’administration métier ;

- un libellé métier :
  - affiché à l’utilisateur ;
  - modifiable par un administrateur métier ;
  - traduisible si nécessaire ;
  - susceptible d’évoluer sans impact technique.

Le code technique constitue un contrat SI.  
Le libellé constitue un paramétrage métier.

---

## 3. Convention de nommage des codes techniques

### 3.1 Langue

Anglais uniquement.

Justifications :
- standard universel en développement logiciel ;
- cohérence avec PHP, Symfony, SQL, APIs ;
- absence de problématiques liées aux accents ou aux déclinaisons linguistiques.

---

### 3.2 Format

UPPER_SNAKE_CASE :
- mots séparés par un underscore (_)
- lettres majuscules uniquement

Exemples valides :
- MEETING_ROOM
- CONFERENCE_ROOM
- MULTI_PURPOSE_ROOM
- INVOICE_PAID
- DOCUMENT_CONTRACT

Exemples à proscrire :
- meeting-room (kebab-case)
- meetingRoom (camelCase)
- salle_reunion (français)
- SalleReunion (mix technique / métier)

---

### 3.3 Règle de stabilité

Un code technique ne doit jamais être modifié après sa création.

Toute évolution fonctionnelle doit passer par :
- une modification du libellé ;
- ou la création d’un nouveau code, si nécessaire.

---

## 4. Stockage en base de données

Les référentiels sont stockés en base afin de permettre un pilotage par des administrateurs métier, tout en garantissant la sécurité des éléments structurants.

### 4.1 Structure logique d’un référentiel

Chaque table de référentiel comporte a minima les champs suivants :

- id : identifiant technique
- code : code technique unique et stable (ex. MEETING_ROOM)
- label : libellé métier affiché à l’utilisateur
- description : description optionnelle
- is_active : indicateur d’activation
- display_order : ordre d’affichage
- is_system : indicateur de caractère structurant du code

---

### 4.2 Signification des champs clés

- code  
  Code technique stable utilisé par l’application.  
  Exemple : MEETING_ROOM

- label  
  Libellé métier affiché dans l’interface utilisateur.  
  Exemple : Salle de réunion

- description  
  Champ optionnel permettant de préciser l’usage métier.

- is_active  
  Permet d’activer ou de désactiver un type sans le supprimer.

- display_order  
  Ordre d’affichage dans les listes et formulaires.

- is_system  
  Indique un type structurant du système d’information :
  - non supprimable ;
  - non modifiable sur le plan technique ;
  - réservé aux usages métier critiques.

---

## 5. Droits et responsabilités

### 5.1 Administrateur métier

Via l’interface de paramétrage, un administrateur métier peut :

- modifier le libellé métier ;
- modifier la description ;
- activer ou désactiver un type ;
- modifier l’ordre d’affichage.

Il ne peut pas :
- modifier le code technique ;
- supprimer un type marqué comme structurant.

Le code technique doit être :
- soit non affiché dans l’interface ;
- soit affiché en lecture seule avec une mention explicative.

---

### 5.2 Équipe technique

L’équipe technique est responsable :
- de la création des codes techniques ;
- de leur utilisation dans les règles métier ;
- de leur cohérence avec les traitements applicatifs (facturation, exports, workflows).

La création ou l’évolution d’un code structurant passe par :
- une migration ;
- ou un écran d’administration technique dédié.

---

## 6. Création de nouveaux éléments par le métier

Deux cas sont distingués.

### 6.1 Référentiels à usage purement métier

Exemples :
- catégories informatives ;
- filtres d’affichage ;
- typologies non exploitées dans le code applicatif.

Ces éléments peuvent être créés par le métier sous réserve que :
- le code soit généré automatiquement selon la convention définie ;
- le code applicatif ne dépende pas explicitement de ces valeurs.

---

### 6.2 Référentiels utilisés dans la logique applicative

Exemples :
- types de salle impactant la facturation ;
- statuts déclenchant des workflows ;
- types de document soumis à des règles spécifiques.

Ces éléments doivent être :
- créés sous contrôle technique ;
- validés en amont ;
- intégrés explicitement dans le code applicatif.

---

## 7. Règle d’or

Le métier pilote les libellés et l’activation.  
Le système d’information garantit la stabilité des codes.

Cette règle s’applique à l’ensemble des référentiels de l’application.
