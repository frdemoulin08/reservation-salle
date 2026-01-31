# Starter kit ready — Checklist

Cette checklist aide à valider qu’un projet Symfony est prêt à être utilisé comme **starter kit** pour un futur fork.

---

## ✅ Fondations projet

- [ ] `README.md` clair (objectif, stack, quickstart)
- [ ] `.env.example` ou `.env.dist` fourni
- [ ] Convention de nommage (routes, templates, services, modules)
- [ ] Structure de dossiers validée (admin, public, docs, tests, assets)

---

## ✅ Environnements & exécution

- [ ] `compose.yaml` (DB + mailer + outil admin DB)
- [ ] Commandes `make` ou scripts d’init (migrations + fixtures + assets)
- [ ] Variables clés documentées (DB, mailer, app name, mailer from)
- [ ] Mode test isolé (DB test dédiée)

---

## ✅ Qualité & sécurité

- [ ] Lint PHP + lint Twig
- [ ] Analyse statique (PHPStan/Psalm) + baseline si besoin
- [ ] CS fixer + standard de code
- [ ] Politique sécurité (password, reset, roles) documentée
- [ ] Headers de sécurité (CSP, HSTS) planifiés

---

## ✅ Tests & CI

- [ ] Tests unitaires + fonctionnels minimum
- [ ] Tests DB (fixtures + purge)
- [ ] CI qui exécute lint + tests + build assets
- [ ] Rapport de couverture (optionnel)

---

## ✅ UX / UI

- [ ] Design system / tokens (couleurs, typographie)
- [ ] Bibliothèque de composants Twig (tables, modals, forms)
- [ ] Accessibilité minimale (ARIA, labels, contrastes)

---

## ✅ Documentation

- [ ] Documentation technique à jour (forms, tables, reset password, logs)
- [ ] Guide de contribution / conventions
- [ ] Guide de tests
- [ ] Checklist de release + versioning

---

## ✅ Opérations

- [ ] Observabilité (logs, erreurs, journalisation sensible)
- [ ] Stratégie de sauvegarde / purge données
- [ ] Migration unique propre (si DB init simple)
- [ ] Tag/version affichée côté UI (footer)

---

## ✅ À valider avant fork

- [ ] Nettoyage des données de démo non utiles
- [ ] Valeurs par défaut cohérentes
- [ ] Documentation adaptée au nouveau contexte projet
- [ ] Checklist passée en revue + validée par un pair
