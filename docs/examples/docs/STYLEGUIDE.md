# Styleguide

This is the site-specific design reference for this notACMS installation. It documents the design tokens, color palette, typography, and Twig components used on this site.

For the styleguide system mechanics (how the `/styleguide/` dev page works, SCSS conventions, component update checklist), see [`docs/STYLEGUIDE.md`](../../STYLEGUIDE.md) in the repository.

---

## What It Covers

| Section | What is shown |
|---|---|
| Colors | All design token colors as swatches with variable names and hex values |
| Typography | Both font families (Open Sans, Source Code Pro), 5-step font size scale, heading hierarchy h1–h6 |
| Spacing | Visual bars for the 5-step spacing scale ($sp-1 … $sp-5); responsive breakpoints ($bp-md, $bp-lg) |
| Buttons | Primary, secondary, outline variants |
| Badges | `.post-card-badge` variants: NEW (green), UPDATED (info), DRAFT (warning), PLANNED (secondary) |
| Alerts | Info, success, danger, warning alert variants |
| Form elements | Text input, email, invalid state with error message, textarea, submit button |
| Code & blockquote | Inline code, code block, blockquote |
| Table | Sample data table |
| Skip link | `.skip-link` — accessibility link, visually hidden by default, shown on keyboard focus; forced visible in styleguide for reference |
| Navigation | `components/navigation.html.twig` with real locale-aware rendering |
| Language switcher | `components/language_switcher.html.twig` |
| Post card | `components/post_card.html.twig` — card variants; badges: `.post-card-badge` NEW (green), `--updated` (info), `--draft` (warning), `--planned` (secondary) |
| Pagination | `components/pagination.html.twig` — three variants: first page (prev disabled), middle page, last page (next disabled) |
| Share buttons | `components/share_buttons.html.twig` |
| Sidebar | `components/sidebar_top.html.twig` (search + contact) and `components/sidebar_bottom.html.twig` (recent posts, categories, tags, archive) wrapped in `.content-sidebar`; active state modifiers: `.is-active` on `li` (category/archive), `.tag-link--active` on tag cloud links |
| Cookie banner | Cookie banner with Accept + dismiss (×) buttons, `position: static` override |
| Breadcrumb | `components/breadcrumb.html.twig` — home / category / title breadcrumb |
| Post page layout | `.post`, `.post-header`, `.post-header__title`, `.post-meta`, `.post-body`, `.post-footer`, `.post-tags-section` |
| Table of contents | `.toc-wrapper` + `.toc-title` (injected by `table-of-contents.js`) |
| Related posts | `components/post_card_mini.html.twig` — compact card for related posts |
| Post navigation | `components/post_navigation.html.twig` — `.post-navigation`, `.post-navigation__item`, `--next`, `.post-navigation__label`, `.post-navigation__title` |
| Series nav | `components/series_nav.html.twig` — `.series-nav`, `.series-nav__title` |
| Page structure | `.page-header`, `.page-intro` (listing pages); `.page-content`, `.page-body` (static pages) |
| Home sections | `.home-intro`, `.section-title`, `.all-posts-link` |
| Search results | `.search-page`, `.search-page__input`, `.search-result`, `.search-result__image`, `.search-result__body`, `.search-page__no-results` |
| About profile | `.about-profile` + `.about-avatar` + `.about-info-*` — avatar, name, role |
| About skills | `.about-skills` — skills list |
| Recommendations | `.recommendation-card` (`<blockquote>`) — quote, expand/collapse, author, role, link |
| Projects grid | `.projects-grid` (responsive grid) + `.project-card` (clickable card with image, title, description, tags) |
| Coming soon | `.coming-soon`, `.coming-soon__card` — page for scheduled (future-dated) posts |
| Error page | `.error-page`, `.error-page__card` — 404/5xx error display |
| Image utilities | `.alignleft`, `.alignright`, `.aligncenter`, `figure` + `figcaption`, lightbox `data-full` trigger |

---

## Design Tokens

All tokens are defined in `assets/styles/_variables.scss`.

### Colors (base palette)

| Variable | Value | Usage |
|---|---|---|
| `$color-primary` | `#2d8a4e` | Primary actions, links, highlights |
| `$color-primary-dark` | `#1f6b3a` | Primary hover state |
| `$color-secondary` | `#6c757d` | Secondary actions, muted elements |
| `$color-secondary-dark` | `#565e64` | Secondary hover state |
| `$color-success` | `#198754` | Success states |
| `$color-danger` | `#dc3545` | Error states |
| `$color-warning` | `#ffc107` | Warning states |
| `$color-info` | `#0dcaf0` | Info states |
| `$color-light` | `#f8f9fa` | Light backgrounds, tag pills |
| `$color-dark` | `#212529` | Dark text, headings |
| `$color-white` | `#ffffff` | White backgrounds |

### Colors (semantic)

| Variable | Value | Usage |
|---|---|---|
| `$color-body-bg` | `#ffffff` | Page background |
| `$color-body` | `#212529` | Body text |
| `$color-muted` | `#6c757d` | Secondary text, meta |
| `$color-border` | `#dee2e6` | Borders, dividers |
| `$color-link` | `#2d8a4e` | Link color |
| `$color-link-hover` | `#1f6b3a` | Link hover color |

### Colors (code blocks)

| Variable | Value | Usage |
|---|---|---|
| `$color-code-bg` | `#f8f9fa` | Code block background |
| `$color-code-inline-bg` | `rgba(175, 184, 193, 0.2)` | Inline code background |

### Colors (series nav)

| Variable | Value | Usage |
|---|---|---|
| `$color-series-bg` | `#cff4fc` | Series nav background |
| `$color-series-border` | `#b6effb` | Series nav border |
| `$color-series-text` | `#055160` | Series nav text |
| `$color-series-link` | `#023e52` | Series nav link |

### Colors (alerts)

| Variable | Value | Usage |
|---|---|---|
| `$color-alert-info-bg` | `#cff4fc` | Info alert background |
| `$color-alert-info-border` | `#b6effb` | Info alert border |
| `$color-alert-info-text` | `#055160` | Info alert text |
| `$color-alert-success-bg` | `#d1e7dd` | Success alert background |
| `$color-alert-success-border` | `#badbcc` | Success alert border |
| `$color-alert-success-text` | `#0f5132` | Success alert text |
| `$color-alert-danger-bg` | `#f8d7da` | Danger alert background |
| `$color-alert-danger-border` | `#f5c2c7` | Danger alert border |
| `$color-alert-danger-text` | `#842029` | Danger alert text |
| `$color-alert-warning-bg` | `#fff3cd` | Warning alert background |
| `$color-alert-warning-border` | `#ffecb5` | Warning alert border |
| `$color-alert-warning-text` | `#664d03` | Warning alert text |

### Typography

| Variable | Value | Notes |
|---|---|---|
| `$font` | Open Sans, system-ui, -apple-system, Segoe UI, Arial, sans-serif | Body and UI text — self-hosted woff2 |
| `$font-mono` | Source Code Pro, SFMono-Regular, Menlo, Monaco, Consolas, Courier New, monospace | Code blocks — self-hosted woff2 |
| `$lh` | `1.5` | Global line-height |

### Font size scale

| Variable | rem | px | Usage |
|---|---|---|---|
| `$fs-sm` | 0.875rem | 14px | Small text, meta, labels |
| `$fs-base` | 1rem | 16px | Body text |
| `$fs-lg` | 1.125rem | 18px | Slightly larger body text |
| `$fs-xl` | 1.25rem | 20px | Sub-headings |
| `$fs-xxl` | 1.5rem | 24px | Section headings |

### Spacing scale

| Variable | rem | px |
|---|---|---|
| `$sp-1` | 0.25rem | 4px |
| `$sp-2` | 0.5rem | 8px |
| `$sp-3` | 1rem | 16px |
| `$sp-4` | 1.5rem | 24px |
| `$sp-5` | 3rem | 48px |

### Layout

| Variable | Value | Usage |
|---|---|---|
| `$container-max` | `1200px` | Max container width |
| `$sidebar-w` | `300px` | Fixed sidebar width |
| `$gap` | `2rem` | Main content / sidebar gap |
| `$bp-md` | `768px` | Tablet breakpoint |
| `$bp-lg` | `992px` | Desktop breakpoint |

### Border-radius

| Variable | Value |
|---|---|
| `$radius-sm` | 0.25rem |
| `$radius` | 0.375rem |
| `$radius-lg` | 0.5rem |
| `$radius-pill` | 50rem |

### Shadows

| Variable | Value |
|---|---|
| `$shadow-sm` | `0 1px 2px rgba(0,0,0,.05)` |
| `$shadow` | `0 .125rem .25rem rgba(0,0,0,.075)` |
| `$shadow-lg` | `0 .5rem 1rem rgba(0,0,0,.15)` |

### Animation

| Variable | Value |
|---|---|
| `$transition` | `.2s ease-in-out` |

---

## Components

All Twig components live in `templates/components/`. Each is an `{% include %}`-able partial.

| Template | Key variables |
|---|---|
| `navigation.html.twig` | `locale`, `app.request.pathInfo` |
| `language_switcher.html.twig` | `locale`, `content` (optional), `translation_map` (optional) |
| `sidebar_top.html.twig` | `sidebar` (object), `locale` — search + contact widgets |
| `sidebar_bottom.html.twig` | `sidebar` (object with `recentPosts`, `categories`, `tags`, `archiveMonths`), `locale` — recent posts, categories, tags, archive |
| `post_card.html.twig` | `post` (ContentItem), `locale`, `featured` (bool), `lcp` (bool), `sizes` (string, optional) |
| `post_card_mini.html.twig` | `post` (ContentItem), `locale` — compact card for related posts; entire element is `<a>` |
| `post_navigation.html.twig` | `prev_post` (?ContentItem), `next_post` (?ContentItem), `locale` — prev/next navigation below post |
| `responsive_img.html.twig` | `src`, `alt`, `sizes` (string), `loading` (default `lazy`), `width` (default `1280`), `height` (default `720`), `fetchpriority` (optional), `pagefind` (bool) — renders `<img>` with `srcset` (640w/960w/1280w) |
| `pagination.html.twig` | `base_url`, `current_page`, `total_pages`, `paginated_route` (string) |
| `share_buttons.html.twig` | `title` (optional), `app.request.pathInfo` |
| `breadcrumb.html.twig` | `breadcrumbs` (array of `{label, url?}`) |
| `series_nav.html.twig` | `series_posts` (ContentItem[]), `series_current_part` (int), `content` (ContentItem) |
| `cookie_banner.html.twig` | (no vars; privacy URL from Twig globals) |
| `about_profile.html.twig` | `author` (object with `.name`, `.avatar`), `role` |
| `recommendation_card.html.twig` | `rec` (object with `.quote`, `.text`, `.name`, `.role`), `expand_label`, `collapse_label` — expandable blockquote card |
| `post_meta_line.html.twig` | `post` (ContentItem), `locale` — category + date meta line |
