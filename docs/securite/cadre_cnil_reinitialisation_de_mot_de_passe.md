# Cadre de sécurisation – Réinitialisation de mot de passe (Reset password)

## 1. Objectif du document

Le présent document a pour objet de cadrer les pratiques applicatives relatives à la réinitialisation des mots de passe, conformément aux recommandations de la CNIL, dans le cadre des applications web développées et maintenues par le service Ressources numériques.

Il constitue un référentiel commun, destiné à être présenté lors d’audits de sécurité commandés par la DSI, et à servir de base de conformité RGPD et SSI.

---

## 2. Références réglementaires et sources CNIL

Les recommandations ci-dessous s’appuient principalement sur :

- CNIL – *Recommandation relative aux mots de passe* (délibération n° 2022-100 du 21 juillet 2022)
- CNIL – Annexe 2 : Tableau de correspondance (questions 49 à 55)
- CNIL – Guide de la sécurité des données personnelles (édition 2024)

Sources officielles :
- https://www.cnil.fr/fr/mots-de-passe
- https://www.cnil.fr/sites/default/files/atoms/files/recommandation-mots-de-passe_annexe2_tableau-correspondance.pdf
- https://www.cnil.fr/sites/default/files/2024-03/cnil_guide_securite_personnelle_2024.pdf

---

## 3. Principes généraux CNIL applicables au reset password

### 3.1. Usage d’un jeton ou lien sécurisé

La réinitialisation du mot de passe doit s’effectuer via un mécanisme de type lien ou jeton (token) de sécurité, et non par l’envoi d’un mot de passe temporaire.

- Aucun mot de passe ne doit être transmis par courriel.
- Le jeton doit être généré de manière aléatoire et imprévisible.

### 3.2. Canal de transmission

Le lien ou jeton de réinitialisation doit être transmis exclusivement via un canal préalablement validé, généralement l’adresse électronique de l’utilisateur :

- L’adresse doit avoir fait l’objet d’une validation préalable.
- Une adresse électronique récemment modifiée ne doit pas être utilisable immédiatement pour une procédure de réinitialisation.
- En cas de changement d’adresse électronique, l’ancienne adresse doit être notifiée.

### 3.3. Durée de validité

Conformément aux recommandations de la CNIL :

- La durée de validité d’un lien ou jeton de réinitialisation doit être limitée.
- Cette durée ne doit en aucun cas excéder 24 heures.

Dans les faits, une durée plus courte (1 à 4 heures) est recommandée afin de réduire la fenêtre d’exploitation en cas de compromission.

### 3.4. Usage unique et révocation

- Chaque jeton de réinitialisation doit être à usage unique.
- Dès qu’un nouveau jeton est émis, tous les jetons précédemment générés pour le même compte doivent être révoqués.
- Un jeton utilisé ou expiré ne doit plus jamais être accepté.

### 3.5. Protection contre l’énumération de comptes

Le système ne doit pas permettre de déterminer si un compte existe ou non :

- Le message de réponse à une demande de réinitialisation doit être neutre.
- Exemple : « Si un compte correspond à cette adresse, un courriel de réinitialisation a été envoyé. »

---

## 4. Journalisation et traçabilité

Les opérations suivantes doivent faire l’objet d’une journalisation adaptée :

- Demande de réinitialisation (date, adresse IP, identifiant technique si connu)
- Utilisation du lien de réinitialisation
- Modification effective du mot de passe

Ces journaux doivent être protégés, horodatés et conservés conformément à la politique de conservation des données du système d’information.

---

## 5. Bonnes pratiques complémentaires

- Mise en place de mécanismes de limitation de fréquence (rate limiting) sur le formulaire « mot de passe oublié »
- Blocage temporaire ou mécanisme de protection après tentatives répétées
- Notification à l’utilisateur après changement effectif du mot de passe
- Invalidation des sessions actives après réinitialisation du mot de passe

---

## 6. Portée du document

Ces règles s’appliquent à l’ensemble des applications web, qu’elles soient :

- internes au Conseil départemental des Ardennes,
- ou accessibles à des utilisateurs externes.

Les modalités techniques d’implémentation peuvent varier selon le contexte d’hébergement et d’exposition, tout en respectant strictement les principes ci-dessus.

