# Breadcrumbs (fil d’Ariane)

Guide opérationnel pour intégrer un fil d’Ariane dans l’admin (et ailleurs).

## Objectif
- Donner le contexte de navigation et la hiérarchie.
- Aligner l’intitulé du dernier crumb avec le `<h1>` de la page.
- Respecter les conventions d’accessibilité (ARIA) + styles Flowbite déjà utilisés.

## Templates disponibles
Les breadcrumbs sont des templates Twig simples (pas de composant Twig natif). Ils sont rendus via `include`.

- `templates/components/breadcrumb/home.html.twig`
- `templates/components/breadcrumb/single.html.twig`
- `templates/components/breadcrumb/double.html.twig`
- `templates/components/breadcrumb/triple.html.twig`
- `templates/components/breadcrumb/crud.html.twig`
- `templates/components/breadcrumb/crud_double.html.twig`

## Règles générales
1) Utiliser `app.request.attributes.get('_route')` pour détecter la route (Symfony 8 n’a plus `Request::get()` pour cela).
2) Le crumb courant porte `aria-current="page"` et est un `<span>` (pas de lien).
3) Les crumbs précédents sont des liens (`<a>`).
4) Le fil d’Ariane est enveloppé par un `<nav aria-label="Fil d’ariane">`.
5) Pour les booléens (`firstLevelHasLink`, `secondLevelHasLink`), utiliser `to_bool(...)` du `BoolExtension`.

## Où l’intégrer
- Dans l’admin, le layout expose un bloc dédié :
  - `templates/admin/_layout.html.twig` → `{% block admin_breadcrumb %}{% endblock %}`

Exemple dans une page admin :
```twig
{% block admin_breadcrumb %}
  <div class="mb-4">
    {% include 'admin/sites/_breadcrumb.html.twig' with { current_label: page_heading } %}
  </div>
{% endblock %}
```

## Exemple complet (cas CRUD double)
### 1) Partiel dédié au module
`templates/admin/sites/_breadcrumb.html.twig`
```twig
{% set current_label = current_label|default('Sites') %}

{{ include('components/breadcrumb/crud_double.html.twig', {
  homeRoute: 'app_admin_index',
  homeLabel: 'Accueil',
  firstLevelRoute: 'app_admin_index',
  firstLevelLabel: 'Gestion',
  firstLevelHasLink: false,
  secondLevelIndexRoute: 'app_admin_sites_index',
  secondLevelIndexLabel: 'Sites',
  secondLevelShowRoute: 'app_admin_sites_show',
  secondLevelShowLabel: current_label,
  secondLevelCreateRoute: 'app_admin_sites_new',
  secondLevelCreateLabel: current_label,
  secondLevelEditRoute: 'app_admin_sites_edit',
  secondLevelEditLabel: current_label
}) }}
```

### 2) Page admin (index, show, new, edit)
```twig
{% set page_heading = 'Sites' %}

{% block admin_breadcrumb %}
  <div class="mb-4">
    {% include 'admin/sites/_breadcrumb.html.twig' with { current_label: page_heading } %}
  </div>
{% endblock %}

<h1 class="mt-3 text-3xl font-semibold text-heading">{{ page_heading }}</h1>
```

## Paramètres par template
### `home.html.twig`
- `homeRoute`
- `homeLabel`

### `single.html.twig`
- `homeRoute`, `homeLabel`
- `route`, `label`

### `double.html.twig`
- `homeRoute`, `homeLabel`
- `firstLevelRoute`, `firstLevelLabel`
- `firstLevelHasLink` (bool, optionnel)
- `secondLevelRoute`, `secondLevelLabel`

### `triple.html.twig`
- `homeRoute`, `homeLabel`
- `firstLevelRoute`, `firstLevelLabel`, `firstLevelHasLink` (bool, optionnel)
- `secondLevelRoute`, `secondLevelLabel`, `secondLevelHasLink` (bool, optionnel)
- `thirdLevelRoute`, `thirdLevelLabel`

### `crud.html.twig`
- `homeRoute`, `homeLabel`
- `indexRoute`, `indexLabel`
- `showRoute`, `showLabel`
- `createRoute`, `createLabel`
- `editRoute`, `editLabel`

### `crud_double.html.twig`
- `homeRoute`, `homeLabel`
- `firstLevelRoute`, `firstLevelLabel`, `firstLevelHasLink` (bool, optionnel)
- `secondLevelIndexRoute`, `secondLevelIndexLabel`
- `secondLevelShowRoute`, `secondLevelShowLabel`
- `secondLevelCreateRoute`, `secondLevelCreateLabel`
- `secondLevelEditRoute`, `secondLevelEditLabel`

## Bonnes pratiques
- Toujours faire correspondre le dernier crumb et le `<h1>`.
- Garder les labels courts.
- Réutiliser un partiel `_breadcrumb.html.twig` par module pour éviter la duplication.
- Si un niveau n’est pas cliquable, passer `firstLevelHasLink: false` ou `secondLevelHasLink: false`.

## Dépannage
- Erreur “Unknown props tag” : ne pas utiliser `{% props %}` (templates simples + `include`).
- Erreur sur `app.request.get` : utiliser `app.request.attributes.get('_route')`.
- Booléens incohérents : vérifier `to_bool(...)` dans `BoolExtension`.
