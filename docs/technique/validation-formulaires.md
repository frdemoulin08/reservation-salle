# Validation des formulaires (front + back)

Ce document decrit le fonctionnement de la validation front et back dans l’admin.

## 1) Validation back (Symfony)
La validation serveur est la reference. Elle vit dans:
- `src/Form/UserType.php` (contraintes Symfony),
- `src/Entity/*` pour les contraintes globales (ex: `UniqueEntity`).

Exemples utilises:
- `NotBlank` pour les champs obligatoires,
- `Email` + `Regex` pour l’email (TLD obligatoire),
- `Count(min=1)` pour forcer au moins un role.

### Pattern email partage (front/back)
```
^[^@\s]+@[^@\s]+\.[^@\s]+$
```

## 2) Validation front (JS)
Helper: `assets/js/helpers/form-validation.js`

Activation:
- `data-validate-form` sur le `<form>`,
- `data-validate-field` sur le conteneur de champ,
- `data-validate-input` sur l’input,
- `data-validate-error` sur le bloc d’erreur.

Le helper gere:
- champs requis,
- format email (typeMismatch),
- pattern custom (`data-validate-pattern`),
- groupes de checkboxes (`data-validate-group="checkboxes"`).

### Comportement UX
- validation au submit,
- validation live apres le premier input,
- blur n’affiche rien si l’utilisateur n’a jamais tape.

## 3) Groupes de checkboxes (roles)
Pour un groupe checkbox:
```twig
<div data-validate-field data-validate-group="checkboxes"
     data-validate-required-message="Au moins un role est obligatoire">
    ...
    <div data-validate-error class="...">{{ form_errors(form.roleEntities) }}</div>
</div>
```

## 4) Styles d’erreur (light/dark)
Utiliser les tokens suivants:
- texte: `text-danger dark:text-fg-danger-strong`
- bordure: `border-danger/60 dark:border-danger-subtle`
- focus: `focus:ring-danger-medium/40 dark:focus:ring-danger`

## 5) Password
La validation du mot de passe est double:
- Back: contraintes dans `src/Form/UserType.php`
- Front: `assets/js/helpers/password-requirements.js` + composant `templates/components/_password_requirements.html.twig`

## 6) Compilation front
Si vous modifiez le JS, rebuild:
```
npm run dev
```
