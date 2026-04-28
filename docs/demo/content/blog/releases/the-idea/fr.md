---
title: "L'idée"
slug: "articles/the-idea"
description: "Comment l'idée de notACMS est née — et pourquoi le monde n'a pas besoin d'un autre CMS."
date: 2026-02-01
category: releases
tags: [announcement, open-source]
template: blog/post
---

> Contexte plus complet sur mon blog personnel : [I left WordPress](https://holas.pl/blog/why-i-left-wordpress/).

## Le problème que je rencontrais sans cesse

Chaque projet PHP sur lequel je travaillais finissait par avoir besoin d'un site web. De la documentation, une page d'accueil, un blog — les classiques. Et chaque fois que je cherchais un outil, j'aboutissais au même constat : un outil qui voulait que je pense comme lui, pas comme un développeur PHP.

Hugo est rapide mais son langage de templates est déroutant. Jekyll nécessite Ruby. WordPress n'est pas adapté au contenu statique. Next.js est puissant mais il transforme un problème de publication Markdown en un problème de bundler JavaScript.

Je me répétais : je connais déjà Symfony. Je connais déjà Twig. J'ai déjà Composer. Pourquoi devrais-je apprendre un nouvel écosystème juste pour publier du texte ?

## La décision zéro base de données

La première chose que j'ai décidée : pas de base de données. Pas « base de données optionnelle » — aucune base de données du tout. Le contenu vit dans des fichiers. Le processus de compilation lit des fichiers. Le résultat est des fichiers.

Cela impose une certaine discipline. La structure du contenu doit être explicite et prévisible. Il n'y a pas de requête de base de données à laquelle se raccrocher quand vous voulez trouver des articles connexes ou générer un sitemap. Tout doit pouvoir être dérivé de l'arborescence de fichiers.

Cette contrainte s'est avérée être la bonne. Elle rend le système facile à comprendre, facile à sauvegarder et trivial pour les outils d'IA à manipuler.

## Conçu pour l'IA dès l'origine

J'ai commencé à y penser sérieusement quand je me suis mis à utiliser les LLM dans mon travail quotidien. Demander à une IA de vous aider à générer du contenu, traduire des pages ou valider des schémas YAML est simple quand le format est du texte brut.

Avec un CMS basé sur une base de données, il faudrait expliquer le schéma, écrire du SQL, gérer les migrations. Avec notACMS, vous remettez à l'IA un répertoire de fichiers Markdown et elle peut lire, générer et modifier le contenu directement — parce que le format est simplement du texte.

Ce n'est pas une fonctionnalité ajoutée après coup. C'est la raison pour laquelle le système est conçu tel qu'il est.

## Les fondations Symfony

J'ai construit notACMS sur Symfony 7 pour la même raison que j'utilise Symfony pour tout le reste : c'est explicite, typé et bien documenté. Le conteneur d'injection de dépendances, les commandes console, Twig — tout cela est du Symfony standard. Il n'y a rien de novateur dans l'infrastructure.

La partie intéressante, c'est le modèle de contenu. Le système de routage lit `_routes.yaml` et génère des routes Symfony à partir de votre arborescence de contenu. La commande de compilation rend chaque route et écrit du HTML statique. La couche de services est fine et remplaçable.

## Et ensuite

Cette première version est une base fonctionnelle. Ce que je veux construire :

- Intégration de la recherche Pagefind (prévue dans la v1.0.0)
- Un meilleur pipeline d'images avec génération WebP
- Un support multilingue plus complet
- Une véritable page de référence de design (c'est ce que ce site est en train de devenir)

Le dépôt est public. Si vous souhaitez suivre le projet ou contribuer, le lien se trouve dans le pied de page.
