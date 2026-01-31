# Guide développeur – Mettre en place un tableau (Symfony 8 + Flowbite)

Ce guide décrit **pas à pas** la mise en place d’un tableau backoffice conforme à la stratégie projet (AJAX, Flowbite, server‑side).

> Prérequis : lire `docs/technique/tableaux/strategie-gestion-tableaux-symfony-flowbite.md` et l’annexe technique.

---

## 1) Repository : requête de base

Expose une méthode dédiée qui applique **uniquement** les filtres métier.

```php
public function createTableQb(TableParams $params): QueryBuilder
{
    $qb = $this->createQueryBuilder('s');

    $search = trim((string) ($params->filters['q'] ?? ''));
    if ($search !== '') {
        $qb
            ->andWhere('s.name LIKE :search OR s.city LIKE :search')
            ->setParameter('search', '%'.$search.'%');
    }

    return $qb;
}
```

---

## 2) Contrôleur : tri + pagination + AJAX

Le contrôleur :
- récupère `TableParams`,
- appelle le repository,
- passe par `TablePaginator`,
- retourne le fragment si AJAX.

```php
#[Route('/administration/sites', name: 'app_admin_sites_index')]
public function index(Request $request, SiteRepository $repo, TablePaginator $paginator): Response
{
    $params = TableParams::fromRequest($request, [
        'sort' => 'updatedAt',
        'direction' => 'desc',
        'per_page' => 10,
    ]);

    $qb = $repo->createTableQb($params);
    $pager = $paginator->paginate($qb, $params, ['name', 'city', 'capacity', 'status', 'updatedAt'], 's');

    if ($request->isXmlHttpRequest()) {
        return $this->render('admin/sites/_table.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    return $this->render('admin/sites/index.html.twig', [
        'pager' => $pager,
        'params' => $params,
    ]);
}
```

---

## 3) Vue “index” : formulaire + conteneur AJAX

Le formulaire possède :
- `data-table-form`
- un champ de recherche `data-table-search`
- un conteneur `data-table-ajax`

```twig
<form method="get"
      action="{{ path('app_admin_sites_index') }}"
      data-table-form
      data-table-target="sites">
    <input type="search"
           name="filter[q]"
           data-table-search
           data-min-length="3"
           placeholder="Rechercher…">
    <input type="hidden" name="sort" value="{{ params.sort }}">
    <input type="hidden" name="direction" value="{{ params.direction }}">
</form>

<div data-table-ajax="sites">
    {% include 'admin/sites/_table.html.twig' with { pager: pager, params: params } %}
</div>
```

---

## 4) Partial tableau : header + pagination

Le tableau :
- utilise `_partials/table/_header.html.twig` pour le tri,
- inclut `_partials/table/_pagination.html.twig`.

```twig
{% include '_partials/table/_header.html.twig' with {
    route: 'app_admin_sites_index',
    params: params,
    columns: columns
} %}

{% include '_partials/table/_pagination.html.twig' with {
    route: 'app_admin_sites_index',
    params: params,
    pager: pager
} %}
```

---

## 5) Recherche AJAX (sans bouton)

Le JS commun (`assets/js/table-ajax.js`) intercepte :
- la saisie `data-table-search`,
- le submit `data-table-form`,
et déclenche une requête AJAX vers la même route.

Règles en place :
- déclenchement à partir de **3 caractères** (configurable via `data-min-length`) ;
- si le champ est vidé, la liste est rechargée.

---

## 6) Checklist finale

- [ ] Repository : méthode `createTableQb()` avec filtres métier.
- [ ] Contrôleur : `TableParams` + `TablePaginator` + fragment AJAX.
- [ ] Twig index : form `data-table-form` + `data-table-search`.
- [ ] Twig table : header + pagination Flowbite.
- [ ] Recherche AJAX : champ avec `data-min-length="3"`.

---

## Références

- `docs/technique/tableaux/strategie-gestion-tableaux-symfony-flowbite.md`
- `docs/technique/tableaux/annexe-technique-gestion-tableaux-symfony-flowbite.md`
