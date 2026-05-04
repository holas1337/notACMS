---
title: "notACMS 1.1.2 — URL canoniques et un constructeur pour les données structurées"
slug: "articles/release-1-1-2"
description: "1.1.2 redirige les URL préfixées par la locale par défaut vers leur forme canonique sans préfixe, remplace le JSON-LD inline par un constructeur Schema.org fluide et apporte deux correctifs de sécurité."
date: 2026-05-04
category: releases
tags: [release, announcement]
template: blog/post
---

## Une URL canonique par page

Si la locale par défaut de votre site est `en`, `/en/blog/` et `/blog/` étaient toutes deux accessibles et rendaient le même contenu. Cela fait deux URL indexables pour une seule page — et les moteurs de recherche choisissent celle qu'ils veulent, qui est rarement celle que vous souhaitez.

1.1.2 ajoute le `DefaultLocaleRedirectListener`. Chaque requête vers `/<locale-par-défaut>/...` retourne une `301` vers l'URL sans préfixe :

```
GET /en/blog/         →  301 Location: /blog/
GET /en/about/?ref=x  →  301 Location: /about/?ref=x
GET /pl/blog/         →  200 (locale non par défaut, intacte)
```

L'écouteur s'exécute avant le routeur Symfony, de sorte que la redirection a lieu avant même que le framework ne tente de faire correspondre une route — pas de travail gaspillé, pas de bruit 404 dans les logs. Les locales non par défaut (`pl`, `de`, `fr` sur ce site) restent intactes.

Cela ferme également une petite fuite SEO : les liens entrants qui incluent accidentellement le préfixe de locale se consolident désormais vers l'URL canonique au lieu de diviser l'autorité du domaine.

## Le JSON-LD Schema.org comme constructeur fluide

Jusqu'en 1.1.1, chaque template ayant besoin de données structurées intégrait un tableau PHP, le passait à `|json_encode|raw` et enveloppait le résultat dans une balise `<script>`. Six templates, six blocs presque identiques — et chacun une nouvelle occasion d'oublier `JSON_UNESCAPED_SLASHES` ou de livrer `<script>false</script>` si un champ du frontmatter contenait un octet invalide.

1.1.2 remplace tout cela par un service et deux fonctions Twig :

```twig
{% block structured_data %}{{ json_ld(structured_data().blogPosting(
    content.title(),
    site_base_url ~ content.url(),
    content.date(),
    content.htmlContent,
    site_author.name,
    content.image() ? site_base_url ~ content.image() : null
)) }}{% endblock %}
```

`structured_data()` retourne le constructeur ; chaînez une méthode typée (`webSite`, `webPage`, `blogPosting`, `collectionPage`, `contactPage`, `person`, `breadcrumbList`, `organization`, `imageObject`) et le résultat est un simple tableau. `json_ld(array)` l'encode et l'enveloppe dans une balise `<script type="application/ld+json">`. Les champs vides et null sont supprimés récursivement, donc les entrées optionnelles (un email d'auteur non défini, une image manquante) n'apparaissent simplement pas dans la sortie.

L'encodage utilise désormais `JSON_THROW_ON_ERROR` — un UTF-8 invalide dans le frontmatter remonte comme une véritable exception au rendu, au lieu de livrer silencieusement une balise `<script>` cassée.

Les templates de base du cœur pour les pages `contact`, `default` et `projects` émettent désormais aussi le balisage Schema.org. Auparavant, les déploiements bare avaient un SEO plus faible que tous les exemples de personnalisation que nous fournissons ; cet écart est comblé.

Consultez le [guide de personnalisation → Données structurées](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#structured-data-json-ld) pour l'API complète du constructeur et pour savoir comment surcharger le bloc `structured_data` dans votre propre thème.

## Correctifs de sécurité

Deux bugs ont été identifiés lors de la revue de 1.1.2 et corrigés avant la sortie :

- **Redirection ouverte via la normalisation des chemins.** La méthode `Request::getPathInfo()` de Symfony ne réduit pas les barres obliques répétées ; ainsi, une requête vers `/<locale-par-défaut>//evil.com` aurait sinon produit `Location: //evil.com` — une redirection relative au protocole que les navigateurs suivent vers une autre origine. Le nouvel écouteur de redirection refuse désormais d'émettre des redirections vers tout ce qui n'est pas un chemin local enraciné par une seule barre oblique.
- **XSS dans les résultats de recherche.** `assets/search.js` injectait le champ `excerpt` de Pagefind dans innerHTML sans échappement. La build de démonstration était déjà correcte ; le cœur avait dérivé. Les deux sont désormais synchronisés. Les balises de surlignage `<mark>` de Pagefind ne sont plus rendues dans les résultats de recherche du cœur — un compromis volontaire au profit d'une valeur par défaut plus sûre.

## Également nouveau en 1.1.2

- **Refactorisation interne de Twig.** La logique des barres latérales, des fils d'Ariane, des badges d'articles, des images og et des filtres de blog a été déplacée des templates vers des extensions Twig dédiées. Les templates restent concentrés sur la mise en page ; les tests peuvent cibler chaque fonction d'aide directement. Nouvelle surface de personnalisation : voir les [Aides de mise en page](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#layout-helpers) dans le guide de personnalisation.
- **Recherches par clé de répertoire en `O(1)`** dans `ContentTree` — une petite amélioration de performance sur les sites comportant des centaines de pages.
- **`docs/customization/old-template/` est désormais déprécié** et sera supprimé en 1.2.0. C'était une aide de transition ponctuelle pour 1.0→1.1 ; les nouveaux travaux de personnalisation devraient partir de `custom-footer/` ou `self-hosted-fonts/`.
- **Mises à jour des dépendances :** Symfony 7.4.8 → 7.4.9 dans tout le bundle, PHPStan 2.1.51 → 2.1.54, PHPUnit 13.1.7 → 13.1.8.

## Liste complète des modifications

Toutes les modifications avec leur catégorie : [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#112---2026-05-04).
