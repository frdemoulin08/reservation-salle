# Stratégie de gestion des tableaux (listes) – Symfony 8 + Flowbite
**Application de réservation et de facturation des salles**  
*Document de cadrage technique – Backoffice, sécurité et réutilisabilité*

---

## 1. Objectifs

Cette stratégie définit un cadre commun pour l’implémentation des tableaux (listes de données) dans l’application, afin de :

- garantir un rendu homogène basé sur Flowbite / Tailwind ;
- assurer un traitement côté serveur (server-side processing) ;
- proposer des interactions **full AJAX** (tri, pagination, recherche, filtres) avec dégradation gracieuse ;
- limiter la dépendance à des bibliothèques JavaScript lourdes ;
- renforcer la sécurité et la lisibilité du code ;
- favoriser la réutilisabilité et éviter la duplication.

---

## 2. Principes généraux

Les tableaux du backoffice reposent sur les principes suivants :

- rendu HTML et logique métier gérés **côté serveur** (Symfony 8 + Twig) ;
- pagination, tri et filtrage gérés **exclusivement côté Symfony**, sur la base de requêtes Doctrine ;
- interactions (tri, changement de page, recherche) réalisées **en AJAX** via un JS léger maison (vanilla JS), sans jQuery ni Stimulus ;
- dégradation gracieuse : en l’absence de JavaScript, les liens fonctionnent en navigation classique (rechargement complet), sans perte fonctionnelle ;
- utilisation de Flowbite uniquement pour le style et l’ergonomie visuelle ;
- logique de sécurité et de filtrage centralisée dans les contrôleurs et repositories.

---

## 3. Fonctionnalités couvertes

La stratégie permet de couvrir les besoins suivants :

- pagination (links “Précédent / Suivant” + pages) ;
- tri simple et multi-colonnes (extensible) ;
- filtrage / recherche serveur (formulaires au-dessus du tableau, déclenchement AJAX sans bouton) ;
- export des données (CSV / XLSX) ;
- affichage cohérent Flowbite (tables, boutons, badges, etc.) ;
- rechargement **AJAX du tableau** (HTML fragment) dans un conteneur dédié.

---

## 4. Architecture technique

### 4.1 Vue d’ensemble

Chaque tableau repose sur trois briques principales :

1. un objet de paramètres de tableau (`TableParams`) ;
2. un service générique de pagination (`TablePaginator`) ;
3. des partials Twig Flowbite réutilisables (table + pagination) embarqués dans un conteneur “AJAX”.

Un JavaScript global léger :

- intercepte les clics sur les liens de tri et de pagination ;
- intercepte les soumissions de formulaires de filtre/recherche ;
- intercepte la saisie sur les champs de recherche (`[data-table-search]`) avec un seuil de caractères ;
- déclenche une requête `fetch()` vers la même route avec l’en-tête `X-Requested-With: XMLHttpRequest` ;
- remplace le contenu du conteneur du tableau par le fragment HTML renvoyé par Symfony ;
- met à jour l’URL (history API) pour conserver un état navigable.

---

## 5. Objet `TableParams`

### Rôle

`TableParams` encapsule les paramètres issus de la requête HTTP :

- page courante ;
- nombre d’éléments par page ;
- colonne de tri ;
- sens du tri ;
- filtres éventuels (recherche, rôles, statut, etc.).

### Responsabilités

- lecture et normalisation des paramètres depuis la requête (GET) ;
- définition de valeurs par défaut ;
- centralisation de la logique de validation basique (page min, direction, etc.).

### Avantages

- signature de contrôleurs simplifiée ;
- cohérence des paramètres entre tableaux ;
- évolution facilitée (ajout de filtres, multi-tri, etc.).

---

## 6. Service `TablePaginator`

### Rôle

Le service `TablePaginator` applique les paramètres de tri et de pagination à un `QueryBuilder` Doctrine.

### Responsabilités

- application du tri à partir d’une **liste blanche** de colonnes autorisées ;
- application de la pagination via un composant dédié (ex. Pagerfanta) ;
- retour d’un objet paginé standard.

### Sécurité

- seules les colonnes explicitement autorisées peuvent être utilisées pour le tri ;
- prévention des injections ou abus via paramètres d’URL (`sort`, `direction`, etc.).

---

## 7. Repositories

Chaque repository expose une méthode dédiée aux tableaux, par exemple :

- `createTableQb(TableParams $params)`.

Responsabilités du repository :

- définir l’alias Doctrine standard (ex. `u` pour `User`) ;
- appliquer les filtres métier à partir de `TableParams` (`filters`) ;
- ne pas gérer la pagination ni le tri générique (délégués au service `TablePaginator`).

---

## 8. Contrôleurs

### Rôle

Les contrôleurs :

- récupèrent les paramètres de tableau via `TableParams::fromRequest()` ;
- délèguent la construction de la requête au repository ;
- délèguent la pagination au service `TablePaginator` ;
- décident du template à renvoyer :
  - requête classique → page complète (layout + conteneur + tableau) ;
  - requête AJAX (`isXmlHttpRequest()`) → fragment HTML du tableau uniquement.

### Responsabilités

- contrôles d’accès (`isGranted`, voters, `denyAccessUnlessGranted`) ;
- passage explicite des paramètres à la vue ;
- aucune logique métier complexe dans la vue ou le JS.

---

## 9. Templates Twig Flowbite

### Partials réutilisables

Les éléments suivants sont factorisés dans `templates/_partials/table/` :

- en-tête de tableau (colonnes, liens de tri avec indicateur de direction) ;
- pagination (précédent / suivant / pages) ;
- état vide (« aucun résultat »).

### Conteneur AJAX

Chaque page de liste backoffice :

- définit un conteneur, par exemple :

  ```twig
  <div id="user-table-container" data-table-ajax="users">
      {% include 'admin/user/_table.html.twig' with { pager: pager, params: params } %}
  </div>
  ```

- le partial `_table.html.twig` inclut :
  - la table Flowbite,
  - la pagination,
  - éventuellement les messages “Aucun résultat”.

### Avantages

- rendu homogène Flowbite ;
- modification globale du style facilitée ;
- réduction massive du copier/coller ;
- compatibilité naturelle avec AJAX (rechargement du fragment) ;
- dégradation gracieuse en navigation classique.

---

## 10. JavaScript AJAX (vanilla)

### Principe

Un script unique backoffice :

- cible les conteneurs marqués (ex. `data-table-ajax="users"`) ;
- intercepte :
  - les clics sur les liens de tri et pagination (`[data-table-link]`) ;
  - les soumissions de formulaires de filtre/recherche (`[data-table-form]`) ;
  - la saisie sur les champs de recherche (`[data-table-search]`) avec un seuil ;
- exécute une requête AJAX (`fetch`) vers la même URL, avec un en-tête `X-Requested-With: XMLHttpRequest` ;
- remplace le contenu HTML du conteneur par la réponse ;
- met à jour l’URL dans la barre d’adresse (history API) pour conserver un état navigable et partageable.

Aucune dépendance à jQuery, Stimulus ou autres frameworks JS n’est requise.

### Recherche sans bouton

Les champs de recherche peuvent déclencher une requête AJAX sans bouton de validation :

- attributs utilisés : `data-table-search` et `data-min-length="3"` ;
- déclenchement à partir de **3 caractères** saisis (seuil configurable) ;
- si le champ est vidé, la liste est relancée avec les filtres restants ;
- les paramètres sont transmis via `filter[q]` (compatible `TableParams`).

Exemple :

```twig
<input type="search"
       name="filter[q]"
       data-table-search
       data-min-length="3"
       placeholder="Rechercher…">
```

---

## 11. Exports de données

### Principe

Les exports sont gérés **côté serveur**, via des routes dédiées de type :

- `/admin/.../export`.

Caractéristiques :

- reprise des mêmes filtres et tris que la liste (via `TableParams`) ;
- génération de fichiers CSV ou XLSX ;
- contrôle strict des colonnes exportées ;
- respect des droits d’accès ;
- lancement via un simple bouton ou lien (non AJAX).

Ce choix évite toute dépendance à des plugins front-end d’export et garantit une maîtrise totale du contenu exporté.

---

## 12. Sécurité

La stratégie respecte les principes suivants :

- toutes les données visibles dans les tableaux sont filtrées et paginées côté serveur ;
- aucun traitement de confiance n’est effectué côté client (AJAX ne fait que transporter du HTML généré par Symfony) ;
- les paramètres d’URL sont validés et contrôlés via `TableParams` et des listes blanches de colonnes triables ;
- les accès aux listes et aux exports sont soumis aux mêmes règles d’habilitation (vérifiées dans les contrôleurs).

Cette approche est particulièrement adaptée aux contextes soumis à audit de sécurité (DSI, CNIL).

---

## 13. Évolutivité

La stratégie permet des évolutions futures sans remise en cause du socle :

- ajout de filtres complexes (dates, statuts, texte libre, etc.) ;
- support du multi-tri en prolongeant `TableParams` et `TablePaginator` ;
- enrichissement progressif du comportement AJAX (indicateur de chargement, messages, etc.) ;
- intégration ponctuelle d’un composant JS avancé pour des cas très spécifiques, tout en conservant le socle commun.

---

## 14. Synthèse

- rendu Flowbite homogène ;
- traitement serveur complet (Symfony 8) ;
- interactions **full AJAX** (tri, pagination, filtres/recherche) avec dégradation gracieuse ;
- absence de dépendances JS lourdes (pas de jQuery, DataTables, Tabulator, Stimulus) ;
- briques réutilisables et maintenables (`TableParams`, `TablePaginator`, partials Twig + conteneurs AJAX + script JS unique) ;
- sécurité et auditabilité renforcées.

Cette stratégie constitue le standard projet pour l’ensemble des tableaux du backoffice.
