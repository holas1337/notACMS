---
title: "Personnalisation"
description: "Surcharger les templates, les styles, les services PHP et la configuration serveur — tout depuis le répertoire local/."
template: page/doc
slug: personnalisation
menu:
  weight: 40
  label: "Personnalisation"
---

## Le Système de Surcharge `local/`

Chaque personnalisation se trouve dans `local/`. Ce répertoire est votre couche spécifique au site. Les répertoires core `templates/`, `assets/` et `src/` sont le framework. Vos surcharges dans `local/` ont priorité.

```
local/
├── assets/          # Surcharges SCSS + JS personnalisé
│   ├── images/      # écraser og-default.jpg
│   └── app.js       # chargé comme deuxième point d'entrée
├── content/         # tout votre contenu (pages, blog, config)
├── docker/          # config Nginx personnalisée
│   └── nginx/       # snippets .conf (redirections, pages d'erreur)
├── docs/            # EDITOR_GUIDE.md + STYLEGUIDE.md spécifiques au site
├── src/             # services PHP personnalisés (Namespace NotACms\Local\)
├── templates/       # surcharges de templates
└── translations/    # surcharges de chaînes UI (messages.*.yaml)
```

## Surcharges de Templates

Pour surcharger un template core, créez un fichier au même chemin sous `local/templates/` :

```bash
# Surcharger le template de page standard
cp templates/page/default.html.twig local/templates/page/default.html.twig
# Éditer maintenant local/templates/page/default.html.twig
```

Le chargeur Twig vérifie d'abord `local/templates/`, puis se rabat sur `templates/`. Vous pouvez surcharger n'importe quel template sans toucher aux fichiers core.

### Surcharger un Bloc Unique

Utilisez le namespace `@base` pour étendre le **template original** et ne surcharger que des blocs spécifiques :

```twig
{# local/templates/blog/post.html.twig #}
{% extends '@base/blog/post.html.twig' %}

{% block sidebar_bottom %}
    <div class="my-widget">...</div>
{% endblock %}
```

Le namespace `@base` pointe toujours vers le répertoire `templates/` original, permettant d'étendre le vrai fichier sans créer de référence circulaire.

### Remplacer Complètement un Template

Créez le fichier au même chemin sans `extends` — il remplace complètement l'original :

```twig
{# local/templates/components/navigation.html.twig #}
<nav class="my-nav">
    <a href="/">Home</a>
</nav>
```

**Surcharges fréquentes :**

| Template Core | Objectif |
|---|---|
| `templates/base.html.twig` | Layout global, Header, Footer |
| `templates/components/navigation.html.twig` | Liens de navigation |
| `templates/page/default.html.twig` | Layout de page standard |
| `templates/page/doc.html.twig` | Layout de documentation |
| `templates/blog/post.html.twig` | Layout d'article de blog |

### Surcharger les données structurées (JSON-LD)

Le template `base.html.twig` émet un schéma JSON-LD `WebSite` dans le bloc
`{% block structured_data %}`. Pour ajouter `SearchAction`, `sameAs` ou
des données `author` personnalisées pour votre site :

```twig
{# local/templates/base.html.twig #}
{% extends '@base/base.html.twig' %}

{% block structured_data %}
{{ json_ld(structured_data().webSite(
    site_name,
    site_base_url,
    structured_data().person(site_author.name, site_base_url),
    {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": site_base_url ~ path('search_' ~ locale) ~ '?q={search_term_string}'
        },
        "query-input": "required name=search_term_string"
    }
)) }}
{% endblock %}
```

Deux fonctions Twig font le travail :

- `json_ld(array)` — encode un tableau PHP en JSON et l'enveloppe dans
  une balise `<script type="application/ld+json">`.
- `structured_data()` — renvoie l'instance `StructuredDataBuilderInterface`,
  permettant d'enchaîner les méthodes typées du builder.

Le builder supprime automatiquement les valeurs vides (`null`, `''`, `[]`).
Si `site_author.email` n'est pas défini dans `_site.yaml`, la clé `email`
est absente de la sortie au lieu d'apparaître vide.

Builders disponibles : `webSite()`, `person()`, `blogPosting()`,
`collectionPage()`, `breadcrumbList()`, `contactPage()`, `webPage()`,
`organization()`, `imageObject()`.

## Surcharges SCSS

`local/assets/` est chargé comme deuxième point d'entrée Importmap (`app-local`). Créez un `local/assets/app.js` qui importe votre SCSS :

```js
// local/assets/app.js
import './styles/app_local.scss';
```

```scss
// local/assets/styles/app_local.scss
// AVERTISSEMENT : Le fichier racine doit s'appeler app_local.scss — pas app.scss ou un autre nom.
// sass-bundle nécessite des noms de base uniques pour tous les fichiers SCSS racine.

// Importer les variables SCSS de base (couleurs, espacements, typographie) pour utilisation dans les surcharges :
@import '../../../../assets/styles/variables';

// Écraser les propriétés CSS personnalisées pour votre marque :
:root {
    --accent:    #2563EB;   // passer au bleu
    --accent-bg: #EFF6FF;
}

// Styles de composants personnalisés ci-dessous
.my-custom-hero {
    background: var(--accent);
    color: #fff;
}
```

> **Important :** Lors de l'utilisation de `app-local`, vous devez aussi surcharger `base.html.twig` pour charger les deux points d'entrée. Créez `local/templates/base.html.twig` :
>
> ```twig
> {# local/templates/base.html.twig #}
> {% extends '@base/base.html.twig' %}
>
> {% block stylesheets %}
>     {{ importmap('app') }}
>     {{ importmap('app-local') }}
> {% endblock %}
> ```
>
> Passer les deux points d'entrée à `importmap()` garantit l'ordre de chargement CSS correct : `app.css` (original) d'abord, puis vos surcharges.

> **Astuce :** Surchargez toujours les propriétés CSS personnalisées (`--accent`, `--bg`, etc.) plutôt que des valeurs hex directes. Cela assure que les modes clair et sombre fonctionnent correctement avec vos couleurs de marque. La liste complète des tokens se trouve dans la [Référence Design](/fr/reference-design/).

## Services PHP Personnalisés

Créez une interface de service dans `src/Service/`, implémentez-la, et Symfony l'autowire automatiquement. Pour les extensions spécifiques au site, placez les implémentations dans `local/src/` avec le namespace `NotACms\Local\` :

```php
// local/src/Service/MyCustomService.php
namespace NotACms\Local\Service;

use NotACms\Service\Content\ContentServiceInterface;

final class MyCustomService
{
    public function __construct(
        private readonly ContentServiceInterface $content,
    ) {}

    /**
     * @return array<\NotACms\Content\ContentItem>
     */
    public function recentPosts(string $locale): array
    {
        return $this->content->getRecentPosts($locale, 5);
    }
}
```

Injectez dans un contrôleur via Constructor Injection — le conteneur DI de Symfony gère le reste.

### Avancé : Event Listeners, Filtres Twig, Décorateurs de Services

En plus des services simples, vous pouvez aussi créer des event listeners, des filtres Twig et des décorateurs de services dans `local/src/` :

```php
// local/src/EventListener/MyListener.php
namespace NotACms\Local\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class MyListener
{
    public function __invoke(MyEvent $event): void { /* ... */ }
}
```

```php
// local/src/Twig/MyExtension.php
namespace NotACms\Local\Twig;

use Twig\Attribute\AsTwigFilter;

final class MyExtension
{
    #[AsTwigFilter('my_filter')]
    public function myFilter(string $value): string { /* ... */ }
}
```

```php
// local/src/Service/MySiteConfigDecorator.php
namespace NotACms\Local\Service;

use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: SiteConfigServiceInterface::class)]
final class MySiteConfigDecorator implements SiteConfigServiceInterface
{
    public function __construct(private readonly SiteConfigServiceInterface $inner) {}
    // ne surcharger que les méthodes nécessaires
}
```

## Config Nginx Personnalisée

Pour les déploiements de production, placez les snippets de configuration Nginx dans `local/docker/nginx/`. Chaque fichier `.conf` dans ce répertoire est automatiquement inclus dans le bloc `server {}`.

Lors du premier déploiement, le bootstrap initialise trois fichiers depuis `docs/demo/docker/nginx/` :

- **`redirects.conf`** — Redirections SEO (www → non-www, URLs legacy)
- **`error-pages.conf`** — pages d'erreur sensibles à la locale
- **`csp.conf`** — Content-Security-Policy élargie pour les origines externes du thème démo (Phosphor Icons, Google Fonts)

```nginx
# local/docker/nginx/redirects.conf — Exemple
location = /old-page/ { return 301 /new-page/; }
location ~ "^\d{4}/\d{2}/\d{2}/([a-z0-9-]+)/$" { return 301 /blog/$1/; }
```

La configuration peut être répartie sur plusieurs fichiers — tous les fichiers `*.conf` sont inclus. Préfixez avec des numéros si l'ordre est important (ex. `10-redirects.conf`, `20-cache.conf`).

### Surcharge Content-Security-Policy

Le template core émet une CSP compatible avec le thème bare via une variable `$csp` définie *avant* l'include `local/docker/nginx/*.conf`, de sorte que tout fichier de surcharge peut la redéfinir. Pour élargir la policy aux assets externes de votre propre site, créez un fichier comme `local/docker/nginx/csp.conf` avec une seule directive `set` :

```nginx
# local/docker/nginx/csp.conf — élargit la CSP aux origines externes
set $csp "default-src 'self'; script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' data: cdn.example.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data:; frame-src 'self'; connect-src 'self';";
```

N'ajoutez pas un second `add_header Content-Security-Policy` — les navigateurs prennent l'intersection de plusieurs en-têtes CSP (plus restrictif, pas plus large).

## Surcharges de Traductions

Créez `local/translations/messages.en.yaml` et/ou `messages.pl.yaml`, `messages.de.yaml`, `messages.fr.yaml` avec uniquement les clés que vous souhaitez modifier. Elles seront fusionnées avec les fichiers de traduction core, les clés omises conservant leur valeur originale.

```yaml
# local/translations/messages.fr.yaml
nav:
  blog: "Articles"

footer:
  copyright: "Tous droits réservés — Mon Site"
```

Vous pouvez surcharger n'importe quelle clé des fichiers `translations/messages.*.yaml`. Les clés imbriquées utilisent la même structure YAML indentée que les fichiers originaux.

## Image OG par Défaut

L'image `og:image` de fallback, affichée lorsqu'une page n'a pas d'image de couverture, utilise par défaut un placeholder avec marque. Pour le remplacer, placez votre image sous `local/assets/images/og-default.jpg`. Le template lit automatiquement depuis ce chemin.

Lors du premier `./notACMS deploy` ou `ddev build`, le bootstrap initialise ce fichier depuis le cœur par défaut, s'il n'existe pas encore. Remplacez le fichier seedé par le vôtre.

Pour une approche complètement différente (format différent, dimensions différentes ou logique différente), surchargez le bloc `{% block og_default_image %}` dans `local/templates/base.html.twig` :

```twig
{# local/templates/base.html.twig #}
{% extends '@base/base.html.twig' %}

{% block og_default_image %}
<meta property="og:image" content="{{ site_base_url ~ asset('images/og-default.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/jpeg">
{% endblock %}
```

## Docs Locales

`local/docs/` contient deux fichiers de référence spécifiques au site, tous deux gitignored et seedés lors du premier déploiement :

**`local/docs/EDITOR_GUIDE.md`** — le guide rédactionnel pour votre site : catégories, tags approuvés, voix et styles de génération d'images. Initialisé depuis `docs/demo/docs/EDITOR_GUIDE.md`. Les mécaniques de contenu au niveau système (champs frontmatter, structure d'URL, séries, brouillons) restent dans `docs/EDITOR_GUIDE.md`.

**`local/docs/STYLEGUIDE.md`** — la référence design pour votre site : tokens de design, palette de couleurs, typographie et liste de composants. Initialisé depuis `docs/demo/docs/STYLEGUIDE.md`. Les mécaniques du styleguide (la page dev `/styleguide/`, conventions SCSS) restent dans `docs/STYLEGUIDE.md`.

Les deux fichiers sont librement modifiables après l'initialisation — le bootstrap ne les écrasera jamais.

## Extension de la Compilation

Ajoutez des étapes de build personnalisées en créant une commande Console Symfony dans `local/src/Command/` :

```php
// local/src/Command/GenerateSitemapCommand.php
namespace NotACms\Local\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'local:sitemap')]
class GenerateSitemapCommand extends Command
{
    // ...
}
```

Puis ajoutez-la à votre alias `ddev build` ou appelez-la explicitement avec `ddev exec bin/console local:sitemap`.

## Exemples Pratiques

Le répertoire `docs/customization/` contient des exemples complets et prêts à copier. Chacun contient tous les fichiers nécessaires et un README avec les commandes `cp` exactes.

| Exemple | Modèle | Ce qu'il démontre |
|---|---|---|
| `custom-post-card` | Remplacement de composant + SCSS | Layout de carte horizontal — montre l'extension `@base`, l'importmap `app-local` et la surcharge de composant |
| `self-hosted-fonts` | Remplacement complet de base + SCSS | Ajout de polices personnalisées — montre le preload, `@font-face` et le remplacement des polices du pied de page de base |
| `php-service-decorator` | PHP `#[AsDecorator]` | Contournement de la validation Turnstile — montre le namespace `NotACms\Local\` et le pattern décorateur |
| `twig-filter` | PHP `#[AsTwigFilter]` | Ajouter un filtre `|excerpt` — montre le namespace `NotACms\Local\` et l'attribut Twig |

---

## Personnalisation Production

### Variables d'Environnement

Créez `.env.local` à la racine du projet (gitignored) :

```bash
# Requis
APP_SECRET=changeme-generate-new
URL=example.com

# Réseau
NGINX_PORT=8123
CERTRESOLVER=le  # ou 'dummy' pour tester

# Optionnel : Formulaire de contact
RUNTIME_PHP_ENABLED=true
MAILER_DSN=smtp://user:pass@smtp.example.com:587
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
```

### Surcharges Docker Compose

Créez `docker-compose.override.yaml` pour les personnalisations locales :

```yaml
services:
  nginx:
    ports:
      - "8080:80"  # Surcharger NGINX_PORT pour cette machine
```

### En-têtes de Sécurité

Ajouter à `local/docker/nginx/redirects.conf` :

```nginx
# En-têtes de sécurité
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Cache des assets statiques
location ~* \.(css|js|webp|ico|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### SSL / HTTPS avec Traefik

Le Docker Compose a des labels Traefik pour le SSL automatique.

Prérequis :
- Réseau externe : `docker network create web`
- Enregistrement DNS A pointant vers l'IP du serveur
- Ports 80/443 ouverts

`.env.local` :
```bash
URL=yourdomain.com
CERTRESOLVER=le  # Let's Encrypt
#CERTRESOLVER=dummy  # Self-signed pour tester
```

Pour une config Traefik personnalisée, ajoutez des labels à `docker-compose.override.yaml`.

### Étapes de Build Personnalisées

Ajoutez des commandes post-build en éditant `scripts/rebuild-content.sh` ou créez un wrapper :

```bash
#!/bin/bash
# custom-deploy.sh
./notACMS deploy --prod
# Custom : sync vers S3, purger CDN, etc.
aws s3 sync public/static/ s3://my-bucket/ --delete
```

### Permissions de Fichiers

Assurez les bonnes permissions pour la production :

```bash
# Corriger les permissions avant le déploiement
sudo chown -R $USER:$USER .
chmod 755 local/docker/nginx/
```
