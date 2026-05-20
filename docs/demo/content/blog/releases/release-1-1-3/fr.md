---
title: "notACMS 1.1.3 — fichiers JSON Schema et mise à jour de sécurité Symfony 7.4.12"
slug: "articles/release-1-1-3"
description: "1.1.3 livre des fichiers JSON Schema pour toute la configuration et le frontmatter, corrige les fragments Pagefind obsolètes, et met à jour Symfony 7.4.12 + Twig 3.26.0 en fermant 24 CVEs."
date: 2026-05-20
category: releases
tags: [release, security, symfony]
template: blog/post
---

## Fichiers JSON Schema pour la configuration et le frontmatter

Six fichiers JSON Schema draft-07 se trouvent maintenant dans `config/schema/` :

| Fichier de schéma | Décrit |
|---|---|
| `site.schema.json` | `_site.yaml` — paramètres du site, locales, liens sociaux, formulaire de contact |
| `routes.schema.json` | `_routes.yaml` — surcharges des chemins URL par locale |
| `tags.schema.json` | `_tags.yaml` — table de traduction des tags |
| `post.frontmatter.schema.json` | Frontmatter des articles de blog |
| `page.frontmatter.schema.json` | Frontmatter des pages statiques |
| `category.frontmatter.schema.json` | Frontmatter des fichiers d'index de catégorie |

Tous les fichiers YAML de template (`_site.yaml`, `_routes.yaml`, `_tags.yaml`) dans `docs/bare/`, `docs/demo/` et `docs/customization/old-template/` portent désormais un commentaire `# yaml-language-server: $schema=` pointant vers l'URL brute sur la branche main. VS Code (et tout éditeur avec YAML Language Server) les détecte automatiquement — validation et autocomplétion fonctionnent sans aucune configuration de projet supplémentaire.

Les schémas sont optimisés pour l'authoring assisté par IA : les descriptions incluent les valeurs par défaut, les contraintes et les explications de comportement, pour qu'un agent IA dans un autre projet puisse récupérer un schéma et savoir exactement ce que fait chaque champ.

Récupérer n'importe quel schéma directement depuis la branche main :

```
https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/<name>.schema.json
```

## Mise à jour de sécurité : Symfony 7.4.12 et Twig 3.26.0

**Mettez à jour immédiatement.** Lancez `composer update` dans votre projet, puis rebuilder.

### Symfony 7.4.12 (21 CVEs)

Les plus impactantes pour un déploiement CMS :

- **CVE-2026-45073** — injection SQL dans `Cache` via un `$prefix` non validé dans `PdoAdapter::doClear()`.
- **CVE-2026-45071** — XXE / divulgation de fichiers locaux dans `DomCrawler::addXmlContent()` avec `validateOnParse` activé.
- **CVE-2026-45075** — les requêtes HEAD contournent le filtre `methods` des attributs `#[IsGranted]`, `#[IsCsrfTokenValid]` et `#[IsSignatureValid]`.
- **CVE-2026-45072** — XSS dans `CodeExtension::fileExcerpt()` de `TwigBridge`.
- **CVE-2026-45068** — injection d'en-tête dans `SendmailTransport` ; les adresses commençant par un tiret sont désormais rejetées.
- **CVE-2026-45067** — les adresses e-mail contenant des sauts de ligne étaient acceptées par `Mime\Address` — elles sont maintenant rejetées.
- **CVE-2026-45305 / 45304 / 45133** — backtracking catastrophique et récursion illimitée dans le parseur YAML.
- **CVE-2026-45066 / 45064 / 45753** — trois contournements de `HtmlSanitizer` : caractères BiDi override, différences de parseurs d'URL et attributs `action`/`formaction`/`poster`/`cite` non assainis.

Liste complète : [symfony.com/blog/symfony-7-4-12-released](https://symfony.com/blog/symfony-7-4-12-released).

### Twig 3.26.0 (4 CVEs)

Les quatre sont des contournements de sandbox. Si votre thème utilise le sandbox de Twig pour des templates fournis par les utilisateurs, ils sont critiques :

- **CVE-2026-46635** — contournement de la liste des propriétés autorisées via le filtre `column` (`array_column` sur des objets).
- **CVE-2026-46638** — `{% sandbox %}{% include %}` ignore `checkSecurity()` sur les templates mis en cache ; correction incomplète de CVE-2024-45411.
- **CVE-2026-24425** — contournement du sandbox via une source policy.
- **CVE-2026-47732** — multiples contournements de la politique `__toString()` via des points de coercition de chaîne non protégés.

## Aussi dans 1.1.3

- **Fragments Pagefind obsolètes corrigés** — `scripts/rebuild-content.sh` supprime maintenant `public/pagefind/` avant de réindexer. Auparavant, supprimer ou renommer du contenu laissait des fichiers de fragments orphelins que Pagefind servait avec les résultats à jour.
- **Améliorations DESIGN.md** — les valeurs hex codées en dur remplacées par des références aux tokens dans les fichiers `DESIGN.md` bare et demo ; alias de couleur `primary` ajouté ; token de composant `card-hover` ajouté au thème demo.

## Comment mettre à jour

```bash
composer update
ddev build   # ou la commande de build équivalente dans votre environnement
```

Aucune modification de configuration ni étape de migration n'est nécessaire.

## Changelog complet

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#113---2026-05-20)
