---
title: "Bezpieczeństwo"
description: "Model bezpieczeństwa notACMS — wbudowane zabezpieczenia, model zagrożeń i najlepsze praktyki wdrożeniowe."
template: page/doc
slug: bezpieczenstwo
menu:
  weight: 80
  label: "Bezpieczeństwo"
---

## Model Zagrożeń

notACMS to **generator statycznych stron**: produkcja serwuje wstępnie wyrenderowany HTML z `public/static/` przez nginx. PHP działa w czasie budowania i, opcjonalnie, w czasie wykonania dla jednego endpointu — `POST /api/contact`. Autorzy treści są zaufani (posiadają wdrożenie); przeciwnik to anonimowy użytkownik sieci, którego powierzchnia ataku to nginx i ten jeden endpoint kontaktowy.

Ten model eliminuje całe kategorie podatności przez projekt:

- **Brak bazy danych** — brak powierzchni ataków SQL injection
- **Brak dynamicznego renderowania stron** — strony to wstępnie zbudowane pliki; nginx serwuje je bezpośrednio bez wykonywania treści
- **Pipeline budowania nigdy nie wykonuje treści** — Markdown jest parsowany przez League CommonMark; frontmatter przez Symfony YAML bez własnych tagów; przetwarzanie obrazów wywołuje ImageMagick z każdym argumentem escaped przez shell

## Funkcje Bezpieczeństwa

- **Cloudflare Turnstile** — ochrona antyspamowa na formularzu kontaktowym. Walidator sprawdza `hostname` zgłaszany przez Cloudflare względem skonfigurowanego `base_url`, odrzucając tokeny wygenerowane na obcych domenach. Błąd jest logowany gdy klucze testowe always-pass są wykryte poza trybem debug.
- **Ochrona przed path traversal** — `MediaFileResolver` sprawdza czy rozwiązane ścieżki pozostają w katalogu treści.
- **Ścisłe typy** — wszystkie pliki PHP używają `declare(strict_types=1)` zapobiegając podatnościom na wymuszanie typów.
- **Walidacja danych wejściowych** — formularz kontaktowy używa komponentu Form Symfony z wbudowanymi ograniczeniami walidacji.
- **Nagłówki bezpieczeństwa** — nginx wysyła `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` i konfigurowalną `Content-Security-Policy` przy każdej odpowiedzi.
- **Endpointy tylko dla trybu debug** — przełączniki podglądu draftów/zaplanowanych wpisów i styleguide są ograniczone przez `kernel.debug` i zwracają 404 na produkcji.

## CSRF Formularza Kontaktowego

Formularz kontaktowy ma `csrf_protection: false` w `ContactType`. Jest to celowe: Turnstile zapewnia właściwość antysfałszowania. Tokeny Turnstile są jednorazowego użytku i powiązane z dozwolonymi domenami klucza strony, więc atakujący z innej domeny nie może wygenerować prawidłowego tokenu. Endpoint odrzuca zgłoszenia bez tokenu, a formularz jest nieaktywny gdy nie skonfigurowano klucza — Turnstile jest w praktyce obowiązkowy dla funkcji kontaktowej.

**Wdrożenia produkcyjne muszą zastąpić zacommitowane klucze testowe always-pass** w `.env.local`. Operatorzy chcący innej ochrony antysfałszowania mogą nadpisać `ContactType` w `local/src/`.

## Najlepsze Praktyki Wdrożeniowe

- **Nigdy nie commituj `.env.local`** — zawiera prawdziwe sekrety i jest w gitignore z dobrego powodu.
- **Obróć klucze demo** — plik `.env` zawiera zastępcze klucze Turnstile; zastąp je w `.env.local` przed wdrożeniem.
- **Utrzymuj zależności aktualne** — regularnie uruchamiaj `composer audit` i aktualizuj pakiety gdy publikowane są advisories bezpieczeństwa.
- **Używaj HTTPS na produkcji** — konfiguracja Docker Compose zawiera Certbot dla automatycznego TLS; nie serwuj strony przez zwykłe HTTP.

## Zgłaszanie Podatności

Nie otwieraj publicznych zgłoszeń dla podatności bezpieczeństwa. Zgłaszaj prywatnie przez [formularz kontaktowy](/pl/kontakt/).
