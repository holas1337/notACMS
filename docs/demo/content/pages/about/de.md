---
title: "Über das Projekt"
slug: "ueber-uns"
description: "notACMS — KI-freundlicher Static Site Generator auf Symfony-Basis. Keine Datenbank, reines Markdown, ein Befehl zum Bereitstellen."
template: page/about
menu:
  weight: 60
  label: "Über das Projekt"
---

## Das Projekt

notACMS ist ein Static Site Generator, der auf Symfony 7.4 und PHP 8.5 aufbaut. Du schreibst Inhalte in Markdown mit YAML-Frontmatter, konfigurierst Locales und Website-Einstellungen in einer einzigen YAML-Datei und führst einen Befehl aus — das Ergebnis ist eine vollständig vorgerenderte HTML-Website. Keine Datenbank, kein PHP zur Laufzeit (außer einem optionalen Kontaktformular).

Jedes vorhandene Werkzeug schien zu verlangen, ein neues Ökosystem zu lernen. Hugo hat seine eigene Templatesprache. Jekyll setzt Ruby voraus. Next.js bringt einen JavaScript-Bundler in einen Workflow, der eigentlich einfaches statisches Publizieren sein sollte.

notACMS ist der Generator, den ich mir gewünscht hätte. Die Architektur ist eine dünne Schicht über Symfony. Das Inhaltsmodell besteht aus Flat Files. Der Build-Schritt ist ein einziger Befehl.

## Philosophie

**Keine Laufzeit-Abhängigkeiten.** Der Site Generator ist komplex; die Ausgabe nicht. Eine fertig gebaute notACMS-Seite ist ein Verzeichnis mit HTML-Dateien. Überall hostbar.

**KI-freundlich.** Flat Markdown- und YAML-Dateien sind für Sprachmodelle trivial lesbar. Du kannst eine KI bitten, Inhalte zu generieren, Seiten zu übersetzen oder dein Frontmatter-Schema zu prüfen — weil das Format einfach Text ist.

**PHP first.** PHP-Entwickler haben jahrzehntelange Erfahrung mit Symfony, Twig und Composer. notACMS setzt genau darauf, anstatt dagegen zu arbeiten.

## Open Source

notACMS steht unter der Apache-2.0-Lizenz. Der Code liegt auf [GitHub](https://github.com/holas1337/notACMS) — offen für Issues, Diskussionen und Pull Requests.