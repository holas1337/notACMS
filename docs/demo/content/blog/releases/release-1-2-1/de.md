---
title: "notACMS 1.2.1 — Charset-Fix, präzisere Build-Warnungen, neue Dokumentationsseiten"
slug: "beitraege/release-1-2-1"
description: "1.2.1 behebt die UTF-8-Darstellung in Plaintext-Antworten, präzisiert die Warnung für mehrdeutige Directory-Keys und fügt die Dokumentationsseiten Theme-Entwicklung und Sicherheit zum Demo-Theme hinzu."
date: 2026-06-13
category: releases
tags: [release, announcement]
template: blog/post
---

## Was ist neu in 1.2.1

Ein kleines Follow-up zu 1.2.0 mit zwei Bugfixes und erweiterter Dokumentation.

## nginx Charset

Plaintext-Antworten — `/llms.txt`, `/robots.txt` — wurden ohne Charset-Deklaration ausgeliefert. Browser stellten Nicht-ASCII-Zeichen als Kauderwelsch dar. `charset utf-8;` ist jetzt auf Server-Block-Ebene in `docker/nginx.conf.template` gesetzt, sodass jeder Antworttyp automatisch die korrekte Deklaration erhält.

## Mehrdeutige Directory-Key-Warnung

Die Warnung "Ambiguous directory key" wurde bisher zur Zeit des Baum-Aufbaus ausgelöst — in dem Moment, wenn ein zweites Content-Element mit demselben Basename registriert wurde — unabhängig davon, ob dieser kurze Key jemals in einem Template tatsächlich verwendet wurde.

Die Warnung wird jetzt auf den tatsächlichen Lookup verschoben: Sie erscheint einmalig und nur dann, wenn `content_item()` oder `content_url()` mit einem mehrdeutigen Kurz-Key aufgerufen wird. Sites mit Namenskollisionen in verschiedenen Content-Abschnitten sehen keine überflüssigen Warnungen mehr für Keys, die sie nie referenzieren.

## Zwei neue Dokumentationsseiten

Das Demo-Theme hat jetzt Seiten für **Theme-Entwicklung** und **Sicherheit**, verfügbar in allen vier Sprachversionen.

**[Theme-Entwicklung](/de/theme-entwicklung/)** behandelt das vollständige Template-Schichten-Modell — wie `local/templates/`, registrierte Theme-Pfade und das Bare-Core nach Priorität aufgelöst werden — sowie eine Referenz für Template-Kontext-Verträge, Twig-Globals, Funktionen und Filter, die ContentItem-API, erforderliche Übersetzungsschlüssel und die sechs Portabilitätsregeln, die jedes Theme befolgen sollte.

**[Sicherheit](/de/sicherheit/)** erklärt das Bedrohungsmodell, das die Sicherheitsentscheidungen von notACMS prägt: keine Datenbank, kein dynamisches Rendering, eine Build-Pipeline, die niemals Content ausführt. Die Seite behandelt die eingebauten Schutzmaßnahmen (Turnstile-Hostname-Validierung, Path-Traversal-Schutz, strikte Typen), das bewusste CSRF-Design beim Kontaktformular und die Deployment-Best-Practices für eine Produktionsinstanz.

Beide Seiten erscheinen im Dokumentations-Dropdown der Navigation und in der Seitenleiste.

## Vollständiger Changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#121---2026-06-13)
