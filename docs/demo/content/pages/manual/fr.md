---
title: "Manuel"
description: "Guide complet pour l'installation, la configuration et le déploiement de notACMS."
template: page/doc
slug: manuel
menu:
  weight: 20
  label: "Manuel"
---

> ## Démarrage Rapide
> 
> ```bash
> # 1. Cloner
> git clone https://github.com/holas1337/notACMS my-site && cd my-site
> 
> # 2. Configurer la production
> echo "APP_SECRET=$(php -r 'echo bin2hex(random_bytes(32));')" >> .env.local
> echo "URL=yourdomain.com" >> .env.local
> 
> # 3. Déployer
> ./notACMS deploy --prod
> ```
> 
> Le site fonctionne sur `https://yourdomain.com`. Modifier le contenu dans `local/content/`.

---

## Installation (Développement)

notACMS utilise [DDEV](https://ddev.readthedocs.io/) pour le développement local.

**Prérequis :** Docker Desktop (ou Orbstack sur Mac), DDEV 1.22+, Git.

```bash
git clone https://github.com/holas1337/notACMS my-site
cd my-site
ddev start
ddev composer install
ddev build
```

Le site fonctionne sur `https://my-site.ddev.site`.

---

## Structure du Contenu

```
local/content/
├── _site.yaml          # Configuration du site
├── _routes.yaml        # Surcharges d'URLs
├── _tags.yaml          # Traductions des tags
├── pages/              # Pages statiques
│   ├── home/
│   │   ├── en.md
│   │   ├── de.md
│   │   └── pl.md
│   └── about/
│       ├── en.md
│       └── pl.md
└── blog/
    ├── _index_en.md
    └── releases/
        └── v1-0-0/
            ├── en.md
            └── pl.md
```

---

## Frontmatter des Pages

| Champ | Type | Requis | Description |
|---|---|---|---|
| `title` | string | Oui | Titre de la page |
| `slug` | string | Oui | Chemin de l'URL |
| `description` | string | Non | Méta-description |
| `date` | date | Articles | Date de publication |
| `updated` | date | Non | Date de dernière modification |
| `category` | string | Articles | Slug de catégorie |
| `tags` | list | Non | Liste des tags |
| `template` | string | Non | Template Twig |
| `image` | string | Non | Chemin vers l'image de prévisualisation |
| `image_alt` | string | Requis avec `image` | Texte alternatif de l'image (valeur par défaut : titre, si omis) |
| `draft` | bool | Non | Masquer en production |
| `pinned` | date ou false | Non | Épingle l'article jusqu'à cette date (incluse) en haut de toutes les listes ; est automatiquement désépinglé lorsque la date est dépassée. Articles uniquement. |
| `featured` | bool | Non | Projets uniquement, affichage dans la grille sélectionnée |
| `dynamic` | bool | Non | Ignorer le pré-rendu statique |
| `series` | string | Non | Clé de série |
| `series_order` | int | Non | Position dans la série |
| `toc` | bool | Non | Afficher la table des matières |
| `related` | list | Non | Slugs d'articles connexes manuels |
| `menu` | object | Non | Configuration de navigation : `weight` (ordre de tri) et `label` (surcharge) |

> **Schémas lisibles par les machines :** [`post.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/post.frontmatter.schema.json), [`page.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/page.frontmatter.schema.json), [`category.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/category.frontmatter.schema.json)

---

## Images

Placer les images dans le sous-répertoire `files/` à côté du contenu :

```
blog/releases/my-post/
├── en.md
└── files/
    └── featured.webp
```

Référencer dans le frontmatter : `image: /media/my-post/featured.webp`

> **Astuce :** Toutes les images doivent être en WebP. Utiliser ImageMagick : `ddev exec convert input.jpg -quality 82 -strip -define webp:method=6 output.webp`

---

## Commandes de Compilation

| Commande | Objectif |
|--------|-------|
| `ddev build` | Compilation complète de développement : Cache + Assets + Pages + Recherche |
| `ddev code-check` | PHPStan + PHP CS Fixer |
| `ddev code-fix` | Correction automatique du style de code |
| `./notACMS deploy --prod` | Déploiement en production (Docker + compilation statique) |
| `./notACMS rebuild --prod` | Mise à jour du contenu uniquement (rapide, pas de reconstruction Docker) |

---

## Déploiement en Production

### Prérequis

- Docker + Docker Compose
- Domaine avec enregistrement DNS A pointant vers le serveur
- Ports 80/443 disponibles (ou personnalisé via `NGINX_PORT`)

### Premier Déploiement

```bash
./notACMS deploy --prod
```

Ce qui se passe :
1. Initialise `local/` depuis `docs/demo/` (uniquement si absent ou vide — le contenu existant n'est jamais écrasé)
2. Construit l'image Docker PHP
3. Installe les dépendances Composer (`--no-dev` en prod)
4. Compile SCSS + Assets
5. Génère le HTML statique → `public/static/`
6. Construit l'index de recherche Pagefind → `public/pagefind/`
7. Démarre le conteneur nginx

### Mises à Jour de Contenu

Après modification des fichiers Markdown :

```bash
./notACMS rebuild --prod
```

Ignore la reconstruction Docker — régénère uniquement HTML + index de recherche (~10s au lieu de ~2-3min).

### Commandes de Conteneur

| Commande | Objectif |
|--------|-------|
| `./notACMS deploy up` | Démarrer les conteneurs (pas de reconstruction) |
| `./notACMS deploy down` | Arrêter les conteneurs |
| `./notACMS deploy restart` | Redémarrer sans reconstruction |

---

## Variables d'Environnement

Créer `.env.local` à la racine du projet (jamais commité, gitignored) :

| Variable | Requis | Définir via | Objectif |
|----------|--------------|------------|-------|
| `APP_SECRET` | Oui | `php -r "echo bin2hex(random_bytes(32));"` | Secret Symfony |
| `URL` | Oui | `yourdomain.com` | Doit correspondre à `base_url` dans `_site.yaml` |
| `NGINX_PORT` | Non | `8123` | Port hôte (Défaut : 8123) |
| `RUNTIME_PHP_ENABLED` | Non | `true`/`false` | Activer PHP pour le formulaire de contact |
| `MAILER_DSN` | Non* | `smtp://user:pass@host:587` | *Requis si le formulaire de contact est activé |
| `TURNSTILE_SITE_KEY` | Non | Cloudflare Key | CAPTCHA (optionnel) |
| `TURNSTILE_SECRET_KEY` | Non | Cloudflare Secret | CAPTCHA (optionnel) |
| `CERTRESOLVER` | Non | `le` ou `dummy` | Résolveur SSL (Défaut : `le`) |
| `UID` / `GID` | Non | `1000` | Correspondre à l'utilisateur hôte (`id`) |

---

## Dépannage

### Port déjà utilisé

**Erreur :** `bind: address already in use`  
**Correction :**
```bash
echo "NGINX_PORT=8080" >> .env.local
./notACMS deploy --prod
```

### Permission refusée

**Erreur :** 403 sur les fichiers statiques  
**Correction :**
```bash
echo "UID=$(id -u)" >> .env.local
echo "GID=$(id -g)" >> .env.local
./notACMS deploy restart
```

### Formulaire de contact 404

**Correction :** Activer l'exécution PHP :
```bash
echo "RUNTIME_PHP_ENABLED=true" >> .env.local
./notACMS deploy restart
```

### Échec du certificat SSL

**Erreur :** Traefik ne peut pas obtenir le certificat  
**Correction :** Vérifier que les ports 80/443 sont ouverts, que le DNS se résout. Ou utiliser `CERTRESOLVER=dummy` pour les tests.

### Les styles ne se mettent pas à jour

**Correction :** Vider le cache et reconstruire :
```bash
rm -rf var/ public/assets/
./notACMS deploy --prod
```

### Compilation bloquée

**Correction :** S'assurer que Docker dispose de suffisamment de mémoire (4GB+) pour la compilation des assets.
