# Guide d'intégration Flowbite 4 + Tailwind CSS 4.1 dans Symfony 8  
*(Version enrichie avec section “Personnalisation du thème Flowbite”)*

## 1. Objectif

Cette documentation explique comment intégrer Flowbite 4 avec Tailwind CSS 4.1 dans un projet Symfony 8 moderne. 
Elle suit les bonnes pratiques recommandées pour :
- utiliser les **design tokens Flowbite** (couleurs, radius, typography, shadows, OKLCH),
- garder une structure propre et maintenable,
- maximiser la compatibilité IA (clarté, conventions, structure prévisible),
- éviter les conflits entre composants maison, Flowbite, Tailwind et dark mode,
- **personnaliser le thème Flowbite proprement**.

---

## 2. Installation

### 2.1. Dépendances de base

```bash
npm install flowbite tailwindcss postcss postcss-loader @tailwindcss/postcss
```

### 2.2. Vérifier la structure d'un projet Symfony 8

```
assets/
 ├─ app.js
 └─ styles/
     └─ app.css
templates/
 └─ base.html.twig
webpack.config.js
postcss.config.mjs
```

---

## 3. Configuration Webpack Encore + PostCSS

Flowbite 4 s'appuie sur Tailwind 4 (CSS-first) via PostCSS.  
Active le loader PostCSS dans `webpack.config.js` :

```js
Encore
  .enablePostCssLoader()
;
```

Crée `postcss.config.mjs` :

```js
export default {
  plugins: {
    "@tailwindcss/postcss": {},
  },
};
```

---

## 4. Intégration dans assets/styles/app.css

Voici la version recommandée :

```css
@import "tailwindcss";

/* Polices */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

/* Thème Flowbite (tokens OKLCH + radius + shadow + typography) */
@import "flowbite/src/themes/default";

/* Flowbite plugin */
@plugin "flowbite/plugin";

/* Permet à Tailwind de scanner Flowbite */
@source "../../node_modules/flowbite";

/* Empêche le scan de public/ */
@source not "../../public";
```

---

## 5. Intégration JavaScript

Ajoute Flowbite dans `assets/app.js` :

```js
import './styles/app.css';
import 'flowbite';
```

---

## 6. Chargement des assets dans Twig

Dans `templates/base.html.twig`, charge le bundle Encore :

```twig
{% block stylesheets %}
    {{ encore_entry_link_tags('app') }}
{% endblock %}

{% block javascripts %}
    {{ encore_entry_script_tags('app') }}
{% endblock %}
```

---

## 7. Utilisation des tokens Flowbite

Flowbite définit des tokens universels via `:root { --color-brand: ..., --radius-base: ... }`.

### 5.1. Exemple de card "token-based"

```html
<div class="bg-neutral-primary-soft border-default shadow-sm rounded-base p-6">
  <h3 class="text-heading mb-2">Titre</h3>
  <p class="text-body mb-4">Contenu exemple utilisant le thème Flowbite.</p>
  <button class="bg-brand text-white rounded-base px-4 py-2 hover:bg-brand-strong">
    Action
  </button>
</div>
```

### 5.2. Pourquoi utiliser ces classes ?

- elles s'adaptent automatiquement au dark mode,
- elles respectent un système de design cohérent,
- elles ne nécessitent plus de connaître `gray-50`, `gray-100`, etc.,
- elles facilitent le theming (ex: branding My Exams).

---

## 8. Exemple d'override propre via @layer

```css
@layer components {
  .card-app {
    @apply bg-neutral-primary-soft border-default shadow-sm rounded-base p-6;
  }

  .btn-app {
    @apply bg-brand text-white rounded-base px-4 py-2 
           hover:bg-brand-strong font-medium shadow-xs;
  }
}
```

---

## 9. Dark mode (bonnes pratiques)

### 7.1. Flowbite gère déjà le dark mode

Les tokens fournissent :
- `--color-neutral-primary-soft-dark`
- `--color-brand-dark`
- etc.

Donc pas besoin de :
```css
html.dark .card-app { ... }
```

### 7.2. Quand utiliser `html.dark` ?

Uniquement pour override un composant externe (ex.: Tom Select, Leaflet).

---

## 10. Guide de migration depuis Tailwind classique

| Ancien Tailwind | Nouveau Flowbite |
|------------------|------------------|
| `bg-gray-50` | `bg-neutral-primary-soft` |
| `border-gray-200` | `border-default` |
| `rounded-lg` | `rounded-base` |
| `shadow-sm` | `shadow-xs` |
| `text-gray-900` | `text-heading` |
| `text-gray-700` | `text-body` |
| `bg-blue-600` | `bg-brand` |
| `hover:bg-blue-700` | `hover:bg-brand-strong` |

---

## 11. Exemples recommandés

### 9.1. Component Button

```html
<button class="bg-brand text-white px-4 py-2 rounded-base shadow-xs
               hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium">
  Valider
</button>
```

### 9.2. Component Input

```html
<input 
  class="bg-surface border-default rounded-base p-2 text-body 
         focus:ring-brand focus:border-brand-medium" />
```

---

## 12. Bonnes pratiques IA-friendly

1. Toujours documenter les classes “token-based”.
2. Toujours regrouper les overrides dans `@layer components`.
3. Toujours privilégier :
   - `bg-neutral-*`, `bg-brand-*`,
   - `shadow-*`, `rounded-*`,
   - `text-heading`, `text-body`,
   - `border-default`.
4. Éviter les `!important` sauf cas extrêmes.
5. Utiliser `.dark` seulement pour composants non-Flowbite.
6. Toujours mettre un exemple avant/après migration.

---

## 13. Checklist finale d'intégration

- [x] `@import "tailwindcss"` présent en début de fichier  
- [x] `@import "flowbite/src/themes/default"` chargé  
- [x] `@plugin "flowbite/plugin"` ajouté  
- [x] `@source "../../node_modules/flowbite"` configuré  
- [x] `import 'flowbite'` ajouté dans `assets/app.js`  
- [x] `encore_entry_link_tags` et `encore_entry_script_tags` présents dans Twig  
- [x] composants maison compatibles tokens  
- [x] aucun conflit de thèmes (pas de `bg-white` forcé)  

---

## 14. Personnalisation du thème Flowbite (couleurs, tokens, branding)

Flowbite 4 repose sur un **design-token system**, donc la personnalisation se fait en redéfinissant les **CSS variables**, pas les classes.

---

### 14.1. Surcharger la couleur *brand*

```css
@layer base {
  :root {
    --color-brand: oklch(0.67 0.18 255);
    --color-brand-strong: oklch(0.58 0.20 255);
    --color-fg-brand: oklch(0.42 0.10 255);
    --color-brand-soft: oklch(0.94 0.05 255);
  }

  .dark {
    --color-brand: oklch(0.75 0.14 255);
    --color-brand-strong: oklch(0.82 0.12 255);
    --color-fg-brand: oklch(0.92 0.06 255);
    --color-brand-soft: oklch(0.20 0.04 255);
  }
}
```

---

### 14.2. Ajouter une couleur personnalisée (ex : “exam”)

### Déclaration des tokens :

```css
@layer base {
  :root {
    --color-exam: oklch(0.72 0.16 260);
    --color-exam-soft: oklch(0.96 0.04 260);
    --color-fg-exam: oklch(0.45 0.07 260);
  }

  .dark {
    --color-exam: oklch(0.78 0.12 260);
    --color-exam-soft: oklch(0.20 0.03 260);
    --color-fg-exam: oklch(0.92 0.04 260);
  }
}
```

### Création des utilitaires :

```css
@layer utilities {
  .bg-exam {
    background-color: var(--color-exam);
  }
  .bg-exam-soft {
    background-color: var(--color-exam-soft);
  }
  .text-fg-exam {
    color: var(--color-fg-exam);
  }
}
```

---

### 14.3. Surcharger les couleurs *neutral*

```css
@layer base {
  :root {
    --color-neutral-primary-soft: oklch(0.97 0.01 250);
  }
  .dark {
    --color-neutral-primary-soft: oklch(0.16 0.01 250);
  }
}
```

---

### 14.4. Bonnes pratiques

✔ Modifier uniquement les tokens (`--color-*`)  
✔ Définir light + dark  
✔ Ne pas surcharger les classes Flowbite  
✔ Nommer les couleurs selon la convention Flowbite  
✔ Documenter tout ajout dans un fichier design-system.md  

---

## 15. Conclusion

Vous avez maintenant un projet Symfony 8 utilisant :
- Tailwind CSS 4.1 moderne,  
- Flowbite 4 comme design system complet,  
- un thème personnalisable via tokens OKLCH,  
- un dark mode natif,  
- une architecture propre, scalable, IA-friendly.

---

## 16. Licence
Libre d'utilisation et de reproduction dans tout projet éducatif ou administratif.
