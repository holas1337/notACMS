---
title: "Warum ich notACMS gebaut habe"
slug: "beitraege/the-idea"
description: "Die Entstehungsgeschichte von notACMS — warum ein weiterer Static Site Generator, die KI-freundliche Philosophie und die Datenbank-Entscheidung."
date: 2024-11-01
category: releases
tags: [announcement, open-source]
template: blog/post
---

> Ausführlichere Hintergrundgeschichte in meinem persönlichen Blog: [I left WordPress](https://holas.pl/blog/why-i-left-wordpress/).

## Das Problem, auf das ich immer wieder stieß

Jedes PHP-Projekt, an dem ich arbeitete, brauchte irgendwann eine Website. Dokumentation, eine Landingpage, ein Blog — das Übliche. Und jedes Mal, wenn ich nach einem Tool griff, landete ich am selben Ort: einem Tool, das mich dazu brachte, wie es zu denken — nicht wie ein PHP-Entwickler.

Hugo ist schnell, aber seine Template-Sprache ist fremd. Jekyll setzt Ruby voraus. WordPress hat die falsche Form für statische Inhalte. Next.js ist mächtig, verwandelt aber ein Markdown-Publishing-Problem in ein JavaScript-Bundler-Problem.

Ich dachte immer wieder: Ich kenne Symfony bereits. Ich kenne Twig bereits. Ich habe Composer bereits. Warum muss ich ein neues Ökosystem lernen, nur um Text zu veröffentlichen?

## Die Entscheidung gegen eine Datenbank

Das Erste, was ich entschied: keine Datenbank. Nicht „Datenbank optional" — gar keine Datenbank. Inhalte leben in Dateien. Der Build-Prozess liest Dateien. Die Ausgabe sind Dateien.

Das erzwingt eine gewisse Disziplin. Die Inhaltsstruktur muss explizit und vorhersehbar sein. Es gibt keine Datenbankabfrage, auf die man zurückgreifen kann, wenn man verwandte Beiträge finden oder eine Sitemap generieren möchte. Alles muss aus dem Dateibaum ableitbar sein.

Diese Einschränkung stellte sich als die richtige heraus. Sie macht das System leicht verständlich, einfach zu sichern und trivial für KI-Tools nutzbar.

## KI-freundlich by Design

Ich begann darüber ernsthaft nachzudenken, als ich LLMs in meiner täglichen Arbeit einzusetzen begann. Eine KI um Hilfe beim Generieren von Inhalten, Übersetzen von Seiten oder Validieren von YAML-Schemas zu bitten, ist unkompliziert, wenn das Format Plain Text ist.

Bei einem datenbankgestützten CMS müsste man das Schema erklären, SQL schreiben und Migrationen verwalten. Mit notACMS übergibt man der KI ein Verzeichnis mit Markdown-Dateien, und sie kann Inhalte direkt lesen, generieren und bearbeiten — weil das Format einfach Text ist.

Das ist keine nachträglich hinzugefügte Funktion. Es ist der Grund, warum das System so gestaltet ist, wie es ist.

## Das Symfony-Fundament

Ich habe notACMS auf Symfony 7 aufgebaut, aus demselben Grund, warum ich Symfony für alles andere nutze: Es ist explizit, typisiert und gut dokumentiert. Der DI-Container, Konsolenbefehle, Twig — all das ist Standard-Symfony. An der Infrastruktur ist nichts Neuartiges.

Das Content-Modell ist der interessante Teil. Das Routing-System liest `_routes.yaml` und generiert Symfony-Routen aus dem Content-Baum. Der Build-Befehl rendert jede Route und schreibt statisches HTML. Die Service-Schicht ist schlank und austauschbar.

## Was als nächstes kommt

Dieses erste Release ist ein funktionierendes Fundament. Was ich noch bauen möchte:

- Pagefind-Suchintegration (kommt in v1.0.0)
- Bessere Bild-Pipeline mit WebP-Generierung
- Vollständigere mehrsprachige Unterstützung
- Eine vollwertige Design-Referenz-Seite (dazu entwickelt sich diese Seite gerade)

Das Repository ist öffentlich. Wer mitverfolgen oder beitragen möchte: der Link befindet sich in der Fußzeile.
