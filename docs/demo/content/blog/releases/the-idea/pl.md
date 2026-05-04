---
title: "Dlaczego zbudowałem notACMS"
slug: "wpisy/the-idea"
description: "Geneza notACMS — dlaczego kolejny generator stron statycznych, filozofia AI-friendly i decyzja o braku bazy danych."
date: 2026-02-01
category: releases
tags: [announcement, open-source]
template: blog/post
---

> Więcej na ten temat na moim blogu: [I left WordPress](https://holas.pl/blog/why-i-left-wordpress/).

## Problem, na który wciąż natrafiałem

Każdy projekt PHP, nad którym pracowałem, w końcu potrzebował strony internetowej. Dokumentacja, landing page, blog — to, co zwykle. I za każdym razem, gdy sięgałem po narzędzie, lądowałem w tym samym miejscu: narzędzie, które chciało, żebym myślał jak ono, a nie jak programista PHP.

Hugo jest szybki, ale jego język szablonów jest obcy. Jekyll wymaga Ruby. WordPress nie nadaje się do treści statycznych. Next.js jest potężny, ale zamienia problem z publikowaniem Markdown w problem z bundlerem JavaScript.

Wciąż myślałem: znam już Symfony. Znam już Twig. Mam już Composer. Dlaczego muszę uczyć się nowego ekosystemu tylko po to, żeby opublikować tekst?

## Decyzja o braku bazy danych

Pierwsza rzecz, którą postanowiłem: żadnej bazy danych. Nie „baza danych opcjonalnie" — żadnej bazy danych w ogóle. Treść przechowywana jest w plikach. Proces budowania odczytuje pliki. Wynik to pliki.

To wymusza pewną dyscyplinę. Struktura treści musi być jawna i przewidywalna. Nie ma zapytania do bazy, po które można sięgnąć, gdy chce się znaleźć powiązane wpisy lub wygenerować mapę strony. Wszystko musi wynikać ze struktury plików.

To ograniczenie okazało się właściwym wyborem. Dzięki temu system jest łatwy do zrozumienia, prosty do archiwizacji i banalny dla narzędzi AI.

## AI-friendly z założenia

Zacząłem poważnie o tym myśleć, gdy zacząłem używać LLM-ów w codziennej pracy. Proszenie AI o pomoc przy generowaniu treści, tłumaczeniu stron czy walidacji schematów YAML jest proste, gdy format to zwykły tekst.

W przypadku CMS-a opartego na bazie danych trzeba by wyjaśniać schemat, pisać SQL, zarządzać migracjami. Z notACMS przekazujesz AI katalog plików Markdown i może ona bezpośrednio odczytywać, generować i edytować treści — bo format to po prostu tekst.

To nie jest funkcja dodana po fakcie. Dlatego właśnie system jest zaprojektowany w ten sposób.

## Fundament Symfony

Zbudowałem notACMS na Symfony 7 z tego samego powodu, dla którego używam Symfony do wszystkiego innego: jest jawny, typowany i dobrze udokumentowany. Kontener DI, komendy konsolowe, Twig — to wszystko standardowy Symfony. W infrastrukturze nie ma nic nowatorskiego.

Model treści to interesująca część. System routingu odczytuje `_routes.yaml` i generuje trasy Symfony z drzewa treści. Komenda budowania renderuje każdą trasę i zapisuje statyczny HTML. Warstwa serwisów jest lekka i wymienialna.

## Co dalej

To pierwsze wydanie to działający fundament. Rzeczy, które chcę zbudować:

- Integracja wyszukiwarki Pagefind (w v1.0.0)
- Lepszy pipeline obrazów z generowaniem WebP
- Pełniejsze wsparcie wielojęzyczne
- Właściwa strona Design Reference (tym staje się ta strona)

Repozytorium jest publiczne. Jeśli chcesz śledzić postępy lub wnieść wkład, link jest w stopce.
