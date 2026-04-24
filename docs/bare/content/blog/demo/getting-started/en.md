---
title: "Getting Started with notACMS"
slug: "blog/getting-started"
description: "Learn how to install notACMS and build your first static site."
date: 2025-04-15
category: demo
tags: [demo]
image: /media/getting-started/featured.webp
image_alt: "A placeholder image for the getting started guide"
updated: 2025-05-01
toc: true
template: blog/post
---

## Getting Started with notACMS

This guide walks you through installing notACMS, creating your first page, and deploying a static site.

## Installation

notACMS requires **PHP 8.5**, **Symfony**, and **DDEV** for local development.

```bash
git clone https://github.com/holas1337/notACMS.git
cd notACMS
ddev start
```

Once the containers are running, install PHP dependencies:

```bash
ddev exec composer install
```

## Writing Content

Content lives in `content/` as Markdown files with YAML frontmatter:

```markdown
---
title: "My First Post"
slug: "blog/first-post"
date: 2025-04-15
category: general
tags: [general]
---

Your post body here.
```

## Deployment

Build static HTML with a single command:

```bash
./notACMS deploy
```

The output is placed in `public/static/` — plain HTML files ready to serve with nginx or upload to any static host.

## Next Steps

- Read the [Architecture docs](https://github.com/holas1337/notACMS/blob/main/docs/ARCHITECTURE.md)
- Customize templates in `local/templates/`
- Add translations in `local/translations/`
