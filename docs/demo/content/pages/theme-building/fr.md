---
title: "Construction de Thème"
description: "Construire un thème complet pour notACMS — couches de templates, API Twig, contrats de contexte et règles de portabilité."
template: page/doc
slug: construction-theme
menu:
  weight: 45
  label: "Construction de Thème"
---

## Hiérarchie des Couches de Templates

notACMS résout les templates depuis trois couches, appliquées par ordre de priorité :

1. **`local/templates/`** — surcharges spécifiques au site (priorité la plus haute)
2. **Templates du thème** — enregistrés via `twig.paths` dans `local/config/packages/twig.yaml`
3. **Templates bare core** dans `templates/`

Tout fichier dans `local/templates/` surcharge l'équivalent dans le thème enregistré ou le core. Le thème bare fournit un ensemble minimal et complet de templates qui rend tous les types de contenu sans dépendances externes. Le thème demo (ce site) ajoute du style et des composants par-dessus.

## Contrats Template ↔ Contexte

Chaque template reçoit les [globales Twig](#globales-twig) plus un contexte spécifique à la page. `locale` est toujours présent.

### Templates de Page

| Route | Template | Contexte clé |
|---|---|---|
| `home_{locale}` | `page/home.html.twig` | `content` (`?ContentItem`), `recentPosts` (`ContentItem[]`), `card_layouts` |
| `static_page_{locale}` | frontmatter `template:` | `content` (`ContentItem`) |
| `contact_{locale}` | `page/contact.html.twig` | `content`, `form`, `contact_form_available` (`bool`) |
| `projects_{locale}` | `page/projects.html.twig` | `content`, `featured_projects`, `total_projects` |
| `search_{locale}` | `search/index.html.twig` | `locale` uniquement |
| pages d'erreur | `page/error.html.twig` | `status_code`, `status_text`, `locale`, `content` (null) |

### Templates de Blog

| Route | Template | Contexte clé |
|---|---|---|
| liste blog + paginée | `blog/list.html.twig` | `posts`, `current_page`, `total_pages`, `total_posts`, `filter_type`, `filter_value`, `index_content`, `card_layouts` |
| article unique | `blog/post.html.twig` | `content`, `related_posts`, `prev_post`, `next_post`, `series_posts`, `series_current_part`, `card_layouts` |
| article planifié | `page/coming-soon.html.twig` | `post`, `card_layouts` |

Valeurs de `filter_type` : `'category'`, `'tag'`, `'archive'`. `card_layouts` fait tourner `['layout-top', 'layout-right', 'layout-text', 'layout-left']`.

### Templates de Feed & E-mail

| Template | Contexte |
|---|---|
| `feed/rss.xml.twig` | `posts` (`ContentItem[]`), `locale` |
| `feed/sitemap.xml.twig` | `items_by_locale` (`array<locale, ContentItem[]>`) |
| `feed/llms.txt.twig` | `posts_by_locale`, `pages` |
| `feed/robots.txt.twig` | globales uniquement |
| `email/contact.html.twig` | `contact` (tableau : `name`, `email`, `website`, `subject`, `message`), `locale` |

## Globales Twig

Disponibles dans chaque template :

| Globale | Type | Source |
|---|---|---|
| `site_name`, `site_base_url`, `site_description` | `string` | `_site.yaml` |
| `site_social` | `array` | `_site.yaml` `social:` (forme définie par le site) |
| `site_author` | `array` | `_site.yaml` `author:` — toujours protéger avec `\|default()` |
| `site_locales` | `array<locale, array>` | par locale : `label`, `og_locale`, `date_format`, … |
| `site_locales_list` | `string[]` | codes locale, le premier = défaut |
| `site_default_locale` | `string` | première clé de `locales:` |
| `image_variant_widths` | `int[]` | `_site.yaml` (défaut `[640, 960]`) |
| `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` | `int` | `_site.yaml` |
| `translation_map` | `array<directoryKey, array<locale, url>>` | traductions publiées uniquement ; les clés sont des chemins de contenu relatifs complets |
| `cf_analytics_token`, `turnstile_site_key`, `notacms_project_url` | `string` | variables d'environnement `twig.yaml` |

## Fonctions & Filtres Twig

| Fonction / filtre | Signature | Retourne |
|---|---|---|
| `content_item(key, locale)` | `(string, string): ?ContentItem` | Item par clé de répertoire — nullable, toujours protéger |
| `content_url(key, locale)` | `(string, string): string` | URL de l'item ; `'/'` si non trouvé |
| `breadcrumbs(content, locale, options)` | `(?ContentItem, string, array): Breadcrumb[]` | `.label` et `.url` (null = page courante) |
| `lang_switch_urls(other_locales)` | `(string[]): array<locale, url>` | URLs du sélecteur de langue |
| `og_image_url(content, site_base_url)` | `(?ContentItem, string): string` | URL de l'image vedette ou image OG par défaut |
| `post_badge(post, new_post_days)` | `(ContentItem, int): ?string` | `'new'`, `'updated'` ou null |
| `sidebar_data(locale)` | `(string): SidebarData` | `.recentPosts`, `.categories`, `.tags`, `.archiveMonths` |
| `structured_data()` | `(): StructuredDataBuilderInterface` | Builder Schema.org — `webSite()`, `blogPosting()`, `breadcrumbList()`, etc. |
| `json_ld(data)` | `(array): string` | Bloc `<script type="application/ld+json">` |
| `srcset_media` (filtre) | `(string): string` | Injecte `srcset`/`sizes` dans les balises `<img>` — pipeline corps standard : `content.htmlContent\|srcset_media\|raw` |

## API ContentItem

Méthodes disponibles sur tout `ContentItem` dans les templates :

```php
$item->title()         // string
$item->description()   // string
$item->slug()          // string — dernier segment du slug frontmatter
$item->url()           // string — chemin complet incl. préfixe locale
$item->date()          // ?DateTimeImmutable — protéger avant |date()
$item->updatedDate()   // ?DateTimeImmutable
$item->tags()          // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()      // string (valeur par défaut : title)
$item->excerpt()       // string
$item->readingTime()   // int
$item->wordCount()     // int
$item->menuLabel()     // string
$item->menuWeight()    // int
$item->series()        // ?string
$item->seriesOrder()   // ?int
$item->directoryKey()  // ?string — chemin de contenu relatif complet
$item->isDraft()       // bool
$item->isScheduled()   // bool
$item->isPinned()      // bool
$item->isFeatured()    // bool
$item->hasToc()        // bool
$item->htmlContent     // string (propriété publique) — HTML rendu
$item->locale          // string (propriété publique)
```

## Clés de Traduction Requises

Chaque catalogue de thème doit définir ces clés dans chaque locale :

| Clé | Utilisée par |
|---|---|
| `blog.title` | Fallback de `blog_filter_title()` quand il n'y a pas de page d'index de blog |
| `contact.form.error`, `contact.form.success` | Réponses JSON de l'API de contact |
| `contact.form.name`, `.email`, `.website`, `.subject`, `.message` | Libellés des champs du formulaire |
| `contact.form.unavailable` | Affiché quand le formulaire de contact n'est pas configuré |

Les clés utilisées par les templates que votre thème ne surcharge pas doivent également exister dans vos catalogues — le traducteur ne se replie sur les `translations/` core que pour les clés que vous ne définissez pas vous-même.

## Règles de Portabilité du Thème

1. **Itérer `site_locales_list`** — ne jamais coder en dur les codes locale ni supposer exactement deux locales.
2. **Protéger les lookups nullables** — `content_item()` retourne null ; les clés `site_author.*` sont définies par le site (`site_author.name|default(site_name)`) ; `content.date()` peut être null.
3. **Ne pas comparer `directoryKey()` à des littéraux** sauf si vous contrôlez la disposition du contenu — il retourne le chemin relatif complet.
4. **Chaque clé `|trans` dans vos templates doit exister dans chaque catalogue locale que vous livrez** — les clés manquantes sont rendues comme des chaînes de clés brutes.
5. **Ne pas faire de correspondance de pattern d'URL pour la détection de section** quand les métadonnées de contenu peuvent répondre à la question.
6. **Pas de `|raw` sur la sortie du catalogue de traduction** — utiliser `|sanitize_html('app.trans_inline')` quand un thème a genuinement besoin de balisage inline dans du texte traduit.

## Assets

Les assets du thème sous `local/assets/` sont référencés comme `asset('local/<file>')`. Les assets core utilisent `asset('<file>')`. Les images de contenu sont servies à `/media/{basename}/{filename}`. Le pipeline d'images responsives est uniquement WebP : les URLs des variantes sont `<base>-<width>w.webp` pour chaque entrée `image_variant_widths`.

Le contrat API complet — incluant tous les champs de contexte, les formes des value objects et les notes sur les cas limites — est maintenu dans `docs/THEME_BUILDING.md`. Les changements incompatibles sont documentés dans `UPGRADE-X.Y.md`.
