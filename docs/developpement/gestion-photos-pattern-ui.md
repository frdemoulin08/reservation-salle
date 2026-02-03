# 📸 Gestion des photos – Pattern UI « Drag & Drop + Galerie inline »

## 1. Objectif du pattern

Ce pattern vise à permettre à un administrateur de :

- visualiser immédiatement les photos déjà associées à un site ;
- ajouter rapidement de nouvelles photos via un glisser-déposer ;
- effectuer des actions simples (aperçu, suppression) sans quitter la page.

Il est conçu pour s’intégrer dans une page déjà dense, sans surcharge visuelle.

---

## 2. Principe général

Le pattern repose sur deux éléments affichés inline :

1. une zone de dépôt (Drag & Drop) compacte, en tête de section ;
2. une galerie de vignettes affichant les photos existantes.

L’ensemble est visible en permanence, sans modal ni navigation secondaire.

---

## 3. Structure UI recommandée

┌───────────────────────────────────────┐
│  📤 Déposer des photos ici             │
│  ou cliquer pour sélectionner          │
└───────────────────────────────────────┘

┌──────┬──────┬──────┬──────┐
│ 🖼️   │ 🖼️   │ 🖼️   │ 🖼️   │
├──────┼──────┼──────┼──────┤
│ 🖼️   │ 🖼️   │  +   │      │
└──────┴──────┴──────┴──────┘

---

## 4. Composants Flowbite mobilisés

### 4.1 Zone d’upload

- Composant : File Upload – Drag & Drop (Flowbite v4)
- Fonction :
  - accepter plusieurs fichiers image ;
  - déclencher un upload asynchrone (AJAX).

Bonnes pratiques UX :
- message explicite (ex. « JPG, PNG – max 5 Mo ») ;
- retour visuel lors de l’upload (loader / spinner).

---

### 4.2 Galerie de photos

- Grille responsive Tailwind :
  - `grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4`
- Chaque photo est affichée sous forme de carte cliquable.

Actions disponibles sur une photo (au survol) :
- 👁️ Aperçu (modal Flowbite) ;
- 🗑️ Suppression (avec confirmation).

Optionnel :
- badge discret :
  - « Photo principale »
  - ou type (« Façade », « Salle », etc.).

---

## 5. Règles UX recommandées

- Les photos existantes doivent être visibles sans interaction préalable.
- L’ajout de nouvelles photos ne doit pas recharger la page.
- Une tuile « + » peut être affichée en fin de galerie pour ouvrir le sélecteur de fichiers.
- En cas d’échec d’upload :
  - message clair ;
  - aucune photo fantôme dans la galerie.

---

## 6. Intégration technique (Symfony)

### 6.1 Organisation Twig

```twig
<section class="mt-8">
  <h3 class="text-lg font-semibold mb-4">Photos du site</h3>

  {# Zone d’upload #}
  {% include 'components/photo_dropzone.html.twig' %}

  {# Galerie #}
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
    {% for photo in site.photos %}
      {% include 'components/photo_card.html.twig' with { photo: photo } %}
    {% endfor %}
  </div>
</section>
```

---

### 6.2 Principes backend

- upload traité via un endpoint dédié :  
  `POST /site/{id}/photos` ;
- stockage des fichiers via la stratégie de gestion documentaire retenue ;
- persistance en base des métadonnées suivantes :
  - chemin du fichier ;
  - type = `photo` ;
  - ordre d’affichage ;
  - métadonnées éventuelles (taille, mime-type, label).

---

## 7. Évolutions possibles (hors V1)

- réorganisation des photos par glisser-déposer ;
- définition d’une photo principale ;
- lazy loading ;
- limitation configurable du nombre de photos par site.

---

## 8. Conclusion

Le pattern « Drag & Drop + Galerie inline » constitue un excellent compromis entre :

- simplicité d’usage pour l’administrateur ;
- lisibilité dans une page déjà chargée ;
- facilité d’implémentation avec Flowbite v4 et Symfony.

👉 Pattern recommandé pour la V1 de l’application.
