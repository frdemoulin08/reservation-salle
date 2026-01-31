# Icônes (Flowbite)

## Choix retenu

Nous utilisons les **icônes Flowbite** en **SVG inline** dans les templates Twig.
Ce choix est volontaire :
- rendu fidèle au design Flowbite,
- héritage direct de `currentColor`,
- aucune dépendance côté bundle d'icônes,
- intégration simple avec Tailwind/Flowbite.

## Source officielle

La bibliothèque d'icônes Flowbite est consultable ici :
- https://flowbite.com/icons/

## Convention de taille

Pour la cohérence visuelle, nous utilisons des classes utilitaires dédiées :
- `.icon-sm` : 16px
- `.icon-md` : 20px
- `.icon-lg` : 24px

Ces classes sont définies dans `assets/styles/app.css` et doivent être préférées aux tailles ad hoc.

## Bonnes pratiques

- Toujours utiliser `fill="none"` et `stroke="currentColor"` comme dans les SVG Flowbite.
- Utiliser `class="icon-sm|icon-md|icon-lg"` pour standardiser les tailles.
- Éviter les SVG externes ou les sprites si aucune mutualisation n'est nécessaire.

## Exemple

```twig
<button class="inline-flex items-center">
  <svg class="icon-md" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/>
  </svg>
  <span class="ml-2">Menu</span>
</button>
```
