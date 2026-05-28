---
title: "notACMS 1.1.4 — mise à jour de sécurité Symfony 7.4.13 et Twig 3.27.0"
slug: "articles/release-1-1-4"
description: "1.1.4 est une version uniquement sécurité : Symfony 7.4.13 et Twig 3.27.0 corrigent 11 CVEs dont un contournement de firewall, un contournement SSRF et cinq contournements du sandbox Twig."
date: 2026-05-28
category: releases
tags: [release, security, symfony]
template: blog/post
---

## Mise à jour de sécurité : Symfony 7.4.13 et Twig 3.27.0

**Mettez à jour immédiatement.** Lancez `composer update` dans votre projet, puis rebuilder.

### Symfony 7.4.13 (6 CVEs)

- **CVE-2026-48489** — contournement du firewall de sécurité : le handler `failure_forward` honorait un paramètre `_failure_path` contrôlé par l'attaquant dans la sous-requête interne, permettant de contourner le firewall.
- **CVE-2026-48736** — contournement SSRF dans `NoPrivateNetworkHttpClient` et `IpUtils::PRIVATE_SUBNETS` via des formes de transition IPv6 (IPv4-mapped, IPv4-compatible, 6to4, Teredo).
- **CVE-2026-48761** — `HtmlSanitizer` ne nettoyait pas les attributs URL sur `<object>`, `<applet>`, `<iframe>`, `<img>` et l'URL dans le contenu `<meta http-equiv="refresh">`.
- **CVE-2026-48760** — `HtmlSanitizer` acceptait des marques BiDi encodées en pourcentage et des espaces Unicode dans les URLs, permettant un contournement du sanitizer par usurpation visuelle.
- **CVE-2026-48784** — `UrlGenerator` encodait incorrectement les segments `../` et `./` chaînés, produisant des URLs pouvant sortir du chemin prévu.
- **CVE-2026-48747** — Mailer : l'algorithme de signature du webhook Mailomat n'était pas fixé à SHA-256, permettant des attaques par substitution d'algorithme.

Liste complète : [symfony.com/blog/symfony-7-4-13-released](https://symfony.com/blog/symfony-7-4-13-released).

### Twig 3.27.0 (5 CVEs)

Les cinq sont des contournements de sandbox. Si votre thème rend des templates contrôlés par l'utilisateur dans un sandbox, ils sont critiques :

- **CVE-2026-46636** — contournement de la liste d'autorisation des filtres, tags et fonctions lorsque l'état du sandbox change entre les rendus dans des workers de longue durée (ex. FrankenPHP, RoadRunner).
- **CVE-2026-48808** — le filtre `column` contournait la liste des propriétés autorisées sous `SourcePolicyInterface`.
- **CVE-2026-48806** — contournement de la politique `__toString()` via des clés de mapping dynamiques : `{% set arr = {(obj): "value"} %}`.
- **CVE-2026-48807** — contournement de la politique `__toString()` via des objets `Traversable` dans les filtres `join`/`replace` et les opérateurs `in`/`not in`.
- **CVE-2026-48805** — régression d'état du sandbox dans les wrappers internes dépréciés (`twig_check_arrow_in_sandbox()`, `twig_array_some()`, `twig_array_every()`).

## Aussi dans 1.1.4

- `symfony/polyfill-*` mis à jour de v1.37.0 à v1.38.1.
- Dépendances de développement : `phpstan/phpstan` 2.1.55 → 2.2.1, `phpunit/phpunit` 13.1.10 → 13.1.13, `rector/rector` 2.4.4 → 2.4.5.

## Comment mettre à jour

```bash
composer update
ddev build   # ou la commande de build équivalente dans votre environnement
```

Aucune modification de configuration ni étape de migration n'est nécessaire.

## Changelog complet

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#114---2026-05-28)
