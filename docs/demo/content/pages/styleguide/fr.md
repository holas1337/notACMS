---
title: "Référence Design"
description: "Le système de design notACMS — tokens de couleur, typographie, composants et référence des classes SCSS."
template: page/styleguide
slug: reference-design
menu:
  weight: 70
  label: "Référence Design"
---

Cette page est une référence vivante pour le système de design notACMS. Chaque composant présenté ici correspond à une classe SCSS nommée. Surchargez les tokens dans `local/assets/styles/` pour appliquer votre identité visuelle.

## Tokens de couleur

Propriétés CSS personnalisées définies dans `assets/styles/_tokens.scss`. Surchargez n'importe laquelle dans `local/assets/styles/` pour rethématiser l'ensemble du site. Le tableau ci-dessous met en évidence les tokens les plus couramment surchargés — consultez `_tokens.scss` pour l'ensemble complet, y compris les groupes `--hero-*`, `--success-*`, `--danger-*` et `--code-*`.

| Token | Clair | Sombre | Utilisation |
|---|---|---|---|
| `--bg` | `#ffffff` | `#0e0d0b` | Arrière-plan de page |
| `--bg-surface` | `#f8f9fa` | `#161410` | Surfaces surélevées, barre latérale |
| `--bg-elevated` | `#f1f3f5` | `#1e1c17` | Boutons, arrière-plan de code en ligne |
| `--text` | `#212529` | `#f8f9fa` | Texte principal |
| `--text-muted` | `#6c757d` | `#9ca3af` | Texte secondaire, légendes |
| `--border` | `#dee2e6` | `#2d2a24` | Séparateurs, bordures de cartes |
| `--accent` | `#B45309` | `#FFB000` | Liens, états actifs, CTA |
| `--accent-bg` | `#fffbeb` | `rgba(255,176,0,.08)` | Arrière-plans d'accent |
| `--sidebar-bg` | `#f8f9fa` | `#111009` | Barre latérale de documentation |
| `--code-bg` | `#f6f8fa` | `#0d1117` | Arrière-plan de bloc de code |
| `--nav-bg` | verre sombre | verre sombre | Toujours sombre |
| `--footer-bg` | `#0e0d0b` | `#0e0d0b` | Toujours sombre |

## Typographie

L'échelle typographique est définie comme variables SCSS dans `assets/styles/_variables.scss`.

| Variable | Taille | Utilisation |
|---|---|---|
| `$fs-xs` | 11px | Étiquettes, badges, métadonnées mono |
| `$fs-sm` | 13px | Légendes, petit texte |
| `$fs-base` | 16px | Texte d'interface par défaut |
| `$fs-lg` | 17px | Corps de texte |
| `$fs-xl` | 22px | Titres H2 |
| `$fs-xxl` | 28px | Variante réduite de H1 |

**Polices :** `Inter` (interface/corps) + `JetBrains Mono` (code, étiquettes, éléments mono). Chargées via le CDN Google Fonts.

## Composants de prose

Tout le contenu Markdown est enveloppé dans `.prose`. Ces classes sont définies dans `assets/styles/_prose.scss`.

### Titres

Les titres H2 comportent une bordure inférieure et `scroll-margin-top` pour la précision des ancres de la table des matières. Les H3 et H4 sont progressivement plus petits et plus clairs.

### Blocs de code

```bash
# Un bloc de code avec l'étiquette de langage bash
ddev build
```

```yaml
# Exemple de configuration YAML
site:
  name: "My Site"
  locales:
    en:
      label: "English"
```

### Encadrés

Les encadrés utilisent le composant `.callout` (défini dans `assets/styles/_components.scss`) :

> **Astuce :** Utilisez les encadrés pour mettre en évidence des informations importantes. L'élément de citation en bloc est rendu sous forme d'encadré stylisé dans la prose notACMS.

### Tableaux

Voir le tableau des tokens de couleur ci-dessus. Les tableaux sont stylisés avec une police monoespace pour la ligne d'en-tête et la couleur d'accent pour la première colonne de données.

## Composants de navigation

### `.sidebar-nav` — Barre latérale de documentation

```
.docs-sidebar
  .sidebar-section
    .sidebar-label       ← en-tête « Documentation »
    .sidebar-nav
      li > a             ← lien par défaut
      li > a.is-active   ← page active
    .sidebar-icon        ← conteneur d'icône
  .sidebar-divider
```

### `.toc-list` — Table des matières (barre latérale droite)

Générée automatiquement à partir des titres h2/h3 dans `.docs-content .prose` par `docs-toc.js`. L'élément actif est suivi via un `IntersectionObserver`.

### `.prev-next-nav` — Précédent/Suivant

```
.prev-next-nav
  .prev-next-card               ← lien vers la page précédente
  .prev-next-card.prev-next-card--next  ← lien vers la page suivante
    .prev-next-dir              ← étiquette « Précédent » / « Suivant »
    .prev-next-title            ← titre de la page
```

## Composants de cartes

### `.feature-card`

Utilisée dans la grille de fonctionnalités de la page d'accueil. Contient `.feature-icon` + titre + description.

### `.release-card`

Utilisée sur la page de liste des versions. Contient `.release-card-meta`, titre, extrait et lien `.read-more`.

### `.stat-card`

Utilisée dans la section « Qu'est-ce que notACMS ? » de la page d'accueil. Contient `.stat-card-num`, `.stat-card-label`, `.stat-card-desc`.

### `.prev-next-card` (articles de blog)

Le même composant `.prev-next-nav` est réutilisé pour la navigation précédent/suivant des articles de blog.

## Référence des icônes Phosphor

Les icônes sont chargées via le CDN Phosphor Icons. Utilisez la syntaxe `<i class="ph ph-{name}"></i>`.

| Icône | Classe | Utilisation |
|---|---|---|
| Livre ouvert | `ph-book-open` | Lien du manuel dans la barre latérale |
| Arborescence | `ph-tree-structure` | Lien d'architecture dans la barre latérale |
| Curseurs | `ph-sliders` | Lien de personnalisation dans la barre latérale |
| Globe | `ph-globe` | Lien des locales dans la barre latérale |
| Palette | `ph-palette` | Lien de référence design dans la barre latérale |
| Fusée | `ph-rocket` | Lien des versions dans la barre latérale |
| Logo GitHub | `ph-github-logo` | Liens GitHub |
| Flèche droite | `ph-arrow-right` | CTA, étiquettes « suivant » |
| Flèche gauche | `ph-arrow-left` | Étiquettes « précédent » |
| Flèche externe | `ph-arrow-square-out` | Liens externes |
| Lune | `ph-moon` | Bascule de thème (état mode clair) |
| Soleil | `ph-sun` | Bascule de thème (état mode sombre) |
| Ampoule | `ph-lightbulb` | Encadrés d'astuces |
| Chevron bas | `ph-caret-down` | Menu déroulant de documentation dans la navigation |
| Éclair | `ph-lightning` | Statistiques, rapidité |
| Loupe | `ph-magnifying-glass` | Recherche |
| Code | `ph-code` | Fonctionnalités Code/PHP |
| Logo Markdown | `ph-markdown-logo` | Fonctionnalité Markdown |
| Liste | `ph-list` | Bascule de la barre latérale mobile |
