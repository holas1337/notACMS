---
title: "notACMS 1.1.1 — Navigationslabels aus Frontmatter und deploy ohne Überschreiben"
slug: "beitraege/release-1-1-1"
description: "1.1.1 führt nav labels aus Frontmatter (statt Übersetzungskeys) ein und behebt den deploy --prod-Bug, der local/-Inhalt überschrieb."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## Navigationslabels aus Frontmatter

Navigationslabels für Content-Seiten kommen jetzt aus dem Frontmatter — nicht aus Übersetzungskeys. Setze `menu.label` im Frontmatter einer Seite und es wird zum Label in der Navigation; lass es weg und der Seiten-`title` wird als Fallback genutzt.

```yaml
---
title: "Architektur-Leitfaden"
slug: "architecture-guide"
menu:
  label: "Architektur"
  weight: 30
---
```

Die neue Twig-Funktion `content_item()` löst jede Content-Seite über den Directory-Key auf:

```twig
{{ content_item('architecture-guide', 'de').menuLabel() }}
{# → "Architektur" (oder der Seitentitel, wenn menu.label fehlt) #}
```

**Warum das wichtig ist:** Bisher musste jede `messages.*.yaml` pro Locale Übersetzungen für `nav.home`, `nav.blog`, `nav.about`, `nav.contact`, `nav.privacy_policy` (Core) und `site.releases`, `site.about`, `site.manual`, `site.architecture`, `site.customization`, `site.locales`, `site.design_reference`, `site.contact` (Demo) enthalten. Eine neue Seite bedeutete N Übersetzungsdateien zu aktualisieren. Jetzt reicht ein Frontmatter-Feld. Custom Templates, die auf die entfernten Keys verweisen, sollten auf `content_item('key', locale).menuLabel()` umsteigen.

## Deploy respektiert bestehenden Inhalt

### Der Bug

`./notACMS deploy --prod` würde bei **jedem** Deploy das gesamte `local/`-Verzeichnis sichern und durch `docs/demo/` ersetzen — selbst wenn es bereits eigenen Inhalt, Templates und Anpassungen enthielt. Die Seed-Logik unterschied nicht zwischen „Nutzer möchte explizit ein Theme neu seeden" und „Nutzer möchte nur mit bestehendem Inhalt redeployen."

### Der Fix

Deploy verhält sich jetzt genauso wie `ddev build`:

- **Kein `--bare` / `--demo` Flag** → seedet `local/` nur wenn es fehlt oder leer ist; überspringt, wenn Inhalt vorhanden ist.
- **`--bare` oder `--demo` explizit übergeben** → sichert bestehendes `local/` und seedet das gewählte Theme.

```bash
./notACMS deploy --prod          # bestehendes local/ bleibt erhalten
./notACMS deploy --prod --demo   # erzwingt Neu-Seed aus docs/demo/
./notACMS deploy --prod --bare   # erzwingt Neu-Seed aus docs/bare/
```

`--prod` steuert jetzt nur Composer-Flags (`--no-dev`) und die Laufzeitumgebung (`APP_ENV=prod`). Es berührt `local/` nie.

## Ebenfalls in 1.1.1

- **Dependency-Update:** `symfony/polyfill-*` Pakete aktualisiert von v1.36.0 auf v1.37.0.
- **Polnische, deutsche und französische Demo-Inhalte** überprüft und verbessert auf allen Seiten und Blog-Posts.
- **Übersetzungsstil-Guides** zum AI-Agent-Skill-System für Polnisch, Deutsch und Französisch hinzugefügt, um konsistente Qualität bei zukünftigen Übersetzungen sicherzustellen.

## Vollständige Liste der Änderungen

Alle Änderungen mit Kategorien: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#111---2026-04-26).
