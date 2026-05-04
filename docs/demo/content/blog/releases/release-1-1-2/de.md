---
title: "notACMS 1.1.2 — Kanonische URLs und ein Builder für strukturierte Daten"
slug: "beitraege/release-1-1-2"
description: "1.1.2 leitet URLs mit Standardlokalisierungs-Präfix auf ihre kanonische Form ohne Präfix um, ersetzt eingebettetes JSON-LD durch einen flüssigen Schema.org-Builder und liefert zwei Sicherheitsfixes."
date: 2026-05-04
category: releases
tags: [release, announcement]
template: blog/post
---

## Eine kanonische URL pro Seite

Wenn die Standardlokalisierung deiner Site `en` ist, waren sowohl `/en/blog/` als auch `/blog/` erreichbar und lieferten denselben Inhalt aus. Das sind zwei indexierbare URLs für eine Seite — und Suchmaschinen wählen die, die ihnen gefällt, was selten die ist, die du dir wünschst.

1.1.2 ergänzt den `DefaultLocaleRedirectListener`. Jede Anfrage an `/<standardlokalisierung>/...` liefert eine `301`-Weiterleitung zur URL ohne Präfix:

```
GET /en/blog/         →  301 Location: /blog/
GET /en/about/?ref=x  →  301 Location: /about/?ref=x
GET /pl/blog/         →  200 (nicht-Standardlokalisierung, unangetastet)
```

Der Listener läuft vor dem Symfony-Router, sodass die Weiterleitung erfolgt, bevor das Framework überhaupt versucht, eine Route zu treffen — keine verschwendete Arbeit, kein 404-Rauschen in den Logs. Nicht-Standardlokalisierungen (`pl`, `de`, `fr` auf dieser Site) bleiben unangetastet.

Außerdem schließt das eine kleine SEO-Lücke: Backlinks, die das Lokalisierungspräfix versehentlich enthalten, konsolidieren sich nun zur kanonischen URL, statt die Domain-Autorität aufzuteilen.

## Schema.org JSON-LD als flüssiger Builder

Bis 1.1.1 baute jedes Template, das strukturierte Daten benötigte, ein PHP-Array inline ein, leitete es durch `|json_encode|raw` und packte das Ergebnis in einen `<script>`-Tag. Sechs Templates, sechs nahezu identische Blöcke — und jeder eine neue Gelegenheit, `JSON_UNESCAPED_SLASHES` zu vergessen oder `<script>false</script>` auszuliefern, falls irgendein Frontmatter-Feld ein ungültiges Byte enthielt.

1.1.2 ersetzt das alles durch einen Service und zwei Twig-Funktionen:

```twig
{% block structured_data %}{{ json_ld(structured_data().blogPosting(
    content.title(),
    site_base_url ~ content.url(),
    content.date(),
    content.htmlContent,
    site_author.name,
    content.image() ? site_base_url ~ content.image() : null
)) }}{% endblock %}
```

`structured_data()` liefert den Builder; verkette eine typisierte Methode (`webSite`, `webPage`, `blogPosting`, `collectionPage`, `contactPage`, `person`, `breadcrumbList`, `organization`, `imageObject`) und das Ergebnis ist ein einfaches Array. `json_ld(array)` kodiert es und packt es in einen `<script type="application/ld+json">`-Tag. Leere und null-Werte werden rekursiv entfernt, sodass optionale Eingaben (eine nicht gesetzte Autor-E-Mail, ein fehlendes Bild) schlicht nicht in der Ausgabe erscheinen.

Die Kodierung verwendet jetzt `JSON_THROW_ON_ERROR` — fehlerhaftes UTF-8 im Frontmatter zeigt sich als echte Exception während des Renderings, statt stillschweigend einen kaputten `<script>`-Tag auszuliefern.

Die schlichten Kern-Templates für die Seiten `contact`, `default` und `projects` geben jetzt ebenfalls Schema.org-Markup aus. Bisher hatten schlichte Bereitstellungen schlechteres SEO als jedes Anpassungsbeispiel, das wir mitliefern; diese Lücke ist geschlossen.

Die vollständige Builder-API und wie du den `structured_data`-Block in deinem eigenen Theme überschreibst, findest du im [Anpassungsleitfaden → Strukturierte Daten](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#structured-data-json-ld).

## Sicherheitsfixes

Zwei Bugs sind während des 1.1.2-Reviews aufgetaucht und vor der Veröffentlichung behoben worden:

- **Open-Redirect über die Pfad-Normalisierung.** Die Methode `Request::getPathInfo()` von Symfony fasst wiederholte Schrägstriche nicht zusammen, sodass eine Anfrage an `/<standardlokalisierung>//evil.com` andernfalls `Location: //evil.com` erzeugt hätte — eine protokollrelative Weiterleitung, der Browser cross-origin folgen. Der neue Redirect-Listener verweigert nun Weiterleitungen auf alles, was kein lokaler Pfad mit einem einzelnen führenden Schrägstrich ist.
- **XSS in Suchergebnissen.** `assets/search.js` band das `excerpt`-Feld von Pagefind ohne Escaping in innerHTML ein. Der Demo-Build war bereits korrekt; der Kern war auseinandergedriftet. Beide sind nun synchron. Die `<mark>`-Hervorhebungstags von Pagefind werden in den Suchergebnissen des Kerns nicht mehr gerendert — ein bewusster Kompromiss zugunsten der sichereren Standardausgabe.

## Ebenfalls neu in 1.1.2

- **Interner Twig-Refactor.** Die Logik für Sidebar, Breadcrumbs, Beitragslabels, og-Bilder und Blog-Filter wurde aus den Templates in dedizierte Twig-Erweiterungen ausgelagert. Templates konzentrieren sich auf das Layout; Tests können jede Helper-Funktion direkt anvisieren. Neue Anpassungsfläche: siehe [Layout-Helfer](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#layout-helpers) im Anpassungsleitfaden.
- **`O(1)`-Lookups nach Verzeichnisschlüssel** in `ContentTree` — eine kleine Performance-Verbesserung auf Sites mit Hunderten von Seiten.
- **`docs/customization/old-template/` ist nun veraltet** und wird in 1.2.0 entfernt. Es war eine einmalige Übergangshilfe für 1.0→1.1; neue Anpassungsarbeiten sollten von `custom-footer/` oder `self-hosted-fonts/` ausgehen.
- **Abhängigkeitsupdates:** Symfony 7.4.8 → 7.4.9 im gesamten Bundle, PHPStan 2.1.51 → 2.1.54, PHPUnit 13.1.7 → 13.1.8.

## Vollständige Liste der Änderungen

Alle Änderungen mit Kategorien: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#112---2026-05-04).
