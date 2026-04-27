---
title: "notACMS 1.1.1 — Labels de navigation depuis le frontmatter et deploy sans écrasement"
slug: "articles/release-1-1-1"
description: "1.1.1 introduit les labels de navigation depuis le frontmatter (au lieu des clés de traduction) et corrige le bug deploy --prod qui écrasait le contenu local/."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## Labels de navigation depuis le frontmatter

Les labels de navigation des pages de contenu proviennent désormais du frontmatter — pas des clés de traduction. Définissez `menu.label` dans le frontmatter d'une page, et ce texte devient le label du menu ; omettez-le, et le `title` de la page sera utilisé comme fallback.

```yaml
---
title: "Guide d'architecture"
slug: "architecture-guide"
menu:
  label: "Architecture"
  weight: 30
---
```

La nouvelle fonction Twig `content_item()` résout n'importe quelle page de contenu par sa clé de répertoire :

```twig
{{ content_item('architecture-guide', 'fr').menuLabel() }}
{# → "Architecture" (ou le titre de la page si menu.label est absent) #}
```

**Pourquoi c'est important :** Auparavant, chaque fichier `messages.*.yaml` devait dupliquer les traductions pour `nav.home`, `nav.blog`, `nav.about`, `nav.contact`, `nav.privacy_policy` (core) et `site.releases`, `site.about`, `site.manual`, `site.architecture`, `site.customization`, `site.locales`, `site.design_reference`, `site.contact` (démo). Ajouter une page impliquait de mettre à jour N fichiers de traduction. Maintenant, il suffit d'un champ dans le frontmatter. Les templates personnalisés qui référencent ces clés supprimées doivent migrer vers `content_item('key', locale).menuLabel()`.

## Deploy préserve le contenu existant

### Le bug

Exécuter `./notACMS deploy --prod` **sauvegardait et remplaçait** tout le répertoire `local/` par `docs/demo/` à chaque déploiement — même lorsqu'il contenait déjà votre contenu, templates et personnalisations. La logique de seeding ne distinguait pas « l'utilisateur a explicitement demandé un re-seed du thème » de « l'utilisateur veut juste redéployer avec le contenu existant. »

### Le correctif

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
