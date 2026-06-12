---
title: "notACMS 1.2.1 — charset, mądrzejsze ostrzeżenia builda, nowe strony dokumentacji"
slug: "wpisy/release-1-2-1"
description: "1.2.1 naprawia renderowanie UTF-8 w odpowiedziach tekstowych, doprecyzowuje ostrzeżenie o niejednoznacznym kluczu katalogu i dodaje strony dokumentacji Theme Building i Security do motywu demo."
date: 2026-06-13
category: releases
tags: [release, announcement]
template: blog/post
---

## Co nowego w 1.2.1

Mała aktualizacja następująca po 1.2.0 — dwie poprawki i rozszerzona dokumentacja.

## nginx charset

Odpowiedzi tekstowe — `/llms.txt`, `/robots.txt` — były serwowane bez deklaracji zestawu znaków. Przeglądarki renderowały znaki spoza ASCII jako krzaki. Dyrektywę `charset utf-8;` ustawiono teraz na poziomie bloku serwera w `docker/nginx.conf.template`, więc każdy typ odpowiedzi automatycznie otrzymuje poprawną deklarację.

## Ostrzeżenie o niejednoznacznym kluczu katalogu

Ostrzeżenie "Ambiguous directory key" wywoływało się wcześniej w czasie budowania drzewa — w chwili rejestracji drugiego elementu treści z tym samym basename — niezależnie od tego, czy ten skrócony klucz był kiedykolwiek rzeczywiście używany w szablonie.

Teraz ostrzeżenie jest odroczone do momentu faktycznego wyszukiwania: pojawia się raz i tylko wtedy, gdy `content_item()` lub `content_url()` jest wywołane z niejednoznacznym skróconym kluczem. Strony z kolizjami nazw w różnych sekcjach treści nie zobaczą już zbędnych ostrzeżeń dla kluczy, których nigdy nie używają.

## Dwie nowe strony dokumentacji

Motyw demo ma teraz strony **Theme Building** i **Security**, dostępne we wszystkich czterech wersjach językowych.

**[Budowanie Motywu](/pl/budowanie-motywu/)** omawia pełny model warstw szablonów — jak `local/templates/`, zarejestrowane ścieżki motywu i bare core rozwiązywane są według priorytetu — oraz zawiera referencję kontraktów kontekstu szablonów, globalnych Twig, funkcji i filtrów, API ContentItem, wymaganych kluczy tłumaczeń i sześciu zasad przenośności, których powinien przestrzegać każdy motyw.

**[Bezpieczeństwo](/pl/bezpieczenstwo/)** wyjaśnia model zagrożeń, który kształtuje decyzje bezpieczeństwa notACMS: brak bazy danych, brak dynamicznego renderowania, pipeline budowania, który nigdy nie wykonuje treści. Strona opisuje wbudowane zabezpieczenia (walidacja hostname Turnstile, ochrona przed path traversal, ścisłe typy), celowe podejście do CSRF w formularzu kontaktowym oraz najlepsze praktyki wdrożeniowe.

Obie strony pojawiają się w menu nawigacyjnym Documentation i pasku bocznym.

## Pełny changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#121---2026-06-13)
