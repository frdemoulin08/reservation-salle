# En-têtes de sécurité

## Objectif

Renforcer la sécurité côté navigateur via des en‑têtes HTTP standards (CSP, HSTS), afin de réduire les risques d’injection de scripts et forcer l’usage du HTTPS.

---

## En‑têtes mis en place

### 1) Content-Security-Policy (CSP)

Implémenté par `SecurityHeadersSubscriber`.

Politique actuelle :

- `default-src 'self'`
- `base-uri 'self'`
- `form-action 'self'`
- `frame-ancestors 'self'`
- `img-src 'self' data:`
- `script-src 'self' 'unsafe-inline'`
- `style-src 'self' 'unsafe-inline'`
- `font-src 'self' data:`
- `connect-src 'self'`
- `object-src 'none'`
- `upgrade-insecure-requests`
- `block-all-mixed-content`

> Note : l’usage de `unsafe-inline` est conservé pour compatibilité (scripts inline liés au thème/Flasher). Il pourra être retiré ultérieurement avec une stratégie de nonce.

### 2) Strict-Transport-Security (HSTS)

Activé **en production uniquement**, et **uniquement si HTTPS**.

Valeur :
```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

---

## Emplacement dans le code

- `src/EventSubscriber/SecurityHeadersSubscriber.php`

---

## Évolutions possibles

- Passer à une CSP **sans `unsafe-inline`** (nonce/sha) pour durcir la politique.
- Ajouter d’autres en‑têtes :
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `X-Content-Type-Options: nosniff`
  - `Permissions-Policy` (ex‑Feature‑Policy)

---

## Rappel

- **CSP** protège contre les scripts injectés (XSS).
- **HSTS** force l’utilisation du HTTPS dans le navigateur.
