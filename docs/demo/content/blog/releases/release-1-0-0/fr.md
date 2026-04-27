---
title: "notACMS v1.0.0 — Première version stable"
slug: "articles/release-1-0-0"
description: "La première version stable de notACMS — un générateur de sites statiques PHP/Symfony avec Markdown, i18n et Twig."
date: 2026-04-09
category: releases
tags: [release, announcement]
template: blog/post
---

## Ce que contient la v1.0.0

Après plusieurs mois d'utilisation interne sur des projets personnels, je publie la première version stable. L'ensemble de fonctionnalités est suffisamment solide pour construire de vrais sites.

### Pipeline de contenu

Le pipeline de contenu est le cœur de notACMS. Il lit le répertoire `local/content/`, analyse le frontmatter et génère un site statique complet en une seule commande.

- Contenu Markdown avec frontmatter YAML
- Rendu CommonMark avec permaliens sur les titres
- Génération d'extraits à partir du contenu textuel
- Calcul du temps de lecture

### Routage multilingue

Les locales sont définies dans `_site.yaml`. Chaque locale dispose de son propre espace d'URL, avec des surcharges de chemin optionnelles dans `_routes.yaml`. Les balises hreflang sont générées automatiquement.

```yaml
site:
  locales:
    en:
      label: "English"
      date_format: "M d, Y"
    pl:
      label: "Polski"
      date_format: "d.m.Y"
```

### Recherche Pagefind

La recherche en texte intégral est intégrée à la sortie statique via [Pagefind](https://pagefind.app/). La commande de compilation génère l'index de recherche automatiquement. Pas d'API externe, pas de recherche côté serveur — juste un index statique qui fonctionne hors ligne.

### Traitement des images

Les images colocées avec le contenu sont traitées lors de la compilation. notACMS génère des variantes WebP à plusieurs largeurs, met à jour automatiquement les attributs `src` avec un `srcset` responsive et gère la correspondance de chemins entre les répertoires de contenu et de sortie.

### Développement local avec DDEV

L'environnement de développement est entièrement conteneurisé avec DDEV. `ddev start` vous donne PHP 8.5, Nginx et tous les outils de compilation. `ddev build` produit la sortie statique. `ddev code-check` lance PHPStan et PHP CS Fixer.

## Changements cassants

Il s'agit de la première version stable. Si vous utilisiez une version antérieure à la 1.0, vérifiez le schéma de `_site.yaml` — la clé `social` est passée d'une liste à un tableau associatif.

## Mise à niveau

```bash
git pull
ddev composer install
ddev build
```

## Et ensuite

La v1.1.0 sera centrée sur le système de design et la documentation. Ce site — construit avec notACMS — deviendra la documentation officielle et la référence de design.
