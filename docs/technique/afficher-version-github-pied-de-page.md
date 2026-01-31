# Afficher dynamiquement le dernier tag GitHub dans le footer de l’application

## 🎯 Objectif

Afficher automatiquement dans le footer de l’application la **dernière version de l’application**, basée sur le **dernier tag Git** du dépôt distant sur GitHub.

Exemple de rendu dans le footer :

> Version `v1.4.2`

Cette documentation décrit une implémentation **Symfony** côté serveur, adaptée à une application type `reservation-salle`.

---

## 🧱 Principe général

1. Appeler l’API GitHub :  
   `GET https://api.github.com/repos/{owner}/{repo}/tags`
2. Récupérer le **premier tag** retourné (le plus récent).
3. **Mettre le résultat en cache** (ex : 1h) pour éviter de spammer l’API GitHub.
4. Exposer cette version comme **variable globale Twig** (`app_version`).
5. L’afficher dans le **footer** de la mise en page principale.

---

## ✅ Prérequis

- Application Symfony 6/7/8
- `symfony/http-client`
- `symfony/cache`
- Twig configuré
- Un dépôt GitHub public (ex. `frdemoulin08/reservation-salle`)

---

## 🧩 Étape 1 – Déclarer le dépôt GitHub en paramètre

`config/services.yaml`

```yaml
parameters:
    github.repo: 'frdemoulin08/reservation-salle'
```

---

## 🛠️ Étape 2 – Service GitHubVersionService

`src/Service/GitHubVersionService.php`

```php
<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GitHubVersionService
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        private string $githubRepo
    ) {}

    public function getLatestTag(): ?string
    {
        return $this->cache->get('github_latest_tag', function (ItemInterface $item) {
            $item->expiresAfter(3600);

            try {
                $response = $this->client->request(
                    'GET',
                    "https://api.github.com/repos/{$this->githubRepo}/tags",
                    [
                        'headers' => [
                            'Accept' => 'application/vnd.github+json',
                        ],
                        'timeout' => 5,
                    ]
                );

                $tags = $response->toArray();
                return $tags[0]['name'] ?? null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}
```

---

## 🧷 Étape 3 – Déclaration du service

`config/services.yaml`

```yaml
services:
    App\Service\GitHubVersionService:
        arguments:
            $githubRepo: '%github.repo%'
```

---

## 🧩 Étape 4 – Variable globale Twig

`src/Twig/AppExtension.php`

```php
<?php

namespace App\Twig;

use App\Service\GitHubVersionService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private GitHubVersionService $githubVersionService
    ) {}

    public function getGlobals(): array
    {
        return [
            'app_version' => $this->githubVersionService->getLatestTag(),
        ];
    }
}
```

---

## 🎨 Étape 5 – Footer Twig

```twig
<footer class="text-xs text-gray-500 text-center py-4">
    {% if app_version %}
        Version {{ app_version }}
    {% else %}
        Version non disponible
    {% endif %}
</footer>
```

---

## 🔐 Variante recommandée (appli interne / audit)

Injecter la version au déploiement via une variable d’environnement :

```bash
APP_VERSION=$(git describe --tags --abbrev=0)
```

Puis en Twig :

```twig
Version {{ app.request.server.get('APP_VERSION') ?? 'version non définie' }}
```

---

## 🧭 Choix de la stratégie

| Contexte | Recommandation |
|--------|----------------|
| Appli publique | API GitHub + cache Symfony |
| Appli interne | Version injectée au build |

---

## 📎 Résumé

- Service dédié + cache
- Variable globale Twig
- Footer propre et audit‑compatible
