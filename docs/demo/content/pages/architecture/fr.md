---
title: "Architecture"
description: "Comment notACMS fonctionne en interne — pipeline de contenu, système de routage et processus de build."
template: page/doc
slug: architecture
menu:
  weight: 30
  label: "Architecture"
---

## Pipeline de Contenu

Chaque build suit une pipeline en quatre étapes :

```
1. sass:build         → Compiler SCSS vers CSS
2. asset-map:compile  → Hacher et copier les assets
3. app:build          → Rendre toutes les pages HTML depuis le contenu + les templates
4. pagefind            → Créer l'index de recherche
```

L'étape 3 est contrôlée par `BuildStaticSiteCommand` dans `src/Command/`, qui scanne le contenu, rend les templates, génère les images responsives et écrit le HTML statique dans `public/static/`. L'étape 4 exécute `npx pagefind` comme processus séparé.

Les URLs sont dérivées du champ frontmatter `slug`, pas des chemins de répertoire. Un article dans `blog/releases/release-1-0-0/en.md` avec `slug: "blog/release-1-0-0"` génère `/blog/release-1-0-0/`.

## Système de Routage

Les routes sont générées à partir de l'arborescence de contenu. Chaque `ContentItem` a une URL définie par son champ frontmatter `slug`.

Le `LocalizedRouteLoader` lit `local/content/_routes.yaml` et enregistre les routes Symfony pour chaque locale. La locale par défaut (EN) n'a pas de préfixe. Les autres locales obtiennent un préfixe `/{locale}/`, sauf si surchargé.

**Exemple de génération d'URL :**

| Fichier de Contenu | Slug Frontmatter | Locale | URL Générée |
|---|---|---|---|
| `pages/about/en.md` | `about` | EN | `/about/` |
| `pages/about/de.md` | `about` | DE | `/de/about/` |
| `blog/releases/release-1-0-0/en.md` | `blog/release-1-0-0` | EN | `/blog/release-1-0-0/` |
| `blog/releases/release-1-0-0/pl.md` | `blog/release-1-0-0` | PL | `/wpisy/release-1-0-0/` |

## Services Core

### `ContentService`

La façade principale pour charger et interroger le contenu. Délègue la construction de l'arborescence à `ContentTreeBuilder` et implémente `ContentCacheInterface`, permettant ainsi à `invalidateCache($locale)` d'invalider les arbres mis en cache lorsque le contenu change.

```php
$posts = $contentService->getPosts($locale, 1, 5);
$recent = $contentService->getRecentPosts($locale, 5);
$item  = $contentService->findByUrl('/about/', $locale);
```

### `ContentItem`

Représente un élément de contenu unique. Expose des méthodes pour les champs frontmatter :

```php
$item->title()          // string
$item->slug()           // string — dernier segment du slug frontmatter (ex. "my-post" de "blog/my-post")
$item->url()            // string (chemin URL complet, ex. /blog/my-post/)
$item->date()           // ?DateTimeImmutable
$item->updatedDate()    // ?DateTimeImmutable
$item->description()    // string
$item->tags()           // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()       // string (valeur par défaut : title)
$item->template()       // string
$item->isDraft()        // bool
$item->isScheduled()    // bool
$item->isPinned()       // bool
$item->isDynamic()      // bool
$item->isFeatured()     // bool
$item->hasToc()         // bool
$item->series()         // ?string
$item->seriesOrder()    // ?int
$item->directoryKey()   // ?string
$item->relatedSlugs()   // string[]
$item->menuWeight()     // int
$item->isIndex()       // bool
$item->readingTime()    // int
$item->wordCount()      // int
$item->excerpt()        // string
$item->htmlContent      // chaîne HTML rendue (propriété publique)
```

### `SiteConfigService`

Lit `local/content/_site.yaml` et expose les paramètres du site en tant que service. Est injecté dans les contrôleurs et les globales Twig via `SiteConfigExtension`.

### `ContentTree`

Représentation interne de la hiérarchie de contenu pour une seule locale. Utilisée pour la navigation, le calcul précédent/suivant et la reconnaissance des séries. `ContentService` l'enveloppe comme une façade, donc les mêmes noms de méthodes de recherche (`findByUrl`, `findPostBySlug`, `findScheduledPostBySlug`) existent aux deux niveaux : la variante service accepte `$locale` et délègue à l'arbre de la locale correspondante.

Méthodes importantes :
- `getAdjacentPosts()` — retourne le Value Object `AdjacentPosts` avec `->prev` et `->next`
- `getSeriesPosts()` — retourne les articles de la même série, triés par `series_order`

### Autres Services

| Service | Objectif |
|---------|-------|
| `ImageResizer` | Wrapper ImageMagick : `resize()` génère des variantes responsives ; `optimize()` recompresse sur place |
| `ResponsiveImageService` | Calcule les largeurs de variantes responsives et crée les valeurs d'attribut `srcset` |
| `SrcsetExtension` | Filtre Twig `srcset_media` — post-traite le HTML pour insérer les attributs `srcset`/`sizes` dans les balises `<img>` |
| `MediaFileResolver` | Résout les chemins de fichiers média à travers les locales |
| `RelatedPostsService` | Trouve les articles connexes via les tags communs et le frontmatter `related` manuel |
| `SessionToggleService` | Générateur de bascule booléenne basée sur session ; dépendance commune des deux services de prévisualisation ci-dessous |
| `DraftPreviewService` | Gère la prévisualisation du contenu brouillon avec des bascules basées sur session |
| `ScheduledPreviewService` | Gère la prévisualisation des articles planifiés futurs |
| `TranslationMapBuilder` | Crée les cartes de traduction d'URL à travers les locales pour le sélecteur de langue |
| `ContactFormConfig` | Fournit la configuration du formulaire de contact (destinataire, Turnstile, etc.) |
| `MarkdownParser` | Convertit Markdown en HTML via CommonMark |
| `SidebarDataProvider` | Assemble les données de la barre latérale (catégories, tags, derniers articles) |
| `TagTranslationService` | Traduit les slugs de tags entre les locales |

### Extensions Twig

Cinq extensions Twig fournissent des données pour les templates :

| Extension | Type | Fournit |
|---|---|---|
| `SiteConfigExtension` | Globals | `site_name`, `site_base_url`, `site_description`, `site_social`, `site_author`, `site_locales`, `site_locales_list`, `site_default_locale`, `image_variant_widths`, `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` |
| `TranslationMapTwigExtension` | Global | `translation_map` — mapping `{directoryKey: {locale: url}}` pour le sélecteur de langue et les tags hreflang |
| `ContentTwigExtension` | Function | `content_url(directoryKey, locale)` — résout l'URL pour un élément de contenu par clé de répertoire |
| `LangSwitcherExtension` | Function | `lang_switch_urls(otherLocales)` — résout les URLs du sélecteur de langue par locale : translation map, override contrôleur, puis fallbacks (archive → paginé → liste blog → accueil) |
| `SrcsetExtension` | Filter | `srcset_media` — insère les attributs `srcset`/`sizes` dans les balises `<img>` pour les images responsives |

De plus, `cf_analytics_token` est enregistré comme globale Twig dans `config/packages/twig.yaml`, lié à la variable d'environnement `CF_ANALYTICS_TOKEN`.

## Flux de Requête (Mode Live)

```
Browser → nginx → PHP-FPM → Symfony Kernel → LocaleListener → Router
          → Controller → ContentService → Twig → HTML-Response
         → Twig-Renderer (appliquer template)
         → HTML-Response
```

Les contrôleurs sont minces. Toute la logique métier réside dans les services.

## Stratégie de Test

```bash
ddev test           # Exécuter toute la suite de tests
ddev code-check     # PHPStan Level 6 + PHP CS Fixer
```

- **Centaines de méthodes de test** dans les suites d'intégration et unitaires.
- **PHPStan :** Analyse statique au niveau 6. Tous les services sont entièrement typés.
- **PHP CS Fixer :** Applique PSR-12 + Standards de Codage Symfony.
- **Tests d'intégration :** Couvrent le routage, le chargement de contenu, les contrôleurs, la sortie de build, le traitement d'images, les services de prévisualisation et les extensions Twig.
- **Tests unitaires :** Couvrent `ContentItem`, `ContentTree`, Value Objects, `RelatedPostsService`, `TagTranslationService` et `ResponsiveImageService`.

Voir `docs/TESTING.md` pour les détails.

## Ajout de Fonctionnalités

notACMS suit le pattern du conteneur de services Symfony. Pour ajouter un nouveau type de contenu ou une étape de build :

1. Créer une interface dans `src/Service/` (pour les contributions core) ou `local/src/` (pour les extensions spécifiques au site)
2. Créer la classe d'implémentation dans le même répertoire
3. Symfony auto-discover et autowire automatiquement — aucun enregistrement manuel `services.yaml` requis
4. Injecter dans le contrôleur ou commande pertinent
5. Surcharger les templates dans `local/templates/` si nécessaire

> **Astuce :** Tous les services sont d'abord des interfaces. Cela facilite le remplacement des implémentations — par exemple, le chargeur de système de fichiers local contre une source S3.

## Runtime PHP (Optionnel)

Par défaut, le site construit est purement statique — Nginx sert `public/static/` directement et PHP n'est pas impliqué.

La configuration Docker de production dans `local/docker/` contient un conteneur PHP optionnel. Définissez `RUNTIME_PHP_ENABLED=true` dans votre environnement de production pour le démarrer. Lorsqu'activé, Nginx redirige `/api/` (et `/{locale}/api/`) vers le conteneur PHP — tout le reste continue d'être servi comme fichier statique.

Le cas d'utilisation standard est le **formulaire de contact** (`ContactController` via SMTP), accessible sous `/api/contact`.

> **Note :** Le développement local (`ddev start`) exécute l'application Symfony complète dynamiquement. C'est une fonctionnalité de confort dev-only — elle ne fait pas partie du runtime PHP de production.

---

## Architecture de Déploiement

### Serving Static-First

Nginx sert les fichiers pré-construits directement depuis `public/static/`. PHP n'est impliqué que pour :
- `/api/contact` — lorsque `RUNTIME_PHP_ENABLED=true`
- Mode développement (`ddev start`)

Flux de trafic de production :
```
Browser → Nginx → public/static/ (HTML, CSS, JS)
                → Proxy vers le conteneur PHP (uniquement pour /api/*)
```

### Build vs Runtime

| Phase | Commande | Sortie | Durée |
|-------|----------|--------|-------|
| 1. Compiler | `sass:build` | `public/assets/app-<hash>.css` | ~2s |
| 2. Assets | `asset-map:compile` | Copies d'assets hachées dans `public/assets/` | ~1s |
| 3. Build | `app:build` (BuildStaticSiteCommand) | `public/static/**/*.html` | ~5s |
| 4. Index | `pagefind` | Index de recherche `public/pagefind/` | ~3s |

Premier déploiement complet : ~2-3 minutes (Build Docker)
Mise à jour de contenu : ~10 secondes

### Profils de Conteneur

| Profil | Conteneurs | Cas d'utilisation |
|----------|-----------|----------|
| (aucun) | nginx uniquement | Sites statiques, pas de formulaire de contact |
| `--profile runtime` | nginx + php | Formulaire de contact activé |

Les profils sont basculés via `RUNTIME_PHP_ENABLED` dans `.env.local`.
