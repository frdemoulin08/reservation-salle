## Convention de nommage des entités et tables de journalisation

Les entités de journalisation suivent une convention distincte entre le code applicatif et la base de données, afin de concilier lisibilité du code et exploitation technique.

- **Côté applicatif (entités Doctrine)**, les entités de log sont nommées avec le suffixe `Log` (ex. `AuthenticationLog`), conformément aux conventions de lecture usuelles et aux standards du framework Symfony.
- **Côté base de données**, les tables de journalisation sont préfixées par `log_` (ex. `log_authentication`), afin de regrouper visuellement les journaux techniques lors du tri alphabétique et de faciliter l’exploitation (audit, purge, supervision).

Cette dissociation volontaire permet :
- une séparation claire entre données métier et données techniques ;
- une meilleure lisibilité du code applicatif ;
- une exploitation simplifiée des journaux en base de données ;
- une cohérence avec les exigences de sécurité, d’audit et de gouvernance du projet.

### Convention sur les identifiants utilisateurs

L’identifiant technique interne est la clé primaire `id`, unique et stable dans le temps.  
Un identifiant lisible peut être dérivé à la volée (ex. `user-000123`) sans être stocké en base.

L’adresse email est l’identifiant de connexion. Elle est unique, normalisée (minuscule) et modifiable.

Pour les usages publics (URLs exposées), un identifiant opaque `publicIdentifier` (UUID/ULID) est utilisé afin de limiter les risques de manipulation d’URL.

### Identifiants dans les URLs

Dans le backoffice d’administration, les pages de détail des utilisateurs sont adressées par l’identifiant interne numérique (`id`) :

- Exemple : `/admin/users/12`.

Ce choix est acceptable car :

- l’accès au backoffice est strictement réservé aux profils habilités ;
- des contrôles d’accès sont systématiquement appliqués côté serveur.

Pour les parties de l’application éventuellement exposées à des profils non administrateurs, il est recommandé d’utiliser des identifiants **opaques** (ex. `publicId` de type UUID/ULID) ou des routes sans identifiant explicite (ex. `/mon-compte`), afin de limiter les risques de manipulation des URLs (IDOR).

### Libellés de formulaires (champs optionnels)

Convention UI : **les champs optionnels** affichent « (optionnel) » dans leur libellé, **aucune mention** pour les champs obligatoires.

Exemples :

- `Adresse (optionnel)`
- `Capacité (optionnel)`

### Couleurs des actions (boutons)

Convention UI : les boutons d’**export** utilisent la variante **success** (vert Flowbite).

Exemple :

- `Exporter CSV` → `variant: 'success'`

### Lignes de tableau cliquables (backoffice)

Convention UI : les lignes de tableaux d’administration doivent être cliquables pour accéder à la page de détail.

Mise en place :
- ajouter `data-row-link="..."` sur le `<tr>`
- ajouter `hover:bg-neutral-secondary-medium` et `cursor-pointer` sur la ligne
- le comportement est géré par le JS global (`assets/js/table-ajax.js`) et **n’interfère pas** avec les boutons/links internes

Exemple :

```twig
<tr class="border-b border-default bg-neutral-primary-soft hover:bg-neutral-secondary-medium cursor-pointer"
    data-row-link="{{ path('app_admin_users_show', { id: user.id }) }}">
    ...
</tr>
```

### Fichiers d’environnement

- `.env` : valeurs par défaut du projet (commitées).
- `.env.example` : **template à copier** pour chaque poste/dev/CI (commité).
- `.env.local` : valeurs locales et **secrets** (non commitées).
- Les variables essentielles sont : `APP_ENV`, `APP_DEBUG`, `APP_SECRET`, `DATABASE_URL`, `MAILER_DSN`, `MAILER_FROM`.
- En CI, privilégier des variables d’environnement injectées plutôt que `.env.local`.

### Script d’initialisation (dev)

Un script d’initialisation est disponible pour accélérer la mise en route :

```bash
./scripts/init-dev.sh
```

Il installe les dépendances, build les assets, applique les migrations et charge les fixtures (purge DB).
