---
title: "À propos"
slug: "about"
description: "notACMS — Générateur de sites statiques convivial pour l'IA basé sur Symfony. Zéro base de données, Markdown pur, une commande pour déployer."
template: page/about
menu:
  weight: 60
  label: "À propos"
---

## Le projet

notACMS est un générateur de sites statiques construit sur Symfony 7.4 et PHP 8.5. Vous écrivez le contenu en Markdown avec du frontmatter YAML, configurez vos langues et paramètres de site dans un seul fichier YAML, et exécutez une commande pour obtenir un site HTML entièrement pré-rendu — pas de base de données, pas de PHP à l'exécution (sauf un formulaire de contact optionnel).

Chaque outil existant semblait nécessiter l'apprentissage d'un nouvel écosystème. Hugo a son propre langage de templates. Jekyll nécessite Ruby. Next.js introduit un bundler JavaScript dans ce qui devrait être un simple flux de publication statique.

notACMS est le générateur que j'aurais voulu trouver. L'architecture est une couche fine au-dessus de Symfony. Le modèle de contenu est en fichiers plats. L'étape de construction est une seule commande.

## Philosophie

**Zéro dépendance à l'exécution.** Le générateur de sites est complexe ; la sortie ne l'est pas. Un site notACMS construit est un répertoire de fichiers HTML. Hébergez-le n'importe où.

**Convivial pour l'IA.** Les fichiers Markdown + YAML plats sont trivialement lisibles par les LLM. Vous pouvez demander à une IA de générer du contenu, traduire des pages ou auditer votre schéma de frontmatter — parce que le format est juste du texte.

**PHP d'abord.** Les développeurs PHP ont des décennies de mémoire musculaire avec Symfony, Twig et Composer. notACMS s'appuie sur cela plutôt que de le combattre.

## Open source

notACMS est sous licence Apache 2.0. Le code est sur [GitHub](https://github.com/holas1337/notACMS) — ouvert aux tickets, discussions et pull requests.