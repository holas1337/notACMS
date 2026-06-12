---
title: "Sécurité"
description: "Modèle de sécurité de notACMS — protections intégrées, modèle de menace et bonnes pratiques de déploiement."
template: page/doc
slug: securite
menu:
  weight: 80
  label: "Sécurité"
---

## Modèle de Menace

notACMS est un **constructeur de site statique** : la production sert du HTML pré-rendu depuis `public/static/` via nginx. PHP s'exécute au moment de la compilation et, optionnellement, à l'exécution pour un seul endpoint — `POST /api/contact`. Les auteurs de contenu sont de confiance (ils possèdent le déploiement) ; l'adversaire est le visiteur web anonyme, dont la surface d'attaque est nginx plus cet unique endpoint de contact.

Ce modèle élimine des catégories entières de vulnérabilités par conception :

- **Pas de base de données** — aucune surface d'injection SQL
- **Pas de rendu de page dynamique** — les pages sont des fichiers pré-construits ; nginx les sert directement sans exécuter le contenu
- **Le pipeline de compilation n'exécute jamais le contenu** — Markdown est analysé par League CommonMark ; le frontmatter par Symfony YAML sans balises personnalisées ; le traitement d'images fait appel à ImageMagick avec chaque argument échappé par le shell

## Fonctionnalités de Sécurité

- **Cloudflare Turnstile** — protection anti-falsification sur le formulaire de contact. Le validateur vérifie le `hostname` rapporté par Cloudflare contre votre `base_url` configurée, rejetant les tokens créés sur des domaines étrangers. Une erreur est journalisée lorsque des clés de test always-pass sont détectées en dehors du mode debug.
- **Protection contre la traversée de chemin** — `MediaFileResolver` valide que les chemins résolus restent dans le répertoire de contenu.
- **Types stricts** — tous les fichiers PHP utilisent `declare(strict_types=1)` pour prévenir les vulnérabilités de coercition de type.
- **Validation des entrées** — le formulaire de contact utilise le composant Form de Symfony avec des contraintes de validation intégrées.
- **En-têtes de sécurité** — nginx émet `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` et une `Content-Security-Policy` configurable sur chaque réponse.
- **Endpoints debug uniquement** — les bascules de prévisualisation brouillon/planifié et le styleguide sont conditionnés par `kernel.debug` et retournent 404 en production.

## CSRF du Formulaire de Contact

Le formulaire de contact a `csrf_protection: false` dans `ContactType`. C'est intentionnel : Turnstile fournit la propriété anti-falsification. Les tokens Turnstile sont à usage unique et liés aux domaines autorisés de la clé de site, de sorte qu'un attaquant cross-site ne peut pas créer un token valide. L'endpoint rejette fermement les soumissions sans token, et le formulaire est inactif quand aucune clé de site n'est configurée — Turnstile est effectivement obligatoire pour la fonctionnalité de contact.

**Les déploiements de production doivent remplacer les clés de test always-pass committées** dans `.env.local`. Les opérateurs qui souhaitent une protection anti-falsification différente peuvent surcharger `ContactType` dans `local/src/`.

## Bonnes Pratiques de Déploiement

- **Ne jamais committer `.env.local`** — il contient de vrais secrets et est dans le gitignore pour une bonne raison.
- **Remplacer les secrets de démonstration** — le fichier `.env` contient des clés Turnstile de substitution ; remplacez-les dans `.env.local` avant de déployer.
- **Maintenir les dépendances à jour** — exécutez régulièrement `composer audit` et mettez à jour les paquets quand des avis de sécurité sont publiés.
- **Utiliser HTTPS en production** — la configuration Docker Compose inclut Certbot pour TLS automatique ; ne pas servir le site en HTTP simple.

## Signaler une Vulnérabilité

Ne pas ouvrir de ticket public pour des vulnérabilités de sécurité. Signaler en privé via le [formulaire de contact](/fr/contact/).
