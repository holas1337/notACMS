---
title: "notACMS 1.2.0 — la version de l'audit"
slug: "articles/release-1-2-0"
description: "Un audit complet du code, de la sécurité et des thèmes en une seule version : ~170 correctifs, un pipeline de build consolidé, une API de thèmes documentée et quelques changements cassants assumés."
date: 2026-06-12
category: releases
tags: [release, security, refactoring]
template: blog/post
---

## La version de l'audit

1.2.0 est le résultat d'un audit complet de notACMS — architecture, sécurité, qualité du code et les deux arborescences de templates. Environ 170 problèmes ont été corrigés. Les points forts :

### Robustesse

- Un fichier markdown au frontmatter invalide ne fait plus planter une langue entière ni le build — il est ignoré et signalé avec son chemin et le détail de l'erreur.
- Un `slug:` manquant ne peut plus détourner silencieusement la page d'accueil ; `posts_per_page: 0` ne plante plus le blog ; les URL dupliquées, les clés de répertoire ambiguës et les tags invalides produisent désormais des avertissements de build explicites.
- Les brouillons et les **pages** planifiées sont maintenant réellement exclus des builds, des menus et de `/llms.txt` — seuls les billets étaient filtrés jusqu'ici.
- `app:build -o <dir>` refuse de vider un répertoire autre que le répertoire statique configuré sans `--force`.
- Visiter `/pl/` pour la première fois ne redirige plus vers la version anglaise — l'URL est interprétée comme un choix de langue et un cookie est enregistré. Le cookie est maintenant conditionnel à `Secure` pour fonctionner aussi en HTTP.
- Les extraits des résultats de recherche affichent désormais les surlignages comme de vraies balises `<mark>`, et non comme du texte littéral `&lt;mark&gt;`.
- Le `_site.yaml` du thème de base contient maintenant des valeurs fictives pour `contact_form` — plus d'erreur de build à la première installation.

### Sécurité

- ImageMagick passe maintenant par `Symfony\Process` avec des tableaux d'arguments — `exec()` a disparu.
- Turnstile valide le hostname de la réponse par rapport à `base_url` et journalise une erreur quand les clés de test toujours-valides sont actives en production. Un contrôle au démarrage avertit si `APP_SECRET` est resté le placeholder par défaut.
- La sortie JSON-LD est hex-échappée (`</script>` dans un titre ne peut plus s'en échapper), les catalogues de traduction ne contiennent plus de HTML, et les en-têtes de sécurité nginx s'appliquent maintenant aussi aux réponses `/assets/` et `/media/`.
- L'API de contact préfixée par la langue était inaccessible dans le déploiement Docker à cause d'un bug de regex nginx — corrigé.

### Pour les créateurs de thèmes

- Nouveau : [THEME_BUILDING.md](https://github.com/holas1337/notACMS/blob/main/docs/THEME_BUILDING.md) — le contrat complet de l'API de thèmes : contextes de templates par route, fonctions et globales Twig, l'API ContentItem et les clés de traduction que chaque thème doit définir.
- `#[LocalizedRoute]` fonctionne maintenant dans `local/src/Controller/`, le changement de langue fonctionne correctement sur les sites à 3 langues et plus, et le pipeline de build statique est décomposé en services réutilisables.

### Nouveautés

- Le build statique génère désormais un fichier `/llms.txt` par langue — les billets les plus récents dans un format lisible par les LLM. Configurable via `llms_limit` dans `_site.yaml` (par défaut : 5). Le template est personnalisable par thème via le namespace Twig `@base`.

### Changements cassants

Quelques-uns — chacun avec une migration en une ligne. `directoryKey()` renvoie maintenant les chemins de contenu complets (la recherche par nom de base fonctionne toujours), `getTree()` a été déplacé vers `ContentTreeProviderInterface`, `blogPosting()` accepte une map nommée, la clé de contexte `lang_switch_url` a disparu, quatre clés de traduction inutilisées ont été supprimées et le paquet `old-template` de la 1.0 a été retiré. Le guide complet : [UPGRADE-1.2.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.2.md).
