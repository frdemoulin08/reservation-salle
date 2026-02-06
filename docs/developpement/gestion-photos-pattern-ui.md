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
- 🗑️ Suppression (avec confirmation) ;
- ✏️ Renommage du libellé (inline).

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

### 6.1 Organisation Twig (implémentation actuelle)

```twig
<section class="mt-6 rounded-2xl border border-default bg-neutral-primary-soft p-6 shadow-xs">
  <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-body">Photos</h2>

  <div class="mt-4 rounded-base border border-default bg-neutral-primary p-4"
       data-photo-dropzone
       data-upload-url="{{ path('app_admin_venues_photo_upload', { publicIdentifier: venue.publicIdentifier }) }}"
       data-csrf-token="{{ csrf_token('upload_venue_photo_' ~ venue.publicIdentifier) }}"
       data-gallery-target="venue-photo-gallery">
    {{ form_start(photo_form, { attr: { class: 'grid gap-4', novalidate: 'novalidate' } }) }}
      <label for="venue-photo-input" class="flex cursor-pointer flex-col items-center justify-center rounded-base border-2 border-dashed border-default bg-neutral-primary-soft px-6 py-8 text-center">
        <p class="text-sm font-semibold text-heading">Cliquez pour téléverser ou glissez-déposez</p>
        <p class="text-xs text-body" data-photo-status>JPG, PNG, WEBP · 5 Mo max</p>
        {{ form_widget(photo_form.photo, { attr: { id: 'venue-photo-input', class: 'sr-only' } }) }}
      </label>
      <div class="text-xs text-danger" data-photo-error>{{ form_errors(photo_form.photo) }}</div>
      <div class="flex items-center justify-end" data-photo-submit>
        {{ include('components/_button.html.twig', { label: 'Ajouter les photos', variant: 'primary', size: 'sm', type: 'submit' }) }}
      </div>
    {{ form_end(photo_form) }}
  </div>

  <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4" id="venue-photo-gallery">
    {% for photo in venue_photos %}
      {# carte photo + actions + renommage inline #}
    {% else %}
      <p class="text-body-subtle">Aucune photo disponible.</p>
    {% endfor %}
    <label for="venue-photo-input" class="flex cursor-pointer items-center justify-center rounded-base border border-dashed border-default bg-neutral-primary-soft text-sm text-body">+</label>
  </div>
</section>
```

---

### 6.2 Principes backend

- upload traité via un endpoint dédié :  
  `POST /administration/sites/{publicIdentifier}/photos` ;
- renommage via :  
  `POST /administration/sites/{publicIdentifier}/photos/{id}/libelle` ;
- suppression via :  
  `POST /administration/sites/{publicIdentifier}/photos/{id}/supprimer` ;
- stockage des fichiers via la stratégie de gestion documentaire retenue ;
- persistance en base des métadonnées suivantes :
  - chemin du fichier (`filePath`) ;
  - type = `PHOTO` (référencé via `SiteDocumentType`) ;
  - taille, mime-type, libellé.

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
