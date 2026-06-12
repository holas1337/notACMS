---
title: "notACMS 1.2.0 — das Audit-Release"
slug: "beitraege/release-1-2-0"
description: "Ein vollständiges Code-, Sicherheits- und Theme-Audit in einem Release: ~170 Fixes, eine gehärtete Build-Pipeline, eine dokumentierte Theme-API und einige bewusste Breaking Changes."
date: 2026-06-12
category: releases
tags: [release, security, refactoring]
template: blog/post
---

## Das Audit-Release

1.2.0 ist das Ergebnis eines vollständigen Audits von notACMS — Architektur, Sicherheit, Codequalität und beide Template-Bäume. Rund 170 Findings wurden behoben. Die Highlights:

### Robustheit

- Eine Markdown-Datei mit kaputtem Frontmatter legt nicht mehr eine ganze Sprache oder den Build lahm — sie wird übersprungen und mit Pfad und Fehler gemeldet.
- Ein fehlendes `slug:` kann die Startseite nicht mehr still übernehmen; `posts_per_page: 0` crasht den Blog nicht mehr; doppelte URLs, mehrdeutige Verzeichnisschlüssel und ungültige Tag-Werte erzeugen klare Build-Warnungen.
- Entwürfe und geplante **Seiten** werden jetzt wirklich aus Builds, Menüs und `/llms.txt` ausgeschlossen — bisher wurden nur Beiträge gefiltert.
- `app:build -o <dir>` weigert sich ohne `--force`, ein anderes Verzeichnis als das konfigurierte Static-Verzeichnis zu leeren.
- Der Aufruf von `/pl/` beim ersten Besuch leitet nicht mehr auf die englische Version um — die URL gilt als Sprachauswahl und ein Cookie wird gesetzt. Das Locale-Cookie ist jetzt auch `Secure`-bedingt, sodass der Mechanismus in HTTP-Entwicklungsumgebungen funktioniert.
- Suchergebnis-Ausschnitte zeigen jetzt hervorgehobene Begriffe als echte `<mark>`-Markierungen statt als wörtlichen `&lt;mark&gt;`-Text.
- Das bare Starter-Theme enthält jetzt Platzhalter-`contact_form`-Werte in `_site.yaml` — kein Build-Fehler mehr bei einer Neuinstallation.

### Sicherheit

- ImageMagick läuft über `Symfony\Process` mit Argument-Arrays — `exec()` ist Geschichte.
- Turnstile prüft jetzt den Antwort-Hostname gegen die `base_url` und loggt einen Fehler, wenn die mitgelieferten Test-Keys in Produktion aktiv sind. Ein Boot-Check warnt bei Platzhalter-`APP_SECRET`.
- JSON-LD-Output ist hex-escaped (`</script>` in einem Titel bricht nicht mehr aus), Übersetzungskataloge enthalten kein HTML mehr, und die nginx-Security-Header gelten jetzt auch für `/assets/`- und `/media/`-Antworten.
- Die Kontakt-API mit Sprachpräfix war im Docker-Runtime-Deployment wegen eines nginx-Regex-Bugs unerreichbar — behoben.

### Für Theme-Entwickler

- Neu: [THEME_BUILDING.md](https://github.com/holas1337/notACMS/blob/main/docs/THEME_BUILDING.md) — der vollständige Theme-API-Vertrag: Template-Kontexte pro Route, Twig-Funktionen und -Globals, die ContentItem-API und die Übersetzungsschlüssel, die jedes Theme definieren muss.
- `#[LocalizedRoute]` funktioniert jetzt in `local/src/Controller/`, der Sprachwechsel funktioniert korrekt auf Seiten mit 3+ Sprachen, und die Static-Build-Pipeline ist in wiederverwendbare Services zerlegt.

### Neu

- Der Static Build erzeugt jetzt eine `/llms.txt`-Datei pro Sprache — die neuesten Beiträge in einem maschinenlesbaren Format für LLM-Kontextfenster. Konfigurierbar über `llms_limit` in `_site.yaml` (Standard: 5). Das Template ist per-Theme über den `@base`-Twig-Namespace überschreibbar.

### Breaking Changes

Einige — jede mit einzeiliger Migration. `directoryKey()` liefert volle Content-Pfade (Basename-Lookups funktionieren weiter), `getTree()` ist nach `ContentTreeProviderInterface` umgezogen, `blogPosting()` nimmt eine benannte Map, der Kontext-Schlüssel `lang_switch_url` ist weg, vier tote Übersetzungsschlüssel wurden entfernt und das `old-template`-Paket aus 1.0 wurde eingestellt. Der vollständige Guide: [UPGRADE-1.2.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.2.md).
