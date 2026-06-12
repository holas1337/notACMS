---
title: "notACMS 1.2.1 — charset fix, smarter build warnings, new docs pages"
slug: "blog/release-1-2-1"
description: "1.2.1 fixes UTF-8 rendering in plain-text responses, tightens the ambiguous directory key warning, and adds Theme Building and Security documentation pages to the demo theme."
date: 2026-06-13
category: releases
tags: [release, announcement]
template: blog/post
---

## What's in 1.2.1

A small follow-up to 1.2.0 with two bug fixes and expanded documentation.

## nginx charset

Plain-text responses — `/llms.txt`, `/robots.txt` — were served without a charset declaration. Browsers would render non-ASCII characters as garbage. `charset utf-8;` is now set at the server-block level in `docker/nginx.conf.template`, so every response type gets the correct declaration automatically.

## Ambiguous directory key warning

The "Ambiguous directory key" warning previously fired at tree construction time — the moment a second content item registered a conflicting basename — regardless of whether that bare key was ever actually used in a template.

Now the warning is deferred to the actual lookup: it fires once, and only when `content_item()` or `content_url()` is called with an ambiguous bare key. Sites with naming collisions in different content sections no longer see spurious warnings from keys they never reference.

## Two new documentation pages

The demo theme now has pages for **Theme Building** and **Security**, available in all four locales.

**[Theme Building](/theme-building/)** covers the full template layer model — how `local/templates/`, registered theme paths, and the bare core resolve in priority order — plus a reference for template context contracts, Twig globals, functions and filters, the ContentItem API, required translation keys, and the six portability rules every theme should follow.

**[Security](/security/)** explains the threat model that shapes notACMS's security decisions: no database, no dynamic rendering, a build pipeline that never executes content. It covers the built-in protections (Turnstile hostname validation, path traversal guarding, strict types), the deliberate CSRF design on the contact form, and the deployment best practices for a production instance.

Both pages appear in the Documentation nav dropdown and sidebar.

## Full changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#121---2026-06-13)
