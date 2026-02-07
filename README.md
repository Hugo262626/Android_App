# Android App — API Réseau social étudiant

API **REST** développée avec **Laravel**, servant de backend à une application mobile (branche `dart`) et une app web WPA pour un **réseau social étudiant** : profils, géolocalisation, messagerie, interactions sociales.

---

## Objectif du projet

Créer une plateforme mobile permettant aux étudiants de :
- créer et gérer un **profil**
- découvrir des étudiants **à proximité** (position géographique)
- échanger via **messages** (messagerie privée)

L’API centralise :
- l’authentification et l’autorisation
- la persistance des données (utilisateurs, messages, localisations…)
- les règles métier (visibilité, sécurité, validation)

---

## Fonctionnalités

### Authentification
- inscription / connexion
- gestion de session via tokens JWT

### Profils étudiants
- CRUD profil (pseudo, bio, photo, école/filière, centres d’intérêt…)
- visibilité / paramètres de confidentialité
- recherche / filtres (école, filière, intérêts)

### Géolocalisation
- mise à jour de position GPS (latitude /longitude)
- consultation d’utilisateurs **proches** (rayon, tri par distance)

### Messagerie
- création de conversation (1–1)
- listing conversations


### API (technique)
- endpoints REST (JSON)
- validation des payloads côté serveur
- pagination sur listings (utilisateurs, messages, posts)
- gestion des erreurs (codes HTTP + messages structurés)

---

## Stack technique

### API REST (branche master)
- **PHP 8+**
- framework **Laravel**
- **MySQL** (ou autre SGBDR compatible)
- **Eloquent ORM**
- jetons JWT pour l’authentification

### App mobile Android (branche dart)
- **Dart**
- **Flutter**

### App web PWA (branche master)
- **Bootstrap 5**
