---
title: "Why I built notACMS"
slug: "blog/the-idea"
description: "The origin story of notACMS — why another static site generator, the AI-friendly philosophy, and the zero-database decision."
date: 2024-11-01
category: releases
tags: [announcement, open-source]
template: blog/post
---

> Longer backstory on my personal blog: [I left WordPress](https://holas.pl/blog/why-i-left-wordpress/).

## The problem I kept running into

Every PHP project I worked on eventually needed a website. Documentation, a landing page, a blog — the usual. And every time I reached for a tool, I ended up in the same place: a tool that wanted me to think like it, not like a PHP developer.

Hugo is fast but its template language is alien. Jekyll requires Ruby. WordPress is the wrong shape for static content. Next.js is powerful but it turns a Markdown publishing problem into a JavaScript bundler problem.

I kept thinking: I already know Symfony. I already know Twig. I already have Composer. Why do I need to learn a new ecosystem just to publish text?

## The zero-database decision

The first thing I decided: no database. Not "database optional" — no database at all. Content lives in files. The build process reads files. The output is files.

This forces a certain discipline. Content structure has to be explicit and predictable. There's no database query to reach for when you want to find related posts or generate a sitemap. Everything has to be derivable from the file tree.

That constraint turned out to be the right one. It makes the system easy to reason about, easy to back up, and trivially easy for AI tools to work with.

## AI-friendly by design

I started thinking about this seriously when I began using LLMs in my daily work. Asking an AI to help generate content, translate pages, or validate YAML schemas is straightforward when the format is plain text. 

With a database-backed CMS, you'd need to explain the schema, write SQL, manage migrations. With notACMS, you hand the AI a directory of Markdown files and it can read, generate, and edit content directly — because the format is just text.

This isn't a feature I added after the fact. It's the reason the system is designed the way it is.

## The Symfony foundation

I built notACMS on top of Symfony 7 for the same reason I use Symfony for everything else: it's explicit, typed, and well-documented. The DI container, console commands, Twig — all of this is standard Symfony. There's nothing novel about the infrastructure.

The content model is the interesting part. The routing system reads `_routes.yaml` and generates Symfony routes from your content tree. The build command renders each route and writes static HTML. The service layer is thin and replaceable.

## What's next

This first release is a working foundation. The things I want to build:

- Pagefind search integration (coming in v1.0.0)
- Better image pipeline with WebP generation
- More complete multilingual support
- A proper Design Reference page (that's what this site is becoming)

The repository is public. If you want to follow along or contribute, the link is in the footer.
