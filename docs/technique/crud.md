# CRUD — snippets standard (admin)

Ce document centralise des snippets réutilisables pour uniformiser les CRUD.

## Suppression (pattern standard)

### 1) Contrôleur (delete)
```php
#[Route('/{id}/delete', name: 'xxx_delete', methods: ['POST'])]
public function delete(Request $request, Entity $entity, EntityManagerInterface $em): Response
{
    if (!$this->isCsrfTokenValid('delete_xxx', (string) $request->request->get('_token'))) {
        return $this->redirectToRoute('xxx_index');
    }

    $em->remove($entity);
    $em->flush();

    $this->addFlash('success', 'Supprimé.');

    return $this->redirectToRoute('xxx_index');
}
```

### 2) Bouton “Supprimer” (table)
```twig
<button
  type="button"
  data-modal-target="xxx-delete-modal"
  data-modal-toggle="xxx-delete-modal"
  data-delete-url="{{ path('xxx_delete', { id: item.id }) }}"
  data-delete-name="{{ item.name }}"
>
  Supprimer
</button>
```

### 3) Modale unique (partial factorisé)
```twig
{% include 'admin/_partials/_delete_modal.html.twig' with {
  modal_id: 'xxx-delete-modal',
  message: 'Confirmez la suppression de cet élément ?',
  csrf_token_id: 'delete_xxx',
  show_name: false
} %}
```

### 4) CSRF stateless (si activé)
```yaml
framework:
  csrf_protection:
    stateless_token_ids:
      - delete_xxx
```

## Références
- Pattern JS : `assets/js/helpers/modal.js`
- Modale factorisée : `templates/admin/_partials/_delete_modal.html.twig`
- Guide complet des modales : `docs/interface/modales-suppression.md`
