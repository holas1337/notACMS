---
title: "Localisation"
description: "Ajouter des langues, traduire le contenu et configurer les patterns d'URL pour chaque locale."
template: page/doc
slug: locales
menu:
  weight: 50
  label: "Localisation"
---

## Ajouter une Nouvelle Locale

Ouvrez `local/content/_site.yaml` et ajoutez une entrée à la section `locales` :

```yaml
site:
  locales:
    en:
      label: "English"
      og_locale: en_US
      date_format: "M d, Y"
      tagline: "static. simple. yours."
      # tagline_commands: ["git push", "ddev build"]  # optionnel : affiché dans le hero sous le tagline
      # font_preload: true                                # optionnel : précharger Inter + JetBrains Mono
    de:
      label: "Deutsch"
      og_locale: de_DE
      date_format: "d.m.Y"
      tagline: "statisch. einfach. deins."
    pl:                                 # ce site de démo contient aussi le Polonais
      label: "Polski"
      og_locale: pl_PL
      date_format: "d.m.Y"
      tagline: "statyczny. prosty. twój."
    fr:                                 # et le Français
      label: "Français"
      og_locale: fr_FR
      date_format: "d/m/Y"
      tagline: "statique. simple. vôtre."
```

C'est tout. notACMS va :
- Ajouter `fr` au sélecteur de langue
- Générer les tags `hreflang` pour toutes les pages avec une traduction FR
- Préfixer les URLs FR avec `/fr/` (ou utiliser votre chemin personnalisé)

C'est la même chose pour `de`, `pl` ou toute autre locale que vous ajoutez.

## Co-localisation du Contenu

Les traductions se trouvent à côté du contenu source. Chaque locale est un fichier Markdown séparé dans le même répertoire :

```
pages/about/
├── en.md    ← Anglais
├── de.md    ← Allemand
├── pl.md    ← Polonais
└── fr.md    ← Français
```

Le champ `slug` dans le frontmatter définit le chemin URL pour chaque locale. Si vide (ou omis pour les pages d'index), l'URL de la page d'accueil est utilisée (`/` pour la locale par défaut, `/{locale}/` pour les autres). Les URLs proviennent toujours du champ frontmatter `slug` — pas des chemins de répertoire.

## Surcharges d'URLs

Par défaut, le chemin EN reçoit le préfixe de locale : `/de/about/`, `/pl/about/`, `/fr/about/`. Pour un chemin entièrement personnalisé, ajoutez une entrée dans `_routes.yaml` :

```yaml
# local/content/_routes.yaml
routes:
  blog_list:
    de: /beitraege/
    pl: /wpisy/
    fr: /articles/
  blog_list_paginated:
    de: /beitraege/seite/{page}/
    pl: /wpisy/strona/{page}/
    fr: /articles/page/{page}/
```

Les surcharges d'URL au niveau de la page sont définies dans le frontmatter :

```yaml
---
title: "À propos"
slug: a-propos
---
```

Cela donne `/fr/a-propos/` au lieu de `/fr/about/`.

## Traductions de Tags

Les tags peuvent être traduits dans `_tags.yaml` :

```yaml
# local/content/_tags.yaml
release:
  de: veröffentlichung
  pl: wydanie
  fr: version
```

Le tag canonique (clé EN) est utilisé en interne. La valeur traduite apparaît dans l'interface utilisateur pour chaque locale. Les tags identiques dans toutes les locales n'ont pas besoin d'entrée — liste uniquement ceux qui diffèrent.

## Comportement du Sélecteur de Langue

Le sélecteur de langue dans la navigation résout automatiquement l'URL correcte pour chaque autre locale via la fonction Twig `lang_switch_urls()`. La chaîne de fallback, dans l'ordre :

1. **Translation map** — recherche une version traduite de la page actuelle via le global `translation_map`
2. **Override contrôleur** — utilise un `lang_switch_url` explicite si défini (ex. pages de tags où le tag peut ne pas exister dans la locale cible)
3. **Archive** — si sur une page d'archive, pointe vers la même archive dans la locale cible
4. **Liste de blog paginée** — si sur la page 2+, pointe vers la même page dans la locale cible
5. **Liste de blog** — si sur une page de liste de blog, pointe vers la liste de blog dans la locale cible
6. **Page d'accueil** — fallback final quand aucun contexte blog ou traduction n'est disponible

Le sélecteur n'apparaît que si 2 locales ou plus sont configurées et qu'au moins une autre locale est définie.

## Stratégie de Fallback

Lorsqu'une page existe en EN mais pas en FR, notACMS va :
- Ne pas générer d'URL FR pour cette page
- Ne pas l'inclure dans la sitemap FR
- Afficher un lien vers la page d'accueil FR dans le sélecteur de langue (pas la version EN de la page)

Cela signifie que vous pouvez traduire progressivement — EN est toujours complet, les autres locales grandissent avec le temps. C'est la même chose pour DE, PL ou toute autre locale.

## hreflang et SEO

notACMS génère automatiquement les balises `<link rel="alternate" hreflang="...">` dans le `<head>` pour toutes les pages avec traductions. Le champ `og_locale` dans la configuration du site contrôle le format de locale Open Graph (`en_US`, `de_DE`, etc.).

```html
<link rel="alternate" hreflang="en" href="https://example.com/about/">
<link rel="alternate" hreflang="de" href="https://example.com/de/about/">
<link rel="alternate" hreflang="fr" href="https://example.com/fr/about/">
<link rel="alternate" hreflang="x-default" href="https://example.com/about/">
```

> **Astuce :** Fournissez toujours une traduction `x-default` pointant vers la langue principale. notACMS gère cela automatiquement pour la locale par défaut.
