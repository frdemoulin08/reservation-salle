# Gestion des comptes utilisateurs et des accès  
**Application de réservation et de facturation des salles**  
*Document de cadrage – Sécurité / RGPD / Audit CNIL*

---

## 1. Objectifs du dispositif

Le dispositif de gestion des comptes utilisateurs vise à :

- garantir un accès sécurisé et maîtrisé à l’application ;
- appliquer le principe de moindre privilège ;
- assurer une traçabilité des accès et des actions sensibles ;
- répondre aux exigences de sécurité et de protection des données personnelles (RGPD / CNIL).

---

## 2. Typologie des utilisateurs et rôles applicatifs

L’application repose sur des **rôles fonctionnels clairement identifiés**, exprimés en anglais conformément aux standards techniques (Symfony / sécurité applicative).

### Rôles définis

| Intitulé fonctionnel (EN) | Rôle Symfony | Description |
|---------------------------|--------------|-------------|
| Super Administrator | `ROLE_SUPER_ADMIN` | Gouvernance globale de l’application (technique et fonctionnelle). |
| Business Administrator | `ROLE_BUSINESS_ADMIN` | Paramétrage fonctionnel de l’application. |
| Application Manager | `ROLE_APP_MANAGER` | Gestion opérationnelle (réservations, relation usager). |
| Supervisor | `ROLE_SUPERVISOR` | Accès au pilotage, tableaux de bord et indicateurs. |
| Authenticated User | `ROLE_USER` | Utilisation standard de l’application après authentification. |
| Public Access | `PUBLIC_ACCESS` | Accès aux pages publiques sans authentification. |

---

## 3. Modèle d’authentification

### 3.1 Accès public

- Les pages publiques sont accessibles sans authentification.
- Aucun compte utilisateur n’est requis.
- L’accès est géré via le mécanisme Symfony `PUBLIC_ACCESS`.

### 3.2 Accès authentifié

- Toute personne authentifiée dispose automatiquement du rôle technique `ROLE_USER`.
- Les droits fonctionnels sont déterminés par les rôles métier supplémentaires éventuellement attribués.

---

## 4. Gestion des rôles et des privilèges

### Principes généraux

- Les rôles sont **fonctionnels et non hiérarchiques**.
- Les droits sont strictement limités au périmètre des missions.
- Un utilisateur peut cumuler plusieurs rôles sans élévation automatique de privilèges.

### Spécificité du Super Administrator

- Seul le rôle `ROLE_SUPER_ADMIN` est autorisé à :
  - attribuer ou modifier les rôles des utilisateurs ;
  - changer de profil (switch de rôle) à des fins d’administration ou de support.
- Cette capacité est strictement encadrée afin de prévenir toute dérive de privilèges.

### Relation entre rôles

- Le rôle `ROLE_BUSINESS_ADMIN` inclut l’ensemble des prérogatives du rôle `ROLE_APP_MANAGER`.
- Aucun autre lien hiérarchique implicite n’est défini entre les rôles.

---

## 5. Sécurité et bonnes pratiques

### Principe de moindre privilège

Chaque utilisateur ne dispose que des droits strictement nécessaires à l’exercice de ses missions.

### Séparation des responsabilités

- Paramétrage fonctionnel ≠ exploitation quotidienne ≠ pilotage.
- Réduction des risques d’erreur, d’abus ou de mauvaise manipulation.

### Désactivation des comptes

- Les comptes inactifs ou devenus non justifiés peuvent être désactivés sans suppression immédiate.
- Les suppressions définitives respectent les règles de conservation des données.

---

## 6. Traçabilité et auditabilité

Afin de répondre aux exigences de contrôle :

- les connexions et actions sensibles peuvent être journalisées ;
- les modifications de rôles et de paramètres structurants sont traçables ;
- les journaux sont conservés pour une durée proportionnée aux objectifs de sécurité.

---

## 7. Conformité RGPD / CNIL

Le dispositif respecte les principes suivants :

- **Finalité** : les comptes utilisateurs sont créés uniquement pour l’accès à l’application.
- **Minimisation** : seules les données strictement nécessaires sont collectées.
- **Sécurité** : contrôle d’accès, authentification, journalisation.
- **Responsabilité** : rôles clairement définis, documentés et audités.

---

## 8. Synthèse

- Aucun accès privilégié sans authentification.
- Aucun rôle métier sans justification fonctionnelle.
- Une gouvernance claire et documentée des habilitations.
- Un modèle compréhensible, auditable et maintenable dans le temps.
