---
title: "O projekcie"
slug: "about"
description: "notACMS — przyjazny dla AI generator stron statycznych zbudowany na Symfony. Zero bazy danych, czysty Markdown, jedno polecenie do wdrożenia."
template: page/about
menu:
  weight: 60
  label: "O projekcie"
---

## Projekt

notACMS to generator stron statycznych zbudowany na Symfony 7.4 i PHP 8.5. Treść przechowujesz w plikach Markdown z YAML, konfigurujesz locales i ustawienia w jednym pliku YAML, a jednym poleceniem otrzymujesz w pełni gotową stronę HTML — bez bazy danych, bez PHP w runtime (z wyjątkiem opcjonalnego formularza kontaktowego).

notACMS to generator, który chciałem mieć. Architektura to cienka warstwa nad Symfony. Model treści to płaskie pliki. Krok budowania to jedno polecenie.

## Filozofia

**Zero zależności w runtime.** Generator jest złożony; wynik nie jest. Zbudowana strona to katalog plików HTML. Hostuj gdzie chcesz.

**AI-friendly.** Płaskie pliki Markdown i YAML są trywialne do odczytu przez modele językowe. Możesz poprosić AI o wygenerowanie treści, tłumaczenie stron lub audyt frontmatteru — bo format to po prostu tekst.

**PHP na pierwszym miejscu.** Programiści PHP mają dekady doświadczenia z Symfony, Twigiem i Composerem. notACMS na tym opiera, nie walczy.

## Open source

notACMS jest na licencji Apache 2.0. Kod na [GitHubie](https://github.com/holas1337/notACMS) — otwarty na issues, dyskusje i pull requesty.