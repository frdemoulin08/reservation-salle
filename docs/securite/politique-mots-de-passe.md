# Politique de contraintes des mots de passe

## Objectif
Ce document fixe la politique de complexité des mots de passe et sert de référence pour les audits de sécurité. Les règles doivent être appliquées **côté interface** (guidage utilisateur) **et côté serveur** (validation bloquante).

## Portée
- Création de compte utilisateur
- Réinitialisation / changement de mot de passe
- Toute fonctionnalité future manipulant un mot de passe

## Contexte d'usage
- Les comptes **métier** (gestionnaires, superviseurs, admins métier) sont créés par l'admin.
- Les **usagers** définiront eux-mêmes leur mot de passe lors de leur création de compte.
  - Cette politique s'applique dans les deux cas.

## Règles obligatoires
1) **Longueur**
- Entre **12 et 64 caractères**.

2) **Catégories de caractères**
- Le mot de passe **doit contenir les 4 catégories** suivantes :
  - minuscules (a–z)
  - majuscules (A–Z)
  - chiffres (0–9)
  - caractères spéciaux autorisés

3) **Jeu de caractères autorisés**
- Lettres non accentuées (A–Z, a–z)
- Chiffres (0–9)
- Caractères spéciaux autorisés (37) :
  - `!"#$%&'()*+,-./:;<=>?@[\]^_{|}~`
  - `€£¥§¤`

4) **Caractères interdits**
- Espaces
- Caractères accentués
- Caractères de contrôle
- Tout caractère en dehors de la liste autorisée ci-dessus

## Implémentation actuelle
### Front (guidage utilisateur)
- Composant : `templates/components/_password_requirements.html.twig`
- Logique dynamique : `assets/js/helpers/password-requirements.js`

### Back (validation bloquante)
- **À aligner systématiquement** sur les règles de ce document.
- Exemple recommandé :
  - Contrainte longueur (12–64)
  - Contrainte regex (jeu de caractères autorisés)
  - Contrainte “4 catégories” (minuscules/majuscules/chiffres/spéciaux)

## Expression régulière de référence
- Regex d’autorisation (full match) :

```
^[A-Za-z0-9!"#$%&'()*+,-./:;<=>?@[\]^_{|}~`€£¥§¤]+$
```

## Exemples
- ✅ Valide : `Abcdef12!@#` (12 caractères, 4 catégories)
- ✅ Valide : `S3curite!€2026` (4 catégories, caractères autorisés)
- ❌ Invalide : `motdepasse` (pas de majuscules/chiffres/spéciaux)
- ❌ Invalide : `Motdepasse1` (pas de caractère spécial)
- ❌ Invalide : `MötDePasse1!` (accent interdit)

## Notes d’audit
- Les règles ci-dessus sont **non optionnelles**.
- Toute évolution doit être tracée dans ce document et alignée sur le front et le back.
