---
title: "notACMS 1.1.0 — Cœur minimal, thème démo, à vous de choisir"
slug: "articles/release-1-1-0"
description: "1.1.0 scinde notACMS en un cœur minimal et un thème démo optionnel. Choisissez votre point de départ avec --bare ou --demo ; personnalisez tout via local/ sans toucher au cœur."
date: 2026-04-24
category: releases
tags: [release, announcement]
template: blog/post
---

## Deux thèmes, un choix au premier build

Le changement phare de 1.1.0 : notACMS livre désormais deux thèmes d'origine, et vous en choisissez un dès votre tout premier build :

```bash
./notACMS deploy --demo    # par défaut — le design amber-phosphor que vous voyez ici
./notACMS deploy --bare    # un wireframe minimal : polices système, mode clair, ~200 lignes de CSS
```

`ddev build` accepte les mêmes flags. Quel que soit votre choix, toutes les fonctionnalités fonctionnent : contenu multilingue, blog, RSS, sitemap, recherche, formulaire de contact, images avec variantes responsives, temps de lecture, indicateur de progression. La différence ne se joue que sur l'apparence — et, surtout, sur ce que vous héritez avant de commencer à personnaliser.

### Cœur minimal

Le cœur situé dans `templates/`, `assets/`, `translations/` est désormais un wireframe. Il est volontairement minimal pour que surcharger un seul bloc ne traîne pas derrière lui un langage visuel contre lequel il faudrait lutter. Si vous construisez un design sur mesure, partez du bare et vous maîtrisez l'intégralité du rendu dès le premier jour.

### Thème démo

Le démo se trouve sous `docs/demo/` et est copié dans `local/` quand vous choisissez `--demo`. C'est le design amber-phosphor complet que vous lisez en ce moment — mode sombre, overlay de recherche, sidebar docs, bouton de thème, tout y est. Partez de là si vous voulez un design abouti dès aujourd'hui et prévoyez d'ajuster, pas de reconstruire.

## Le système de surcharges `local/`

Les deux thèmes utilisent le même mécanisme : chaque fichier dans `local/` prend le pas sur le chemin correspondant du cœur.

| Couche | Emplacement de surcharge | Resolver |
|---|---|---|
| Templates Twig | `local/templates/*.html.twig` | Kernel Symfony |
| Point d'entrée SCSS | `local/assets/styles/app_local.scss` | Bundle Sass |
| Point d'entrée JS | `local/assets/app.js` | Importmap AssetMapper |
| Traductions | `local/translations/messages.*.yaml` | Translator Symfony |
| Contenu | `local/content/**` | Paramètre `notacms_content` |
| Fragments Nginx | `local/docker/nginx/*.conf` | Entrypoint du conteneur |

Le cœur n'est jamais modifié. `git pull` reste propre. Les personnalisations se trouvent dans le dépôt de votre site, pas dans un fork de celui-ci.

## Mise à niveau depuis 1.0.0

Si vous tourniez en 1.0.0 et voulez que votre site ait exactement la même apparence, le paquet de compatibilité tient en une ligne :

```bash
cp -r docs/customization/old-template/. local/
ddev build
```

Cela dépose le thème pré-1.1.0 complet — templates, SCSS, polices, images, traductions — dans `local/`. Votre site s'affiche exactement comme avant. Vous pouvez ensuite retirer des fichiers de `local/` au fur et à mesure que vous adoptez de nouveaux éléments de design.

Les vraies ruptures auxquelles vous ferez face si vous aviez personnalisé l'ancien cœur :

- **Point d'entrée SCSS renommé** : `local/assets/styles/local.scss` → `local/assets/styles/app_local.scss`. Renommez le fichier et mettez à jour l'import dans `local/assets/app.js`.
- **Variables SCSS de couleur du cœur remplacées par des CSS custom properties** : `$color-body` → `var(--text)`, `$color-link` → `var(--accent)`, etc. Correspondance complète dans [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md).
- **Clés de traduction retirées** du cœur (toujours présentes dans le thème démo) : `header.tagline`, `nav.projects`, `nav.search`, `blog.published_on`, `blog.comments_disabled`. Si vos templates appellent `'...'|trans` dessus, mettez-les à jour.
- **Répertoire `docs/examples/` retiré** : les éléments utiles ont été déplacés vers `docs/customization/old-template/` ; les exemples de surcharge ont été supplantés par le modèle bare/démo.

Voir [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md) pour la liste complète avec extraits avant/après.

## Également nouveau en 1.1.0

- **Temps de lecture** et **indicateur de progression** sur les posts, docs et releases.
- **Sélecteur de langue** comme extension Twig (`lang_switch_urls`) avec des chaînes de fallback correctes pour les archives, listes paginées et pages d'accueil.
- **Les extraits de posts retirent les ancres de titres** — plus de `#` parasites dans les cartes de liste.
- **Ossature de batterie de tests** sous `tests/Unit/`, `tests/Integration/`, `tests/Fixtures/` avec une couverture initiale et une commande `test` exécutable depuis l'hôte.
- **Scripts d'agent IA** sous `.claude/skills/` pour travailler sur le dépôt : ajout de locales, vérifications d'alignement des docs, balayages de site, traductions et génération de guides de mise à niveau.

## Liste complète des modifications

Chaque changement avec sa catégorie : [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#110---2026-04-24).
