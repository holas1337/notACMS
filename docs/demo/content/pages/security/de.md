---
title: "Sicherheit"
description: "Das Sicherheitsmodell von notACMS — eingebaute Schutzmaßnahmen, Bedrohungsmodell und Deployment-Best-Practices."
template: page/doc
slug: sicherheit
menu:
  weight: 80
  label: "Sicherheit"
---

## Bedrohungsmodell

notACMS ist ein **statischer Website-Builder**: Die Produktion liefert vorgerenderten HTML aus `public/static/` über nginx. PHP läuft zur Build-Zeit und optional zur Laufzeit für einen Endpunkt — `POST /api/contact`. Content-Autoren sind vertrauenswürdig (sie besitzen das Deployment); der Angreifer ist der anonyme Web-Besucher, dessen Angriffsfläche nginx plus dieser einzelne Kontakt-Endpunkt ist.

Dieses Modell eliminiert ganze Kategorien von Schwachstellen by Design:

- **Keine Datenbank** — keine SQL-Injection-Angriffsfläche
- **Kein dynamisches Seitenrendering** — Seiten sind vorgebaute Dateien; nginx liefert sie direkt ohne Content-Ausführung
- **Build-Pipeline führt niemals Content aus** — Markdown wird von League CommonMark geparst; Frontmatter von Symfony YAML ohne Custom-Tags; die Bildverarbeitung ruft ImageMagick auf, mit jedem Argument shell-escaped

## Sicherheitsfunktionen

- **Cloudflare Turnstile** — Anti-Fälschungsschutz für das Kontaktformular. Der Validator prüft den `hostname`, den Cloudflare meldet, gegen deine konfigurierte `base_url` und lehnt Tokens ab, die auf fremden Domains erstellt wurden. Ein Fehler wird protokolliert, wenn Always-Pass-Testschlüssel außerhalb des Debug-Modus erkannt werden.
- **Path-Traversal-Schutz** — `MediaFileResolver` validiert, dass aufgelöste Pfade innerhalb des Content-Verzeichnisses bleiben.
- **Strikte Typen** — alle PHP-Dateien verwenden `declare(strict_types=1)` zur Verhinderung von Typkoerzions-Schwachstellen.
- **Eingabevalidierung** — das Kontaktformular verwendet Symfonys Form-Komponente mit integrierten Validierungseinschränkungen.
- **Sicherheits-Header** — nginx sendet `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` und eine konfigurierbare `Content-Security-Policy` bei jeder Antwort.
- **Debug-only Endpunkte** — Draft/Scheduled-Preview-Toggles und das Styleguide sind auf `kernel.debug` beschränkt und geben in Produktion 404 zurück.

## Kontaktformular CSRF

Das Kontaktformular hat `csrf_protection: false` in `ContactType`. Das ist absichtlich: Turnstile stellt die Anti-Fälschungseigenschaft bereit. Turnstile-Tokens sind einmalig und an die erlaubten Domains des Sitekeys gebunden, sodass ein Cross-Site-Angreifer kein gültiges Token erstellen kann. Der Endpunkt lehnt Einreichungen ohne Token hart ab, und das Formular ist inaktiv, wenn kein Sitekey konfiguriert ist — Turnstile ist effektiv obligatorisch für das Kontakt-Feature.

**Produktions-Deployments müssen die committed Always-Pass-Testschlüssel** in `.env.local` ersetzen. Operatoren, die einen anderen Anti-Fälschungsschutz wollen, können `ContactType` in `local/src/` überschreiben.

## Deployment-Best-Practices

- **Niemals `.env.local` committen** — es enthält echte Secrets und ist aus gutem Grund in der Gitignore.
- **Demo-Secrets rotieren** — die `.env`-Datei enthält Platzhalter-Turnstile-Schlüssel; ersetze sie in `.env.local` vor dem Deployment.
- **Abhängigkeiten aktuell halten** — regelmäßig `composer audit` ausführen und Pakete aktualisieren, wenn Sicherheitsadvisories veröffentlicht werden.
- **HTTPS in Produktion verwenden** — das Docker-Compose-Setup enthält Certbot für automatisches TLS; die Site nicht über einfaches HTTP ausliefern.

## Sicherheitslücke melden

Keine öffentlichen Issues für Sicherheitslücken erstellen. Privat über das [Kontaktformular](/de/kontakt/) melden.
