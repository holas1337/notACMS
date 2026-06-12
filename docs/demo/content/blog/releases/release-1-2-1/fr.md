---
title: "notACMS 1.2.1 — correction charset, avertissements de build plus précis, nouvelles pages de documentation"
slug: "articles/release-1-2-1"
description: "1.2.1 corrige le rendu UTF-8 dans les réponses texte, affine l'avertissement de clé de répertoire ambiguë et ajoute les pages de documentation Construction de Thème et Sécurité au thème demo."
date: 2026-06-13
category: releases
tags: [release, announcement]
template: blog/post
---

## Quoi de neuf dans 1.2.1

Un petit suivi de 1.2.0 avec deux corrections de bugs et une documentation étendue.

## Charset nginx

Les réponses texte — `/llms.txt`, `/robots.txt` — étaient servies sans déclaration de charset. Les navigateurs affichaient les caractères non-ASCII comme du charabia. `charset utf-8;` est maintenant défini au niveau du bloc serveur dans `docker/nginx.conf.template`, de sorte que chaque type de réponse reçoit automatiquement la déclaration correcte.

## Avertissement de clé de répertoire ambiguë

L'avertissement "Ambiguous directory key" se déclenchait auparavant lors de la construction de l'arbre — au moment où un second élément de contenu enregistrait un basename en conflit — indépendamment du fait que cette clé courte soit jamais utilisée dans un template.

L'avertissement est maintenant différé jusqu'à la recherche effective : il se déclenche une seule fois, et uniquement quand `content_item()` ou `content_url()` est appelé avec une clé courte ambiguë. Les sites avec des collisions de noms dans différentes sections de contenu ne voient plus d'avertissements superflus pour des clés qu'ils ne référencent jamais.

## Deux nouvelles pages de documentation

Le thème demo dispose maintenant de pages pour **Construction de Thème** et **Sécurité**, disponibles dans les quatre locales.

**[Construction de Thème](/fr/construction-theme/)** couvre le modèle complet de couches de templates — comment `local/templates/`, les chemins de thème enregistrés et le bare core sont résolus par ordre de priorité — ainsi qu'une référence pour les contrats de contexte de template, les globales Twig, les fonctions et filtres, l'API ContentItem, les clés de traduction requises et les six règles de portabilité que tout thème devrait suivre.

**[Sécurité](/fr/securite/)** explique le modèle de menace qui guide les décisions de sécurité de notACMS : pas de base de données, pas de rendu dynamique, un pipeline de compilation qui n'exécute jamais le contenu. La page couvre les protections intégrées (validation du hostname Turnstile, protection contre la traversée de chemin, types stricts), la conception CSRF délibérée du formulaire de contact et les bonnes pratiques de déploiement pour une instance de production.

Les deux pages apparaissent dans le menu déroulant Documentation et dans la barre latérale.

## Changelog complet

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#121---2026-06-13)
