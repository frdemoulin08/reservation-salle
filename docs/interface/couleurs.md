# Couleurs CD08 (extraites du CSS du site cd08.fr)

> Source: valeurs **hex** repérées dans le CSS fourni (classes `bg-primary`, `text-secondary-dark`, variables `--tw-prose-*`, etc.).
> Les OKLCH ci-dessous sont calculées à partir des hex.

## Palette (hex + OKLCH)

| Token | Hex | OKLCH | Usage (observé) |
|---|---:|---:|---|
| `primary` | `#00312F` | `oklch(0.2827 0.0487 190.44)` | bg-primary / text-primary |
| `primary-light` | `#003E37` | `oklch(0.3266 0.0583 182.53)` | bg-primary-light / text-primary-light (souvent liens/typo) |
| `primary-lighter` | `#DEEFE6` | `oklch(0.9370 0.0216 163.04)` | bg-primary-lighter (fonds très doux) |
| `secondary` | `#B18800` | `oklch(0.6480 0.1325 87.17)` | bg-secondary / text-secondary (accent) |
| `secondary-dark` | `#8B6C00` | `oklch(0.5466 0.1117 88.73)` | bg-secondary-dark / text-secondary-dark |
| `secondary-light` | `#DAA507` | `oklch(0.7509 0.1528 85.15)` | bg-secondary-light (accent clair) |
| `secondary-lighter` | `#FEF5E6` | `oklch(0.9731 0.0220 80.69)` | bg-secondary-lighter (fonds accent doux) |
| `secondary-lightest` | `#FBF9F4` | `oklch(0.9823 0.0069 88.64)` | bg secondaire très clair (fonds) |
| `border-light` | `#F1ECDD` | `oklch(0.9429 0.0206 91.59)` | bordure claire |
| `bg-cream-1` | `#FCFAF4` | `oklch(0.9850 0.0082 91.48)` | fond principal très clair |
| `bg-cream-2` | `#F8F4EA` | `oklch(0.9675 0.0139 88.68)` | fond secondaire |
| `bg-cream-3` | `#F0EBDB` | `oklch(0.9396 0.0219 92.51)` | fond tertiaire |

## Suggestions de mapping Flowbite (si besoin)

```css
:root {
  /* Brand = primaire */
  --color-brand:        #00312F;
  --color-brand-strong: #003E37; /* hover */
  --color-brand-medium: #DEEFE6; /* ring/focus doux */
  --color-brand-soft:   #DEEFE6; /* fonds */

  /* Accent = secondaire */
  --color-accent:       #B18800;
  --color-accent-strong:#8B6C00;
  --color-accent-soft:  #FEF5E6;
}
```

## Notes

- Ces couleurs sont **plus précises** que l’extraction “à l’œil” : elles proviennent directement du CSS (donc de la prod du site).
- Si le site charge plusieurs bundles, il peut exister d’autres variantes (ex: états, gradients).
