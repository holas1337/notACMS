---
title: "About"
slug: "about"
description: "notACMS — AI-friendly static site generator built on Symfony. Zero database, pure Markdown, one command to deploy."
template: page/about
menu:
  weight: 60
  label: "About"
---

## The project

notACMS is a static site generator built on Symfony 7.4 and PHP 8.5. You write content in Markdown with YAML frontmatter, configure your locales and site settings in a single YAML file, and run one command to get a fully pre-rendered HTML site — no database, no runtime PHP (except an optional contact form).

Every existing tool seemed to require learning a new ecosystem. Hugo has its own template language. Jekyll wants Ruby. Next.js brings a JavaScript bundler into what should be a simple static publishing workflow.

notACMS is the generator I wanted to exist. The architecture is a thin layer on top of Symfony. The content model is flat files. The build step is one command.

## Philosophy

**Zero dependencies at runtime.** The site generator is complex; the output is not. A built notACMS site is a directory of HTML files. Serve it from anywhere.

**AI-friendly.** Flat Markdown + YAML files are trivially readable by LLMs. You can ask an AI to generate content, translate pages, or audit your frontmatter schema — because the format is just text.

**PHP first.** PHP developers have decades of Symfony, Twig, and Composer muscle memory. notACMS leans into that rather than fighting it.

## Open source

notACMS is licensed under Apache 2.0. The code is on [GitHub](https://github.com/holas1337/notACMS) — open to issues, discussions, and pull requests.
