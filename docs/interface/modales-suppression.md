# Modales de suppression (guide dev)

Ce guide explique comment mettre en place une **modale de suppression** avec une **modale unique** et un **formulaire sécurisé** (CSRF), en suivant le pattern utilisé sur l’admin.

## Objectifs
- Une seule modale par page (pas une par ligne).
- L’action du formulaire est définie **au clic** via JS.
- Sécurité côté serveur : **POST + CSRF**.

## Prérequis
- Le helper JS des modales est chargé : `assets/js/helpers/modal.js` via `assets/js/pages/base_template.js`.
- Le template de la page intègre `assets/app.js` (déjà le cas dans le layout de base).

## Étapes

### 1) Ajouter le bouton “Supprimer” dans le tableau
Dans le tableau, chaque bouton doit fournir l’URL de suppression et, optionnellement, un libellé à afficher.

```twig
<button
  type="button"
  data-modal-target="site-delete-modal"
  data-modal-toggle="site-delete-modal"
  data-delete-url="{{ path('app_admin_sites_delete', { id: site.id }) }}"
  data-delete-name="{{ site.name }}"
>
  Supprimer
</button>
```

**Pourquoi :**
- `data-modal-target` / `data-modal-toggle` ouvrent la modale unique.
- `data-delete-url` donne l’action exacte du formulaire (générée côté serveur).
- `data-delete-name` permet d’afficher le nom si besoin.

### 2) Insérer la modale unique (factorisée)
Utiliser le partial commun : `templates/admin/_partials/_delete_modal.html.twig`.

```twig
{% include 'admin/_partials/_delete_modal.html.twig' with {
  modal_id: 'site-delete-modal',
  message: 'Confirmez la suppression de ce site ?',
  csrf_token_id: 'delete_site',
  show_name: false
} %}
```

**Paramètres utiles :**
- `modal_id` : doit **matcher** le `data-modal-target` du bouton.
- `message` : texte de confirmation.
- `csrf_token_id` : ID utilisé par `csrf_token()` et vérifié côté contrôleur.
- `show_name` : affiche un placeholder `data-delete-name` si activé.

### 3) Vérifier le contrôleur (CSRF)
Le contrôleur doit valider le token **avec le même ID**.

```php
if (!$this->isCsrfTokenValid('delete_site', (string) $request->request->get('_token'))) {
    return $this->redirectToRoute('app_admin_sites_index');
}
```

### 4) (Optionnel) Activer le CSRF stateless
Si l’app utilise des tokens stateless : ajouter l’ID dans `config/packages/csrf.yaml`.

```yaml
framework:
  csrf_protection:
    stateless_token_ids:
      - delete_site
```

## Fonctionnement JS (résumé)
Le JS lit les data-attrs au clic sur “Supprimer” et :
- ouvre la modale,
- injecte `data-delete-url` dans `form.action`,
- injecte `data-delete-name` si `show_name` est activé.

Le formulaire reste un **POST classique** (pas AJAX) :
- le serveur vérifie le CSRF,
- exécute la suppression,
- redirige avec un flash.

## Checklist rapide
- [ ] Bouton avec `data-modal-target` + `data-delete-url`
- [ ] Modale unique incluse avec le bon `modal_id`
- [ ] Token CSRF correct côté Twig et contrôleur
- [ ] JS chargé (`assets/app.js`)

