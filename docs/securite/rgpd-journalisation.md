# Note RGPD – Journalisation des connexions

## Finalité

La journalisation des connexions vise exclusivement la **sécurité** du système d’information et des données traitées par l’application, notamment :

- la détection d’accès non autorisés ou de tentatives malveillantes ;
- l’analyse et l’investigation d’incidents de sécurité ;
- la traçabilité des accès au système.

Cette journalisation ne poursuit aucune finalité de surveillance individuelle ou de contrôle de l’activité métier des utilisateurs.

---

## Données enregistrées (principe de minimisation)

Afin de rester proportionnée au regard de la finalité poursuivie, la journalisation des connexions conserve uniquement les données suivantes :

- identifiant utilisateur interne (lorsqu’un compte existe) ;
- identifiant saisi lors de la tentative de connexion (adresse email), le cas échéant ;
- date et heure de l’événement ;
- type d’événement :
  - connexion réussie ;
  - connexion échouée ;
  - déconnexion ;
- adresse IP ;
- user-agent (navigateur / appareil).

Aucune donnée sensible n’est enregistrée (mot de passe, contenu métier, données fonctionnelles).

La CNIL recommande la traçabilité des opérations avec l’identifiant de l’auteur, la date/heure et la nature de l’opération afin de détecter les anomalies.
Dans ce projet, l’adresse IP et le user-agent sont ajoutés afin de disposer d’un contexte technique minimal, strictement limité à des fins de sécurité.

---

## Cas des échecs d’authentification

Les tentatives de connexion **échouées** sont journalisées afin de permettre la détection de comportements anormaux (ex. tentatives répétées, attaques par force brute).

Dans certains cas (email inexistant ou compte non reconnu), il n’est pas possible d’associer l’événement à un utilisateur existant.

Dans ce contexte :

- la référence à l’utilisateur peut être absente (champ utilisateur nullable) ;
- seul l’identifiant saisi (email), l’horodatage et le contexte technique sont conservés.

Ce mécanisme permet une journalisation complète des événements de sécurité, sans création de compte ni enrichissement excessif des données personnelles.

---

## Durée de conservation

- **Journalisation standard** : **6 à 12 mois**.
- **Exceptions** : conservation prolongée uniquement en cas :
  - d’obligation légale ;
  - de gestion de contentieux ;
  - de contrôle interne ;
  - ou d’analyse post-incident de sécurité.

Le projet retient une **durée de conservation par défaut de 12 mois**, paramétrable si nécessaire.

---

## Purge automatique

Une purge automatique des journaux est mise en œuvre afin de garantir le respect des durées de conservation.

Commande utilisée :

php bin/console app:auth-log:purge --days=365

Cette commande est exécutée de manière **hebdomadaire** via une tâche planifiée (CRON).
Elle supprime exclusivement les entrées dont l’ancienneté dépasse la durée de conservation définie.

Par défaut, une **suppression définitive** est appliquée.

Si un besoin statistique durable devait être identifié, une **anonymisation irréversible** pourrait être envisagée, après cadrage avec le DPO.

---

## Sécurité et accès aux journaux

Les journaux de connexion font l’objet de mesures techniques et organisationnelles destinées à :

- limiter l’accès aux seules personnes habilitées ;
- prévenir tout accès non autorisé ou détournement de finalité ;
- garantir l’intégrité des données de journalisation.

L’accès aux journaux est strictement restreint aux profils disposant de responsabilités en matière de sécurité et d’administration de l’application.

---

## Base légale

La base légale du traitement repose sur l’**intérêt légitime** du responsable de traitement lié à la sécurité du système d’information
(RGPD – article 6, paragraphe 1, point f).

Cette base légale est documentée et pourra être validée ou ajustée en lien avec le DPO.
