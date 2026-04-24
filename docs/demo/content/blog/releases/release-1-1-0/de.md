---
title: "notACMS 1.1.0 — Schlanker Kern, Demo-Theme, freie Wahl"
slug: "beitraege/release-1-1-0"
description: "1.1.0 teilt notACMS in einen minimalen Kern und ein optionales Demo-Theme. Wähle deinen Ausgangspunkt mit --bare oder --demo; alles passt du über local/ an, ohne den Kern zu berühren."
date: 2026-04-24
category: releases
tags: [release, announcement]
template: blog/post
---

## Zwei Themes, eine Entscheidung beim ersten Build

Die wichtigste Änderung in 1.1.0: notACMS liefert jetzt zwei Themes ab Werk, und du wählst beim allerersten Build eines davon aus:

```bash
./notACMS deploy --demo    # Standard — das Amber-Phosphor-Design, das du hier siehst
./notACMS deploy --bare    # ein minimales Wireframe: Systemschriften, Light Mode, ~200 Zeilen CSS
```

`ddev build` unterstützt dieselben Flags. Egal, welches du wählst — alle Funktionen laufen: mehrsprachige Inhalte, Blog, RSS, Sitemap, Suche, Kontaktformular, Bilder mit responsiven Varianten, Lesezeit, Lesefortschritt. Der Unterschied liegt nur in der Optik — und vor allem darin, wie viel du erbst, bevor du mit dem Anpassen beginnst.

### Schlanker Kern

Der Kern in `templates/`, `assets/`, `translations/` ist jetzt ein Wireframe. Er ist bewusst minimal gehalten, damit das Überschreiben eines einzelnen Blocks nicht gleich eine visuelle Sprache mitschleift, gegen die du ankämpfen musst. Wenn du ein maßgeschneidertes Design baust, starte mit dem Bare-Theme und du besitzt das gesamte Erscheinungsbild ab Tag eins.

### Demo-Theme

Das Demo lebt unter `docs/demo/` und wird nach `local/` kopiert, wenn du `--demo` wählst. Es ist das vollständige Amber-Phosphor-Design, das du gerade liest — Dark Mode, Such-Overlay, Docs-Sidebar, Theme-Umschalter, alles dabei. Starte hier, wenn du heute ein poliertes Design willst und planst, zu tweaken statt neu zu bauen.

## Das `local/`-Override-System

Beide Themes nutzen denselben Mechanismus: Jede Datei in `local/` hat Vorrang vor dem entsprechenden Pfad im Kern.

| Schicht | Override-Ort | Resolver |
|---|---|---|
| Twig-Templates | `local/templates/*.html.twig` | Symfony-Kernel |
| SCSS-Entrypoint | `local/assets/styles/app_local.scss` | Sass-Bundle |
| JS-Entrypoint | `local/assets/app.js` | AssetMapper-Importmap |
| Übersetzungen | `local/translations/messages.*.yaml` | Symfony-Translator |
| Inhalte | `local/content/**` | Parameter `notacms_content` |
| Nginx-Snippets | `local/docker/nginx/*.conf` | Container-Entrypoint |

Der Kern wird nie bearbeitet. `git pull` bleibt sauber. Anpassungen leben im Repo deiner Site, nicht in einem Fork dieses Repos.

## Upgrade von 1.0.0

Wenn du 1.0.0 benutzt hast und deine Site exakt so aussehen soll wie vorher, ist das Kompatibilitätspaket ein Einzeiler:

```bash
cp -r docs/customization/old-template/. local/
ddev build
```

Das legt das komplette Theme vor 1.1.0 — Templates, SCSS, Schriften, Bilder, Übersetzungen — in `local/` ab. Deine Site rendert exakt wie zuvor. Anschließend kannst du Dateien aus `local/` selektiv entfernen, sobald du neue Design-Elemente übernimmst.

Die echten Breaking Changes, auf die du stößt, wenn du den alten Kern angepasst hast:

- **SCSS-Entrypoint umbenannt**: `local/assets/styles/local.scss` → `local/assets/styles/app_local.scss`. Benenne die Datei um und passe den Import in `local/assets/app.js` an.
- **SCSS-Farbvariablen im Kern durch CSS Custom Properties ersetzt**: `$color-body` → `var(--text)`, `$color-link` → `var(--accent)` usw. Vollständige Zuordnung in [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md).
- **Übersetzungsschlüssel entfernt** aus dem Kern (im Demo-Theme weiterhin vorhanden): `header.tagline`, `nav.projects`, `nav.search`, `blog.published_on`, `blog.comments_disabled`. Wenn deine Templates `'...'|trans` auf diese Schlüssel aufrufen, passe das an.
- **Verzeichnis `docs/examples/` entfernt**: Die nützlichen Teile sind nach `docs/customization/old-template/` umgezogen; die Scratch-Override-Beispiele wurden durch das Bare/Demo-Modell abgelöst.

Siehe [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md) für die vollständige Liste mit Vorher/Nachher-Snippets.

## Ebenfalls neu in 1.1.0

- **Lesezeit** und **Lesefortschritt** auf Posts, Docs und Releases.
- **Sprachumschalter** als Twig-Extension (`lang_switch_urls`) mit korrekten Fallback-Ketten für Archive, paginierte Listen und Startseiten.
- **Post-Excerpts entfernen Überschrift-Anker** — keine verirrten `#`-Zeichen mehr in den Listenkarten.
- **Test-Suite-Gerüst** unter `tests/Unit/`, `tests/Integration/`, `tests/Fixtures/` mit initialer Abdeckung und einem vom Host ausführbaren `test`-Befehl.
- **KI-Agent-Skills** unter `.claude/skills/` für die Arbeit am Repo: Locales hinzufügen, Doc-Alignment-Checks, Site-Sweeps, Übersetzungen und Erstellen von Upgrade-Guides.

## Vollständiges Changelog

Jede Änderung mit Kategorie: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#110---2026-04-24).
