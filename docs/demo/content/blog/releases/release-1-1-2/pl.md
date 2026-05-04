---
title: "notACMS 1.1.2 — Kanoniczne adresy URL i builder dla danych strukturalnych"
slug: "wpisy/release-1-1-2"
description: "1.1.2 przekierowuje adresy URL z prefiksem domyślnej lokalizacji do ich kanonicznej postaci bez prefiksu, zastępuje wbudowany JSON-LD płynnym builderem Schema.org i wprowadza dwie poprawki bezpieczeństwa."
date: 2026-05-04
category: releases
tags: [release, announcement]
template: blog/post
---

## Jeden kanoniczny adres URL na stronę

Jeśli domyślną lokalizacją witryny jest `en`, zarówno `/en/blog/`, jak i `/blog/` były dostępne i renderowały tę samą treść. To dwa indeksowalne adresy URL dla jednej strony — a wyszukiwarki wybierają ten, który im się podoba, co rzadko jest tym, którego oczekujesz.

1.1.2 dodaje `DefaultLocaleRedirectListener`. Każde żądanie do `/<domyślna-lokalizacja>/...` zwraca `301` na adres URL bez prefiksu:

```
GET /en/blog/         →  301 Location: /blog/
GET /en/about/?ref=x  →  301 Location: /about/?ref=x
GET /pl/blog/         →  200 (lokalizacja niedomyślna, nietknięta)
```

Listener uruchamia się przed routerem Symfony, więc przekierowanie następuje, zanim framework w ogóle spróbuje dopasować trasę — żadnej zmarnowanej pracy, żadnych szumów 404 w logach. Lokalizacje niedomyślne (`pl`, `de`, `fr` w tej witrynie) pozostają nietknięte.

To również eliminuje drobny wyciek SEO: linki zwrotne, które przypadkowo zawierają prefiks lokalizacji, konsolidują się teraz do kanonicznego adresu URL, zamiast dzielić autorytet domeny.

## JSON-LD Schema.org jako płynny builder

Do wersji 1.1.1 każdy szablon, który potrzebował danych strukturalnych, osadzał tablicę PHP, przepuszczał ją przez `|json_encode|raw` i opakowywał wynik w tag `<script>`. Sześć szablonów, sześć niemal identycznych bloków, z których każdy stwarzał nową okazję do zapomnienia o `JSON_UNESCAPED_SLASHES` lub do wysłania `<script>false</script>`, jeśli frontmatter zawierał nieprawidłowy bajt.

1.1.2 zastępuje to wszystko serwisem i dwiema funkcjami Twig:

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

`structured_data()` zwraca builder; wywołaj typowaną metodę (`webSite`, `webPage`, `blogPosting`, `collectionPage`, `contactPage`, `person`, `breadcrumbList`, `organization`, `imageObject`), a wynikiem jest zwykła tablica. `json_ld(array)` koduje ją i opakowuje w tag `<script type="application/ld+json">`. Puste i null-owe pola są usuwane rekursywnie, więc opcjonalne dane wejściowe (nieustawiony e-mail autora, brakujący obraz) po prostu nie pojawią się na wyjściu.

Kodowanie używa teraz `JSON_THROW_ON_ERROR` — nieprawidłowe UTF-8 we frontmatter ujawnia się jako rzeczywisty wyjątek podczas renderowania, zamiast po cichu wysyłać uszkodzony tag `<script>`.

Bazowe szablony rdzenia dla stron `contact`, `default` i `projects` również emitują teraz znaczniki Schema.org. Wcześniej wdrożenia bazowe miały słabszy SEO niż każdy przykład dostosowywania, który dostarczamy; ta luka została zamknięta.

Pełne API buildera oraz informacje o nadpisywaniu bloku `structured_data` we własnym motywie znajdziesz w przewodniku [Dostosowywanie → Dane strukturalne](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#structured-data-json-ld).

## Poprawki bezpieczeństwa

Dwa błędy ujawniły się podczas przeglądu 1.1.2 i zostały naprawione przed wydaniem:

- **Otwarte przekierowanie poprzez normalizację ścieżki.** Metoda `Request::getPathInfo()` w Symfony nie zwija powtórzonych ukośników, więc żądanie do `/<domyślna-lokalizacja>//evil.com` mogłoby w przeciwnym razie wygenerować `Location: //evil.com` — przekierowanie względne wobec protokołu, za którym przeglądarki podążają na inne źródło. Nowy listener przekierowań nie wystawia teraz przekierowań na nic, co nie jest lokalną ścieżką z pojedynczym ukośnikiem.
- **XSS w wynikach wyszukiwania.** `assets/search.js` osadzał pole `excerpt` z Pagefind w innerHTML bez escapowania. Wersja demo była już poprawna; rdzeń się rozjechał. Obie są teraz zsynchronizowane. Tagi `<mark>` z Pagefind nie są już renderowane w wynikach wyszukiwania rdzenia — świadomy kompromis na rzecz bezpieczniejszej wartości domyślnej.

## Co jeszcze w 1.1.2

- **Wewnętrzny refaktoring Twig.** Logika sidebaru, breadcrumbów, plakietek wpisów, obrazów og oraz filtrów bloga została przeniesiona z szablonów do dedykowanych rozszerzeń Twig. Szablony skupiają się na układzie; testy mogą celować bezpośrednio w każdą funkcję pomocniczą. Nowa powierzchnia dostosowywania: zobacz [Funkcje pomocnicze układu](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#layout-helpers) w przewodniku dostosowywania.
- **Wyszukiwanie po kluczu katalogu w czasie `O(1)`** w `ContentTree` — drobna optymalizacja wydajności w witrynach z setkami stron.
- **`docs/customization/old-template/` jest teraz przestarzałe** i zostanie usunięte w 1.2.0. Był to jednorazowy element pomocniczy migracji 1.0→1.1; nowe prace nad dostosowywaniem powinny opierać się na `custom-footer/` lub `self-hosted-fonts/`.
- **Aktualizacje zależności:** Symfony 7.4.8 → 7.4.9 w całym pakiecie, PHPStan 2.1.51 → 2.1.54, PHPUnit 13.1.7 → 13.1.8.

## Pełna lista zmian

Wszystkie zmiany z kategoriami: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#112---2026-05-04).
