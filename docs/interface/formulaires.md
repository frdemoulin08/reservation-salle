# Formulaires (UI)

Ce guide explique comment construire un formulaire coherent (styles, erreurs, validations) et comment reutiliser les partials.

## 1) Regles generales
- Toujours desactiver la validation HTML5 native: `novalidate`.
- Toujours afficher les erreurs serveur via `form_errors`.
- Toujours garder la validation back (Symfony), meme si une validation front existe.

## 2) Partial reutilisable: `_form_field.html.twig`
Chemin: `templates/components/_form_field.html.twig`

Ce partial gere:
- label + input + message d'erreur,
- classes de base + etat d'erreur (light/dark),
- data-attrs pour la validation front.

### Exemple (champ texte requis)
```twig
{{ include('components/_form_field.html.twig', {
    field: form.lastname,
    required_message: 'Le nom est obligatoire'
}) }}
```

### Exemple (email requis + format)
```twig
{{ include('components/_form_field.html.twig', {
    field: form.email,
    wrapper_class: 'md:col-span-2',
    required_message: 'L’email est obligatoire',
    email_message: 'Le format de l’email est invalide',
    pattern: '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$',
    pattern_message: 'Le format de l’email est invalide'
}) }}
```

### Exemple (input avec attrs custom)
```twig
{{ include('components/_form_field.html.twig', {
    field: form.mobilePhone,
    input_attr: {
        placeholder: '06 12 34 56 78',
        'data-phone-mask': true
    }
}) }}
```

## 3) Champs specifiques (hors partial)
Certains champs ne passent pas par le partial:
- mot de passe (toggle, generateur, requirements),
- elements tres custom (switch, radios specifiques, etc).

Dans ces cas, reutiliser les memes classes d'erreur que le partial:
- texte erreur: `text-danger dark:text-fg-danger-strong`
- bordure erreur: `border-danger/60 dark:border-danger-subtle`
- focus ring: `focus:ring-danger-medium/40 dark:focus:ring-danger`

## 4) Activer la validation front
Sur le formulaire:
```twig
{{ form_start(form, { attr: {
    class: '...',
    novalidate: 'novalidate',
    'data-validate-form': 'true'
} }) }}
```

Le JS est global (voir `assets/js/helpers/form-validation.js`) et s’active sur les champs qui exposent les data-attrs.

## 5) Checklist rapide
- [ ] `novalidate` sur le form
- [ ] `data-validate-form` sur le form
- [ ] partial `_form_field` pour les champs standard
- [ ] erreurs back affichees via `form_errors`
- [ ] classes d'erreur coherentes light/dark
- [ ] validation back (Symfony) toujours presente
