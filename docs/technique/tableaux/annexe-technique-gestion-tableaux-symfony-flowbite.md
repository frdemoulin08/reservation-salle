# Annexe technique – Gestion des tableaux (Symfony 8 + Flowbite, AJAX)
**Application de réservation et de facturation des salles**  
*Implémentation de la stratégie de gestion des listes (tri / pagination / recherche en AJAX)*

Cette annexe complète le document « Stratégie de gestion des tableaux (listes) – Symfony 8 + Flowbite » en fournissant un cadre technique concret pour l’implémentation :

- des tableaux Flowbite,
- avec tri, pagination, recherche **en AJAX**,
- sans jQuery ni Stimulus.

---

## 1. Arborescence recommandée

Proposition minimale :

- `src/Table/`
  - `TableParams.php`
  - `TablePaginator.php`
- `src/Repository/`
  - `UserRepository.php` (et autres repositories métier)
- `src/Controller/Admin/`
  - `UserController.php` (et autres contrôleurs backoffice)
- `templates/_partials/table/`
  - `_header.html.twig`
  - `_pagination.html.twig`
- `templates/admin/user/`
  - `index.html.twig`
  - `_table.html.twig`
- `assets/js/`
  - `table-ajax.js` (script commun de gestion AJAX des tableaux)

Cette structure est à adapter en fonction des besoins, mais l’idée est de **centraliser** la logique générique dans `src/Table`, `templates/_partials/table` et un JS partagé.

---

## 2. Objet `TableParams`

### 2.1. Objectif

Encapsuler les paramètres de liste (page, tri, filtres/recherche) en un objet simple, construit à partir de la requête HTTP (GET).

### 2.2. Exemple de code

```php
<?php
// src/Table/TableParams.php

namespace App\Table;

use Symfony\Component\HttpFoundation\Request;

class TableParams
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $sort,
        public readonly string $direction,
        public readonly array $filters = []
    ) {
    }

    public static function fromRequest(Request $request, array $defaults = []): self
    {
        $defaults = array_merge([
            'page'      => 1,
            'per_page'  => 25,
            'sort'      => 'createdAt',
            'direction' => 'desc',
        ], $defaults);

        $page = max(1, (int) $request->query->get('page', $defaults['page']));
        $perPage = max(1, (int) $request->query->get('per_page', $defaults['per_page']));
        $sort = (string) $request->query->get('sort', $defaults['sort']);
        $direction = strtolower((string) $request->query->get('direction', $defaults['direction'])) === 'asc' ? 'asc' : 'desc';

        // Convention : tous les filtres sont sous filter[...]
        $filters = $request->query->all('filter');

        return new self($page, $perPage, $sort, $direction, $filters);
    }

    public function toQueryArray(): array
    {
        $query = [
            'page'      => $this->page,
            'per_page'  => $this->perPage,
            'sort'      => $this->sort,
            'direction' => $this->direction,
        ];

        if (!empty($this->filters)) {
            $query['filter'] = $this->filters;
        }

        return $query;
    }
}
```

---

## 3. Service `TablePaginator`

### 3.1. Objectif

Appliquer tri + pagination à un `QueryBuilder` en s’appuyant sur une liste de colonnes de tri autorisées.

### 3.2. Exemple de code

```php
<?php
// src/Table/TablePaginator.php

namespace App\Table;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class TablePaginator
{
    /**
     * @param QueryBuilder $qb           Requête de base
     * @param TableParams  $params       Paramètres de tableau
     * @param string[]     $allowedSorts Noms des colonnes Doctrine autorisées pour le tri (sans alias)
     * @param string       $alias        Alias Doctrine principal (ex: 'u')
     */
    public function paginate(
        QueryBuilder $qb,
        TableParams $params,
        array $allowedSorts,
        string $alias = 'e'
    ): Pagerfanta {
        $sort = \in_array($params->sort, $allowedSorts, true)
            ? $params->sort
            : $allowedSorts[0];

        $qb->orderBy(sprintf('%s.%s', $alias, $sort), $params->direction);

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage($params->perPage);
        $pager->setCurrentPage($params->page);

        return $pager;
    }
}
```

> Remarque : ce service suppose l'utilisation de **Pagerfanta** (ou équivalent).  
> L'alias Doctrine (`u`, `r`, `s`, etc.) est passé depuis le repository ou le contrôleur.

---

## 4. Repositories

### 4.1. Principe

Chaque repository expose une méthode permettant de construire une requête de base pour les tableaux, sur laquelle seront appliqués filtres + tri + pagination.

### 4.2. Exemple (UserRepository)

```php
<?php
// src/Repository/UserRepository.php

namespace App\Repository;

use App\Entity\User;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');

        // Exemple de filtre par rôle (optionnel)
        if (!empty($params->filters['role'] ?? null)) {
            $qb
                ->andWhere(':role MEMBER OF u.roles')
                ->setParameter('role', $params->filters['role']);
        }

        // Exemple de recherche texte sur le nom
        if (!empty($params->filters['q'] ?? null)) {
            $qb
                ->andWhere('LOWER(u.lastName) LIKE :q OR LOWER(u.firstName) LIKE :q')
                ->setParameter('q', '%' . strtolower($params->filters['q']) . '%');
        }

        // À compléter selon les besoins (statut, service, etc.)
        return $qb;
    }
}
```

---

## 5. Contrôleurs backoffice (full AJAX)

### 5.1. Exemple : liste des utilisateurs

```php
<?php
// src/Controller/Admin/UserController.php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Table\TableParams;
use App\Table\TablePaginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_user_index')]
    public function index(
        Request $request,
        UserRepository $userRepository,
        TablePaginator $tablePaginator
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_APP_MANAGER');

        $params = TableParams::fromRequest($request, [
            'sort'      => 'createdAt',
            'direction' => 'desc',
            'per_page'  => 25,
        ]);

        $qb = $userRepository->createTableQb($params);

        $pager = $tablePaginator->paginate(
            $qb,
            $params,
            allowedSorts: ['createdAt', 'lastName', 'email'],
            alias: 'u'
        );

        if ($request->isXmlHttpRequest()) {
            // Requête AJAX : on ne renvoie que le fragment du tableau
            return $this->render('admin/user/_table.html.twig', [
                'pager'  => $pager,
                'params' => $params,
            ]);
        }

        // Requête classique : page complète
        return $this->render('admin/user/index.html.twig', [
            'pager'  => $pager,
            'params' => $params,
        ]);
    }
}
```

---

## 6. Partials Twig Flowbite et conteneur AJAX

### 6.1. En-tête de tableau (`_header.html.twig`)

```twig
{# templates/_partials/table/_header.html.twig #}
<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
  <tr>
    {% for col in columns %}
      <th scope="col" class="px-4 py-3">
        {% if col.sort is defined and col.sort %}
          {% set isCurrent = params.sort == col.sort %}
          {% set nextDirection = isCurrent and params.direction == 'asc' ? 'desc' : 'asc' %}
          <a
            href="{{ path(route, params.toQueryArray()|merge({ sort: col.sort, direction: nextDirection, page: 1 })) }}"
            class="flex items-center gap-1"
            data-table-link
          >
            {{ col.label }}
            {% if isCurrent %}
              <span>{{ params.direction == 'asc' ? '▲' : '▼' }}</span>
            {% endif %}
          </a>
        {% else %}
          {{ col.label }}
        {% endif %}
      </th>
    {% endfor %}
  </tr>
</thead>
```

> Remarque : `params.toQueryArray()` suppose que `TableParams` est passé tel quel à Twig.

---

### 6.2. Pagination (`_pagination.html.twig`)

```twig
{# templates/_partials/table/_pagination.html.twig #}
{% if pager.nbPages > 1 %}
  <nav class="flex items-center justify-between pt-4" aria-label="Table navigation">
    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
      Page
      <span class="font-semibold text-gray-900 dark:text-white">
        {{ pager.currentPage }}
      </span>
      sur
      <span class="font-semibold text-gray-900 dark:text-white">
        {{ pager.nbPages }}
      </span>
    </span>

    <div class="inline-flex items-center space-x-1">
      {% if pager.hasPreviousPage %}
        <a
          href="{{ path(route, params.toQueryArray()|merge({ page: pager.previousPage })) }}"
          data-table-link
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        >
          Précédent
        </a>
      {% endif %}

      {% if pager.hasNextPage %}
        <a
          href="{{ path(route, params.toQueryArray()|merge({ page: pager.nextPage })) }}"
          data-table-link
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        >
          Suivant
        </a>
      {% endif %}
    </div>
  </nav>
{% endif %}
```

---

### 6.3. Partial tableau (`_table.html.twig`)

```twig
{# templates/admin/user/_table.html.twig #}

{% set columns = [
  { label: 'Nom', sort: 'lastName' },
  { label: 'Email', sort: 'email' },
  { label: 'Créé le', sort: 'createdAt' },
  { label: 'Actions' }
] %}

<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
    {% include '_partials/table/_header.html.twig' with {
      columns: columns,
      params: params,
      route: 'admin_user_index'
    } %}

    <tbody>
      {% for user in pager.currentPageResults %}
        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
          <td class="px-4 py-2">{{ user.lastName }} {{ user.firstName }}</td>
          <td class="px-4 py-2">{{ user.email }}</td>
          <td class="px-4 py-2">{{ user.createdAt|date('d/m/Y H:i') }}</td>
          <td class="px-4 py-2">
            <a
              href="{{ path('admin_user_show', { id: user.id }) }}"
              class="font-medium text-blue-600 dark:text-blue-500 hover:underline"
            >
              Voir
            </a>
          </td>
        </tr>
      {% else %}
        <tr>
          <td colspan="{{ columns|length }}" class="px-4 py-4 text-center text-gray-400">
            Aucun utilisateur trouvé.
          </td>
        </tr>
      {% endfor %}
    </tbody>
  </table>
</div>

{% include '_partials/table/_pagination.html.twig' with {
  pager: pager,
  params: params,
  route: 'admin_user_index'
} %}
```

---

### 6.4. Template principal (`index.html.twig`) avec conteneur AJAX

```twig
{# templates/admin/user/index.html.twig #}

{% extends 'base.html.twig' %}

{% block body %}
  <h1 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
    Utilisateurs
  </h1>

  {# Exemple de formulaire de recherche/filtre #}
  <form
    method="get"
    action="{{ path('admin_user_index') }}"
    data-table-form
    class="mb-4 flex items-center gap-2"
  >
    <input
      type="text"
      name="filter[q]"
      value="{{ params.filters.q ?? '' }}"
      placeholder="Rechercher…"
      class="border text-sm rounded-lg block w-64 p-2.5 bg-gray-50 border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500"
    >
    <button
      type="submit"
      class="px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300"
    >
      Rechercher
    </button>
  </form>

  <div
    id="user-table-container"
    data-table-ajax="users"
  >
    {# Contenu initial (HTML complet du tableau) #}
    {% include 'admin/user/_table.html.twig' with {
      pager: pager,
      params: params
    } %}
  </div>
{% endblock %}
```

---

## 7. JavaScript AJAX (vanilla)

### 7.1. Fichier `assets/js/table-ajax.js`

```js
document.addEventListener('click', function (event) {
  const link = event.target.closest('[data-table-link]');
  if (!link) {
    return;
  }

  const container = link.closest('[data-table-ajax]');
  // Si le lien n'est pas dans un conteneur de tableau AJAX, on laisse le comportement normal
  if (!container) {
    return;
  }

  event.preventDefault();

  fetch(link.href, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Erreur réseau');
      }
      return response.text();
    })
    .then(html => {
      container.innerHTML = html;

      // Optionnel : mettre à jour l’URL dans la barre d’adresse
      if (window.history && window.history.pushState) {
        window.history.pushState({}, '', link.href);
      }
    })
    .catch(err => {
      console.error(err);
      // En cas d’erreur, fallback : rechargement classique
      window.location.href = link.href;
    });
});

document.addEventListener('submit', function (event) {
  const form = event.target.closest('[data-table-form]');
  if (!form) {
    return;
  }

  const container = document.querySelector('[data-table-ajax]');
  if (!container) {
    return;
  }

  event.preventDefault();

  const url = new URL(form.action, window.location.origin);
  const formData = new FormData(form);

  // On reconstruit la query string à partir du formulaire
  for (const [key, value] of formData.entries()) {
    url.searchParams.set(key, value);
  }

  fetch(url.toString(), {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Erreur réseau');
      }
      return response.text();
    })
    .then(html => {
      container.innerHTML = html;

      // Mettre à jour l’URL
      if (window.history && window.history.pushState) {
        window.history.pushState({}, '', url.toString());
      }
    })
    .catch(err => {
      console.error(err);
      window.location.href = url.toString();
    });
});
```

> Ce script :
> - ne dépend d’aucune librairie externe ;
> - intercepte tri, pagination et formulaires de filtre/recherche ;
> - assure une dégradation gracieuse (en cas de problème, la navigation classique fonctionne).

---

## 8. Exports (CSV / XLSX)

### Principe

- réutiliser `TableParams` pour récupérer les mêmes filtres/tri que la liste ;
- construire un `QueryBuilder` similaire, mais sans pagination ;
- générer un fichier CSV/XLSX côté serveur.

### Exemple d'esquisse CSV

```php
#[Route('/admin/users/export', name: 'admin_user_export')]
public function export(
    Request $request,
    UserRepository $userRepository
): Response {
    $this->denyAccessUnlessGranted('ROLE_APP_MANAGER');

    $params = TableParams::fromRequest($request, [
        'sort'      => 'createdAt',
        'direction' => 'desc',
        'per_page'  => 1000, // éventuellement ignoré
    ]);

    $qb = $userRepository->createTableQb($params);

    $users = $qb->getQuery()->getResult();

    $response = new StreamedResponse(function () use ($users) {
        $handle = fopen('php://output', 'w+');
        fputcsv($handle, ['Nom', 'Email', 'Créé le'], ';');

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->getLastName() . ' ' . $user->getFirstName(),
                $user->getEmail(),
                $user->getCreatedAt()?->format('d/m/Y H:i'),
            ], ';');
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename=\"utilisateurs.csv\"');

    return $response;
}
```

---

## 9. Points de vigilance

- **Tri** : toujours utiliser une liste blanche (`$allowedSorts`) pour éviter les injections via le paramètre `sort`.
- **Sécurité** : appliquer `denyAccessUnlessGranted()` dans tous les contrôleurs de listes et d’exports.
- **Performance** : adapter les limites de pagination (`per_page`) et, si besoin, limiter le volume exporté.
- **Lisibilité** : centraliser les composants génériques dans `src/Table`, `templates/_partials` et `table-ajax.js` pour éviter la duplication.
- **Dégradation gracieuse** : les liens et formulaires restent pleinement utilisables sans JavaScript.

---

## 10. Synthèse

Cette annexe fournit un socle technique :

- générique, réutilisable ;
- compatible avec Flowbite et Twig ;
- avec **tri, pagination et recherche en AJAX** ;
- sans dépendance à des bibliothèques front-end lourdes ;
- aligné avec les exigences de sécurité et d’auditabilité du projet.

Codex peut s’appuyer sur cet exemple pour implémenter l’ensemble des tableaux backoffice de l’application.
