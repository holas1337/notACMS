---
title: "notACMS 1.1.1 — Deploy préserve le contenu existant"
slug: "articles/release-1-1-1"
description: "1.1.1 corrige un bug où ./notACMS deploy --prod écrasait le contenu existant de local/. Deploy ne seede désormais que si local/ est absent ou vide — comme ddev build."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## Le bug

Exécuter `./notACMS deploy --prod` **sauvegardait et remplaçait** tout le répertoire `local/` par `docs/demo/` à chaque déploiement — même lorsqu'il contenait déjà votre contenu, templates et personnalisations. La logique de seeding ne distinguait pas « l'utilisateur a explicitement demandé un re-seed du thème » de « l'utilisateur veut juste redéployer avec le contenu existant. »

## Le correctif

Deploy fonctionne désormais exactement comme `ddev build` :

- **Pas de flag `--bare` / `--demo`** → seede `local/` uniquement s'il est absent ou vide ; ignore s'il contient du contenu.
- **`--bare` ou `--demo` passé explicitement** → sauvegarde le `local/` existant et seede le thème choisi.

```bash
./notACMS deploy --prod          # préserve le local/ existant
./notACMS deploy --prod --demo   # force le re-seed depuis docs/demo/
./notACMS deploy --prod --bare   # force le re-seed depuis docs/bare/
```

`--prod` ne contrôle désormais que les flags Composer (`--no-dev`) et l'environnement d'exécution (`APP_ENV=prod`). Il ne touche jamais à `local/`.

## Également dans 1.1.1

- **Mise à jour des dépendances :** paquets `symfony/polyfill-*` mis à jour de v1.36.0 à v1.37.0.
- **Contenu démo polonais, allemand et français** révisé et amélioré sur toutes les pages et articles du blog.
- **Guides de style de traduction** ajoutés au système de compétences de l'agent IA pour le polonais, l'allemand et le français afin d'assurer une qualité cohérente pour les traductions futures.

## Liste complète des modifications

Tous les changements avec leur catégorie : [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#111---2026-04-26).
