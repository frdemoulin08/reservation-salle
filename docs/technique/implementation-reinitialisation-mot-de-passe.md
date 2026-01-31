# Documentation technique – Reset password (implémentation actuelle)

## 1. Objet

Ce document décrit l’implémentation réelle du reset password dans ce projet Symfony (routes, configuration, persistance, sécurité, journalisation et tests). Il remplace la version générique.

---

## 2. Stack et points d’entrée

- Bundle : **SymfonyCasts ResetPasswordBundle**.
- Contrôleur : `App\Controller\Security\ResetPasswordController`.
- Formulaires :
  - `App\Form\ResetPasswordRequestFormType` (demande)
  - `App\Form\ChangePasswordFormType` (changement)
- Templates :
  - `templates/security/reset_password/request.html.twig`
  - `templates/security/reset_password/check_email.html.twig`
  - `templates/security/reset_password/reset.html.twig`
  - `templates/security/reset_password/email.html.twig`

---

## 3. Routes exposées

| Route | Nom | Rôle | Template |
|---|---|---|---|
| `/mot-de-passe-oublie` | `app_forgot_password_request` | Demande de reset | `request.html.twig` |
| `/mot-de-passe-oublie/envoye` | `app_check_email` | Message neutre | `check_email.html.twig` |
| `/reinitialiser-mot-de-passe/{token}` | `app_reset_password` | Nouveau mot de passe | `reset.html.twig` |

Accès public configuré dans `config/packages/security.yaml` :
- `^/mot-de-passe-oublie`
- `^/reinitialiser-mot-de-passe`

---

## 4. Configuration et paramètres

- **ResetPasswordBundle** :
  - `config/packages/reset_password.yaml`
  - Repository : `App\Repository\ResetPasswordRequestRepository`
- **Rate limiting** (par IP + par email) : `config/packages/rate_limiter.yaml`
  - `password_reset_ip` : 5 requêtes / heure
  - `password_reset_email` : 5 requêtes / heure
  - En test : 1000 / heure pour éviter les blocages.
- **Expéditeur email** :
  - Paramètre `app.mailer_from` (défini dans `config/services.yaml`)
  - Valeur issue de `MAILER_FROM` dans `.env` / `.env.local`

---

## 5. Modèle de données

### 5.1. Jetons de reset (bundle)

- Entité : `App\Entity\ResetPasswordRequest`
- Table : `reset_password_request`
- Champs principaux (via `ResetPasswordRequestTrait`) :
  - `selector` (public, court)
  - `hashed_token` (stocké en hash)
  - `requested_at` / `expires_at`
  - `user_id`

Aucune durée personnalisée n’est définie dans `config/packages/reset_password.yaml` : la durée par défaut du bundle s’applique.

### 5.2. Journalisation des événements

- Entité : `App\Entity\ResetPasswordLog`
- Table : `log_reset_password`
- Champs :
  - `event_type` (RESET_REQUEST / RESET_SUCCESS / RESET_INVALID)
  - `identifier` (email saisi)
  - `user_id` (nullable)
  - `occurred_at`
  - `ip_address`, `user_agent`
  - `failure_reason` (nullable)

---

## 6. Flux fonctionnel implémenté

### 6.1. Demande de reset

1. Saisie email via `ResetPasswordRequestFormType`.
2. **Rate limiting IP** appliqué dès la soumission.
3. Si formulaire valide : **rate limiting email**.
4. Journalisation `RESET_REQUEST` (même si l’utilisateur n’existe pas).
5. Si user absent/inactif → redirection vers la page neutre (`/mot-de-passe-oublie/envoye`).

### 6.2. Génération et envoi du lien

- Suppression des anciennes demandes : `removeRequests($user)`.
- Génération du token via le bundle.
- Envoi d’un email HTML (`email.html.twig`).
- Stockage du token en session pour la page « email envoyé ».

### 6.3. Réinitialisation

- Validation du token par le bundle.
- En cas d’échec : log `RESET_INVALID` + flash « lien invalide/expiré ».
- En cas de succès :
  - `removeResetRequest($token)`
  - `cleanSessionAfterReset()`
  - Hash du nouveau mot de passe + `flush`
  - Log `RESET_SUCCESS`
  - Invalidation de session si l’utilisateur connecté correspond

---

## 7. Validation des formulaires

### 7.1. Demande

`ResetPasswordRequestFormType` :
- `NotBlank` (clé `user.email.required`)
- `Email` + `Regex` (clé `user.email.invalid`)

### 7.2. Changement de mot de passe

`ChangePasswordFormType` :
- `NotBlank` (clé `user.password.required`)
- `Length` 12–64 (clés `user.password.min_length`, `user.password.max_length`)
- `Regex` sur caractères autorisés (`user.password.invalid_chars`)
- `Regex` sur 4 catégories (`user.password.categories`)

---

## 8. Journalisation visible en admin

Un écran d’administration liste les événements :
- Route : `/administration/reset-password-logs`
- Contrôleur : `App\Controller\Administration\ResetPasswordLogController`
- Template : `templates/admin/reset_password_logs/index.html.twig`

---

## 9. Tests et fixtures

- Tests fonctionnels : `tests/Functional/Security/ResetPasswordTest.php`
- Rate limit relâché en environnement `test` (voir `rate_limiter.yaml`).
- Fixtures : `ResetPasswordLogFixtures` (100 logs de test), chargées via `DatabaseWebTestCase`.

---

## 10. Points non implémentés (à considérer si besoin)

- CAPTCHA adaptatif après plusieurs tentatives.
- Alertes/supervision sur volumes anormaux.
- Durée de validité personnalisée si exigences métier spécifiques.
