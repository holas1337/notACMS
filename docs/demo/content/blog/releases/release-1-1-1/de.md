---
title: "notACMS 1.1.1 — Deploy respektiert bestehenden Inhalt"
slug: "beitraege/release-1-1-1"
description: "1.1.1 behebt einen Bug, bei dem ./notACMS deploy --prod bestehenden local/-Inhalt überschrieb. Deploy seedet jetzt nur, wenn local/ fehlt oder leer ist — wie ddev build."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## Der Bug

`./notACMS deploy --prod` würde bei **jedem** Deploy das gesamte `local/`-Verzeichnis sichern und durch `docs/demo/` ersetzen — selbst wenn es bereits eigenen Inhalt, Templates und Anpassungen enthielt. Die Seed-Logik unterschied nicht zwischen „Nutzer möchte explizit ein Theme neu seeden" und „Nutzer möchte nur mit bestehendem Inhalt redeployen."

## Der Fix

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
