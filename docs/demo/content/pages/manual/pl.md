---
title: "Podręcznik"
description: "Kompletny przewodnik po instalacji, konfiguracji i wdrażaniu notACMS."
template: page/doc
slug: podrecznik
menu:
  weight: 20
  label: "Podręcznik"
---

> ## Szybki start
> 
> ```bash
> # 1. Klonowanie
> git clone https://github.com/holas1337/notACMS my-site && cd my-site
> 
> # 2. Konfiguracja produkcyjna
> echo "APP_SECRET=$(php -r 'echo bin2hex(random_bytes(32));')" >> .env.local
> echo "URL=yourdomain.com" >> .env.local
> 
> # 3. Wdrożenie
> ./notACMS deploy --prod
> ```
> 
> Strona jest dostępna pod adresem `https://yourdomain.com`. Edytuj treść w `local/content/`.

---

## Instalacja (środowisko deweloperskie)

notACMS używa [DDEV](https://ddev.readthedocs.io/) do lokalnego developmentu.

**Wymagania:** Docker Desktop (lub Orbstack na Mac), DDEV 1.22+, Git.

```bash
git clone https://github.com/holas1337/notACMS my-site
cd my-site
ddev start
ddev composer install
ddev build
```

Strona jest dostępna pod adresem `https://my-site.ddev.site`.

---

## Struktura treści

```
local/content/
├── _site.yaml          # Konfiguracja strony
├── _routes.yaml        # Nadpisywanie URL
├── _tags.yaml          # Tłumaczenia tagów
├── pages/              # Strony statyczne
│   ├── home/
│   │   ├── en.md
│   │   ├── de.md
│   │   └── pl.md
│   └── about/
│       ├── en.md
│       └── pl.md
└── blog/
    ├── _index_en.md
    └── releases/
        └── v1-0-0/
            ├── en.md
            └── pl.md
```

---

## Frontmatter strony

| Pole | Typ | Wymagane | Opis |
|---|---|---|---|
| `title` | string | Tak | Tytuł strony |
| `slug` | string | Tak | Ścieżka URL |
| `description` | string | Nie | Meta opis |
| `date` | date | Posty | Data publikacji |
| `updated` | date | Nie | Data ostatniej modyfikacji |
| `category` | string | Posty | Slug kategorii |
| `tags` | list | Nie | Lista tagów |
| `template` | string | Nie | Szablon Twig |
| `image` | string | Nie | Ścieżka do obrazka |
| `image_alt` | string | Wymagane z `image` | Tekst alt obrazka (domyślnie tytuł, jeśli pominięty) |
| `draft` | bool | Nie | Ukryj w produkcji |
| `pinned` | date lub false | Nie | Przypnij post na górę wszystkich list do tej daty (włącznie); automatycznie odpinany po jej minięciu. Tylko posty. |
| `featured` | bool | Nie | Tylko projekty, wyświetl w wyselekcjonowanej siatce |
| `dynamic` | bool | Nie | Pomiń statyczne pre-renderowanie |
| `series` | string | Nie | Klucz serii |
| `series_order` | int | Nie | Pozycja w serii |
| `toc` | bool | Nie | Pokaż spis treści |
| `related` | list | Nie | Ręczne slugi powiązanych postów |
| `menu` | object | Nie | Konfiguracja nav: `weight` (kolejność sortowania) i `label` (nadpisanie) |

> **Schematy czytelne maszynowo:** [`post.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/post.frontmatter.schema.json), [`page.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/page.frontmatter.schema.json), [`category.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/category.frontmatter.schema.json)

---

## Obrazy

Umieść obrazy w podkatalogu `files/` obok treści:

```
blog/releases/my-post/
├── en.md
└── files/
    └── featured.webp
```

Referencja w frontmatter: `image: /media/my-post/featured.webp`

> **Wskazówka:** Wszystkie obrazy muszą być WebP. Użyj ImageMagick: `ddev exec convert input.jpg -quality 82 -strip -define webp:method=6 output.webp`

---

## Komendy budowania

| Komenda | Cel |
|---------|-----|
| `ddev build` | Pełny dev build: cache + assety + strony + wyszukiwanie |
| `ddev code-check` | PHPStan + PHP CS Fixer |
| `ddev code-fix` | Auto-fix stylu kodu |
| `./notACMS deploy --prod` | Wdrożenie produkcyjne (Docker + statyczny build) |
| `./notACMS rebuild --prod` | Aktualizacja treści tylko (szybko, bez przebudowy Dockera) |

---

## Wdrożenie produkcyjne

### Wymagania

- Docker + Docker Compose
- Domena z rekordem DNS A wskazującym na serwer
- Porty 80/443 dostępne (lub niestandardowe przez `NGINX_PORT`)

### Pierwsze wdrożenie

```bash
./notACMS deploy --prod
```

Co się dzieje:
1. Seeduje `local/` z `docs/demo/` (tylko jeśli brak lub puste — istniejąca treść nigdy nie jest nadpisywana)
2. Buduje obraz Docker PHP
3. Instaluje zależności Composer (`--no-dev` w prod)
4. Kompiluje SCSS + assety
5. Generuje statyczny HTML → `public/static/`
6. Buduje indeks wyszukiwania Pagefind → `public/pagefind/`
7. Uruchamia kontener nginx

### Aktualizacje treści

Po edycji plików Markdown:

```bash
./notACMS rebuild --prod
```

Pomija przebudowę Dockera — regeneruje tylko HTML + indeks wyszukiwania (~10s vs ~2-3min).

### Komendy kontenerów

| Komenda | Cel |
|---------|-----|
| `./notACMS deploy up` | Uruchom kontenery (bez przebudowy) |
| `./notACMS deploy down` | Zatrzymaj kontenery |
| `./notACMS deploy restart` | Restart bez przebudowy |

---

## Zmienne środowiskowe

Utwórz `.env.local` (nigdy nie commitowane, wykluczony z gita):

| Zmienna | Wymagane | Ustaw przez | Cel |
|---------|----------|-------------|-----|
| `APP_SECRET` | Tak | `php -r "echo bin2hex(random_bytes(32));"` | Sekret Symfony |
| `URL` | Tak | `yourdomain.com` | Musi pasować do `base_url` w `_site.yaml` |
| `NGINX_PORT` | Nie | `8123` | Port hosta (domyślnie: 8123) |
| `RUNTIME_PHP_ENABLED` | Nie | `true`/`false` | Włącz PHP dla formularza kontaktowego |
| `MAILER_DSN` | Nie* | `smtp://user:pass@host:587` | *Wymagane jeśli formularz włączony |
| `TURNSTILE_SITE_KEY` | Nie | Klucz Cloudflare | CAPTCHA (opcjonalne) |
| `TURNSTILE_SECRET_KEY` | Nie | Sekret Cloudflare | CAPTCHA (opcjonalne) |
| `CERTRESOLVER` | Nie | `le` lub `dummy` | Resolver SSL (domyślnie: `le`) |
| `UID` / `GID` | Nie | `1000` | Dopasuj użytkownika hosta (`id`) |

---

## Rozwiązywanie problemów

### Port już w użyciu

**Błąd:** `bind: address already in use`  
**Naprawa:**
```bash
echo "NGINX_PORT=8080" >> .env.local
./notACMS deploy --prod
```

### Odmowa dostępu

**Błąd:** 403 przy plikach statycznych  
**Naprawa:**
```bash
echo "UID=$(id -u)" >> .env.local
echo "GID=$(id -g)" >> .env.local
./notACMS deploy restart
```

### Formularz kontaktowy 404

**Naprawa:** Włącz runtime PHP:
```bash
echo "RUNTIME_PHP_ENABLED=true" >> .env.local
./notACMS deploy restart
```

### Certyfikat SSL nie działa

**Błąd:** Traefik nie może pobrać certyfikatu  
**Naprawa:** Sprawdź czy porty 80/443 są otwarte, DNS się rozwiązuje. Albo użyj `CERTRESOLVER=dummy` do testowania.

### Style się nie aktualizują

**Naprawa:** Wyczyść cache i zbuduj ponownie:
```bash
rm -rf var/ public/assets/
./notACMS deploy --prod
```

### Budowanie się zawiesza

**Naprawa:** Upewnij się, że Docker ma wystarczająco pamięci (4GB+) do kompilacji assetów.
