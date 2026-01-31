# 🎨 Recommandations d’intégration Flowbite 4 + Tailwind 4  
### *Symfony 8 – Design tokens, bonnes pratiques et structure IA-friendly*

---

## 1. 🎯 Objectifs généraux

Ces recommandations visent à garantir une intégration propre, durable et évolutive entre **Flowbite 4**, **Tailwind 4** et les composants personnalisés du projet.

Elles reposent sur cinq principes :

1. **Privilégier les design tokens Flowbite**  
   (couleurs, radius, typography, shadows, OKLCH).
2. **Limiter les surcharges CSS**, éviter de réécrire ce que Flowbite fait déjà.
3. **Assurer une structure simple, stable et lisible par une IA.**
4. **Maximiser la compatibilité Tailwind ↔ Flowbite ↔ composants internes.**
5. **Centraliser la personnalisation dans des tokens CSS**, sans modifier les tokens natifs Flowbite.

---

## 2. 🧱 Structure recommandée d’un `app.css`

L'ordre des imports et directives garantit la bonne génération des tokens Flowbite, la cohérence du thème et l’absence de conflit entre layers Tailwind.

```css
/* --- 1. Base Tailwind --- */
@import "tailwindcss";

/* Exclure le dossier public pour éviter les boucles de compilation */
@source not "../../public";

/* --- 2. Thème Flowbite + Font --- */
@import "flowbite/src/themes/default";
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");

/* --- 3. Plugins et sources --- */
@plugin "flowbite/plugin";
@source "../../node_modules/flowbite";

/* --- 4. CSS legacy (libs externes) --- */
@import "leaflet/dist/leaflet.css";
@import "leaflet.markercluster/dist/MarkerCluster.css";
@import "leaflet.markercluster/dist/MarkerCluster.Default.css";
@import "./flowbite-datepicker.css";

/* --- 5. Layers Tailwind --- */
@layer base { /* Styles sémantiques globaux */ }

@layer components { /* Composants maison (cards, boutons, Tom Select…) */ }

@layer utilities { /* Optionnel */ }

/* --- 6. Personnalisation du thème (tokens supplémentaires) --- */
@layer base {
  :root {
  --z-overlay: 50;
  --z-dropdown: 60;

  /* Exemple de couleurs métier utilisant Tailwind */
  --color-brand-50:  theme(colors.sky.50);
  --color-brand-500: theme(colors.sky.500);
  }
}
```

---

## 3. 🧩 Principes de design systémique recommandés

### 3.1. 🔵 Toujours privilégier les design tokens Flowbite  
Ne jamais utiliser d’hex, de pixels ou de valeurs brutes si un token existe.

❌ À éviter :

```css
background-color: #e0e7ff;
border-radius: 0.375rem;
```

✔️ Préférer :

```css
background-color: var(--color-blue-100);
border-radius: var(--radius-md);
```

---

### 3.2. 🛑 Éviter les surcharges redondantes  
Certains styles actuels utilisent à la fois :

- `@apply bg-blue-100 text-blue-900`
- `background-color: var(--color-blue-100)`
- `color: var(--color-blue-900)`

👉 **Toujours choisir une seule source de vérité.**

**Option A – Utilitaires Tailwind** (simple)  
**Option B – Tokens Flowbite** (recommandé pour composants complexes comme Tom Select).

---

### 3.3. 🌗 Dark mode 100 % basé sur `.dark`  

✔ Utiliser :

```css
@apply dark:text-gray-200;
```

ou via tokens Flowbite.

---

### 3.4. 🔢 Gestion des z-index via tokens  
Les valeurs brutes doivent être remplacées par des tokens spécifiés dans `@theme`.

❌ À proscrire :

```css
z-index: 9999;
z-index: 2147483647;
```

✔ Correct :

```css
z-index: var(--z-dropdown);
```

---

### 3.5. 🧱 Composants maison uniquement dans `@layer components`

```css
@layer components {
  .card {
    @apply rounded-lg shadow-sm bg-white dark:bg-gray-800;
  }
}
```

---

### 3.6. 🧬 Tom Select : surcharges minimales et tokenisées  

```css
.ts-dropdown .option.selected {
  background-color: var(--color-blue-100);
  color: var(--color-blue-900);
}

.ts-control .item {
  background-color: var(--color-blue-50);
  border-radius: var(--radius-md);
  color: var(--color-gray-900);
}
```

Pas de `!important` sauf contrainte technique impérative.

---

## 4. 🛠️ Checklist IA (pour refactor ou génération automatique)

L’IA doit systématiquement appliquer ces règles :

### ✔ 1. Toujours remplacer les valeurs brutes par des tokens Flowbite.  
### ✔ 2. Ne jamais écraser un token Flowbite existant.  
### ✔ 3. Ne pas mélanger `@apply` + tokens pour une même propriété.  
### ✔ 4. Placer tout composant custom dans `@layer components`.  
### ✔ 5. Utiliser exclusivement `.dark` pour le mode sombre.  
### ✔ 6. Utiliser les tokens `--z-overlay`, `--z-dropdown` pour les couches UI.  
### ✔ 7. Surcharger Tom Select uniquement pour l’alignement design.  
### ✔ 8. Éviter les sélecteurs globaux non ciblés (`svg`, `a[href="/"]`…).  

---

## 5. 📦 Modèle final de fichier `app.css`

```css
@import "tailwindcss";
@source not "../../public";

@import "flowbite/src/themes/default";
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");

@plugin "flowbite/plugin";
@source "../../node_modules/flowbite";

@import "leaflet/dist/leaflet.css";
@import "leaflet.markercluster/dist/MarkerCluster.css";
@import "leaflet.markercluster/dist/MarkerCluster.Default.css";
@import "./flowbite-datepicker.css";

@layer base {}

@layer components {}

@layer utilities {}

@layer base {
  :root {
    --z-overlay: 50;
    --z-dropdown: 60;

    --color-brand-50: theme(colors.sky.50);
    --color-brand-500: theme(colors.sky.500);
  }
}
```

---

## 6. 🏁 Conclusion

En adoptant cette approche :

- Flowbite devient la **source de vérité design**,  
- les styles sont **clairs, tokenisés, IA-friendly**,  
- les conflits CSS sont fortement réduits,  
- le thème devient **simple à maintenir et étendre**,  
- tous les composants (maison, Flowbite, Tom Select, Tabulator) restent cohérents,  
- l’évolution du design (OKLCH, tokens dynamiques) est facilitée.

---

**Document recommandé pour `/docs/interface/recommandations-flowbite-tailwind.md`.**
