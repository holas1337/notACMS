---
name: translate-content
description: Translate existing content (pages, posts, indices) to other locales. Use when the user asks to "translate", "add translation", "translate this to", or similar. Supports path, URL, title, or "this" as input.
allowed-tools: Read, Glob, Grep, Bash, Write, Edit
---

# Skill: Translate Content to Other Locales

## Description

Translate existing content (pages, posts, indices) to other locales. Supports multiple input methods: locale-to-locale translation, content-by-path, content-by-URL, or referencing "this" content context.

## Usage Patterns

### Pattern 1: Translate by Locales (Batch)
Translate all content from source locale to target locale.

```
@translate-content {source-locale} {target-locale}

Example: @translate-content en es           # EN → ES (all content)
Example: @translate-content en de           # EN → DE (all content)
```

### Pattern 2: Translate by Content Path
Translate specific content file or directory.

```
@translate-content {content-path} to {target-locale}

Example: @translate-content pages/manual to es
Example: @translate-content blog/releases/release-1-1-0 to fr
Example: @translate-content pages/about to de,pl,fr        # Multiple targets
```

### Pattern 3: Translate by URL
Reference content by its URL (useful when viewing in browser).

```
@translate-content url:{full-url} to {target-locale}

Example: @translate-content url:https://site.com/manual/ to es
Example: @translate-content url:/blog/release-1-1-0/ to fr
```

### Pattern 4: Translate "This" Content
Translate the content currently being viewed or edited.

```
@translate-content this to {target-locale}
@translate-content this to remaining                      # All except source
@translate-content this to missing                        # Only locales without translation

Example: @translate-content this to es                  # Current page → ES
Example: @translate-content this to de,fr,pl            # Current page → multiple
Example: @translate-content this to remaining            # Current page → all missing locales
```

### Pattern 5: Translate by Title/Name
Find content by title and translate.

```
@translate-content "{title-fragment}" to {target-locale}

Example: @translate-content "Manual" to es
Example: @translate-content "Quick Start" to fr          # Finds by title match
Example: @translate-content "release-1-1-0" to de        # Finds by slug
```

---

## Input Resolution

The skill attempts to resolve content in this order:

1. **Exact content path** — `pages/manual`, `blog/releases/release-1-1-0`
2. **URL parsing** — Extracts path, resolves to content directory
3. **Title/slug search** — Greps frontmatter for matching title or slug
4. **Current context** — "this" resolves to file from conversation context

### URL to Content Mapping

| URL Pattern | Content Location |
|-------------|------------------|
| `/manual/` | `pages/manual/en.md` (default locale) |
| `/es/manual/` | `pages/manual/es.md` |
| `/blog/release-1-1-0/` | `blog/releases/release-1-1-0/en.md` |
| `/es/articulos/release-1-1-0/` | `blog/releases/release-1-1-0/es.md` (localized slug) |

**Resolution process:**
1. Remove domain and protocol
2. Strip locale prefix (e.g., `/es/`)
3. Match remaining path to content directory by slug or directory name
4. Locate source file for default locale

---

## Phase 1: Resolve Content

### Step 1.1: Determine Source Content

**If given content path:**
```
Input: "pages/manual"
Resolve: local/content/pages/manual/{source-locale}.md
```

**If given URL:**
```
Input: "https://site.com/es/manual/" or "/es/manual/"
Steps:
  1. Strip protocol/domain → "/es/manual/"
  2. Detect locale from URL prefix → "es"
  3. Remove locale prefix → "/manual/"
  4. Match to content directory → "pages/manual/"
  5. Locate source → "pages/manual/{default-locale}.md"
```

**If given title/slug:**
```
Input: "Manual" or "manual"
Search: Grep all {source}.md files for "title:.*Manual" or "slug: manual"
Resolve: First match found
```

**If "this":**
```
Input: "this"
Resolve: Content file from current conversation context (e.g., file being viewed)
```

### Step 1.2: Determine Target Locales

**Explicit list:**
```
"to es" → ["es"]
"to de,fr,pl" → ["de", "fr", "pl"]
```

**Keyword "remaining":**
```
All configured locales except source locale
```

**Keyword "missing":**
```
Configured locales where content file does not yet exist
```

**Validation:**
- Verify each target locale exists in `_site.yaml`
- Warn if translation file missing for target locale

---

## Phase 2: Translation Strategy

### Locale-Specific Style Guides

Each target locale has specific conventions. Follow the guide for the locale you're translating to. If a locale is not listed here, use professional, formal register and avoid anglicisms.

#### Polish (pl)

**Register:** Formal/professional. Direct but not colloquial. Address the reader in third person or impersonally — avoid second-person informal ("zrobisz to") unless the EN source uses direct "you".

**Anglicism avoidance** — never use English words when a natural Polish equivalent exists:

| Avoid (anglicism) | Use instead |
|---|---|
| "build" (as noun) | "budowanie" or "build" in backticks only when referring to the CLI command |
| "deploy" (as noun) | "wdrożenie" or "deploy" in backticks only when referring to the CLI command |
| "redeploy" | "ponowne wdrożenie" |
| "changelog" | "lista zmian" |
| "skille" | "skrypty" or "umiejętności" depending on context |
| "suite testów" | "zestaw testów" |
| "content" (as noun) | "treść" |
| "seed / seedowanie" | "seedowanie" in backticks for the CLI concept, "inicjalizacja" otherwise |
| "overridy" | "nadpisania" |
| "feature" | "funkcja" / "funkcjonalność" |

**Common literal-translation traps:**

| English source | Bad (literal) | Good (natural PL) |
|---|---|---|
| "X lives in `dir/`" | "X żyje w `dir/`" | "X znajduje się w `dir/`" |
| "ships with X" | "wysyła z X" | "zawiera X" / "dostarczane z X" |
| "the lot" | "wszystko razem" | "całość" / "kompletny zestaw" |
| "the lot" (in list) | "wszystko" | "i więcej" / "oraz inne" |
| "Also in X.Y.Z" | "Ponadto w X.Y.Z" | "Co jeszcze w X.Y.Z" |
| "Full changelog" | "Pełny changelog" | "Pełna lista zmian" |
| "if missing" | "jeśli brakuje" | "jeśli nie istnieje" |
| "Polish content" | "Polska treść" | "Treść w języku polskim" |
| "personalisation lives in" | "personalizacje żyją w" | "personalizacje znajdują się w" |
| "pull request" | "pull request" | keep in backticks for technical term |

**Punctuation and formatting:**
- Use Polish quotation marks „…" (low-high) for quoted speech, not English "…" or guillemets «…»
- Use em-dash (—) with spaces, not en-dash (-)
- Comma before "jeśli", "że", "który" when connecting clauses
- Keep technical terms in backticks: `--prod`, `local/`, `ddev build`

**Section headers:**
- Match the tone of the EN original but use natural PL phrasing
- "The bug" → "Błąd" (punchy is OK)
- "The fix" → "Naprawa"
- "Full changelog" → "Pełna lista zmian"
- "Also in X.Y.Z" → "Co jeszcze w X.Y.Z"

#### German (de)

**Register:** Professional but conversational (Du-form is standard in German tech writing). Use "du" directly — do not switch to "Sie" mid-text.

**Anglicism / Denglisch avoidance** — German tech writing is particularly prone to Denglisch. Prefer native German terms:

| Avoid (Denglisch) | Use instead |
|---|---|
| "Content" (as noun) | "Inhalte" or "Inhalt" |
| "deployen" (verb) | "bereitstellen" or "deploy" in backticks only for the CLI command |
| "der Build" (noun) | "der Build" in backticks for CLI, "die Erstellung" / "der Build-Vorgang" otherwise |
| "der Deploy" (noun) | "die Bereitstellung" or "deploy" in backticks for CLI |
| "tweaken" | "anpassen" / "feinjustieren" |
| "Changelog" (bare) | "Liste der Änderungen" / "Änderungsprotokoll" |
| "Excerpts" | "Auszüge" / "Kurztexte" |
| "Test-Suite-Gerüst" | "Gerüst der Test-Suite" / "Test-Infrastruktur" |
| "Skills" (AI agents) | "Fähigkeiten" / "Skripte" |

**Common literal-translation traps:**

| English source | Bad (literal/Denglisch) | Good (natural DE) |
|---|---|---|
| "X lives in `dir/`" | "X lebt unter `dir/`" | "X befindet sich unter `dir/`" |
| "customisations live in" | "Anpassungen leben im" | "Anpassungen befinden sich im" |
| "Full changelog" | "Vollständiges Changelog" | "Vollständige Liste der Änderungen" |
| "Content update" | "Content-Update" | "Inhaltsaktualisierung" |
| "Content structure" | "Content-Struktur" | "Inhaltsstruktur" |
| "Also in X.Y.Z" | "Ebenfalls in X.Y.Z" | "Ebenfalls neu in X.Y.Z" |
| "test suite scaffolding" | "Test-Suite-Gerüst" | "Gerüst der Test-Suite" |

**Compound nouns:** German naturally forms compounds, so "Content-Struktur" is readable but "Inhaltsstruktur" is more native. Prefer the German compound when one exists. Keep hyphenated English terms only when they're established loanwords (Template, Design, Theme).

**Punctuation and formatting:**
- Use German quotation marks „…" (low-high), not English "…" or French «…»
- En-dash (–) for ranges, em-dash (—) for parenthetical clauses
- Commas before "dass", "wenn", "ob" in subordinate clauses
- Keep technical terms in backticks: `--prod`, `local/`, `ddev build`

**Section headers:**
- "Full changelog" → "Vollständige Liste der Änderungen"
- "Also in X.Y.Z" → "Ebenfalls neu in X.Y.Z"

#### French (fr)

**Register:** Professional standard French. Use "vous" (standard politeness). No anglicisms — French tech writing has a strong tradition of official terminology (cf. Commission d'enrichissement de la langue française).

**Anglicism avoidance** — prefer official French equivalents:

| Avoid (anglicism) | Use instead |
|---|---|
| "build" (as verb) | "construire" / "compiler"; "build" in backticks only for CLI |
| "rebuild" (bare) | "reconstruction" / "reconstruire" |
| "changelog" | "liste des modifications" / "journal des modifications" |
| "Skills" (AI agents) | "scripts" / "compétences" |
| "suite de tests" (anglicism calque) | "batterie de tests" / "ensemble de tests" |
| "curatée" | "sélectionnée" / "mise en avant" |
| "le démo" | "la démo" (feminine: la démonstration) |
| "le content" | "le contenu" |

**Common literal-translation traps:**

| English source | Bad (anglicism/calque) | Good (natural FR) |
|---|---|---|
| "X lives in `dir/`" | "X vit sous `dir/`" | "X se trouve sous `dir/`" |
| "customisations live in" | "les personnalisations vivent dans" | "les personnalisations se trouvent dans" |
| "Build the image" | "Build l'image" | "Construit l'image" |
| "Build the index" | "Build l'index" | "Construit l'index" |
| "Full changelog" | "Changelog complet" | "Liste complète des modifications" |
| "rebuild Docker" | "rebuild Docker" | "reconstruction Docker" |
| "Also in X.Y.Z" | "Également dans X.Y.Z" | "Également nouveau en X.Y.Z" |
| "test suite scaffolding" | "Ossature de suite de tests" | "Infrastructure de tests" / "Batterie de tests initiale" |

**Punctuation and formatting:**
- Use French guillemets « … » with non-breaking spaces inside, not English "…"
- Semi-colons, colons, exclamation marks, and question marks preceded by a non-breaking space
- Keep technical terms in backticks: `--prod`, `local/`, `ddev build`

**Section headers:**
- "Full changelog" → "Liste complète des modifications"
- "Also in X.Y.Z" → "Également nouveau en X.Y.Z"

### Frontmatter Decisions

**Slug strategy** (translate per locale — always):
The `slug` frontmatter field defines the URL path for each locale. **Always translate the slug** to the target locale's word for the page, not the source locale's. This produces locale-appropriate URLs like `/pl/architektura/` instead of `/pl/architecture/`.

- Translate `slug: about` → PL `slug: o-projekcie`, DE `slug: ueber-uns`, FR `slug: a-propos`
- Keep `slug` identical across locales only when the word is identical (e.g., `architecture` in EN/FR)
- `slug` is per-locale in each `.md` file — change it in the target locale's frontmatter
- `_routes.yaml` only handles structural routes (blog, search, contact), NOT page slugs

**Menu weight:**
- Must match source exactly (sort order alignment)

**Template:**
- Always keep same as source

### Content Type Detection

| File Pattern | Type | Translation Depth |
|--------------|------|-------------------|
| `*/_index_*.md` | Index/Section | Frontmatter only |
| `blog/*/index.md` | Post | Full (frontmatter + body) |
| `pages/*/index.md` | Page | Full (frontmatter + body) |
| `*/index.md` in subdir | Sub-page | Full (frontmatter + body) |

---

## Phase 3: Execute Translation

### For Each Target Locale:

1. **Create target file** at same directory as source
   ```
   Source: pages/manual/en.md
   Target: pages/manual/es.md
   ```

2. **Translate frontmatter:**
   - Copy source frontmatter
   - Translate `title`, `description`, `menu.label`
   - Apply slug strategy
   - Keep `template`, `menu.weight`, `date`, `category`, `tags`

3. **Translate body:**
   - Translate headings, paragraphs, lists
   - Keep code blocks, file paths, CSS/PHP references
   - Translate table headers and descriptions (keep field names)
   - Translate link text (keep URLs or adapt if localized slugs)

4. **Special handling:**
   - Index files: body stays empty (just `---` after frontmatter)
   - Posts: keep `date`, `category`, `tags` identical to source

---

## Phase 4: Verification

### Per File Verification

```bash
# Check target file created
[ -f "{target-path}" ] && echo "CREATED" || echo "FAILED"

# Check frontmatter has required keys
grep -q "^title:" "{target}" && echo "TITLE OK"
grep -q "^slug:" "{target}" && echo "SLUG OK"

# Check body not empty (has content after frontmatter)
awk '/^---$/{found++} found==2 && NF' "{target}" | head -1

# Verify slug is translated (not identical to source slug)
source_slug=$(grep "^slug:" "{source}" | sed 's/^slug: *//; s/"//g')
target_slug=$(grep "^slug:" "{target}" | sed 's/^slug: *//; s/"//g')
if [ "$source_slug" != "$target_slug" ] && [ -n "$target_slug" ]; then
    echo "SLUG TRANSLATED: $source_slug → $target_slug"
else
    echo "WARNING: Slug not translated (still '$target_slug'). Consider localizing."
fi
```

### Translation Map Verification

After creating translations, verify the system recognizes the link:

```bash
# Check hreflang appears on source page
curl -s {source-url} | grep 'hreflang="{target}"'

# Check target URL returns 200
curl -s -o /dev/null -w "%{http_code}" {target-url}
```

---

## Common Input Patterns

### Blog Post by URL

```
User: @translate-content url:/blog/release-1-1-0/ to fr

Resolve:
  - URL: /blog/release-1-1-0/
  - Locale: en (no prefix = default)
  - Path: blog/release-1-1-0/
  - Content: blog/releases/release-1-1-0/en.md
  - Target: blog/releases/release-1-1-0/fr.md
```

### Page by Title

```
User: @translate-content "Architecture" to es

Resolve:
  - Search: grep -r "^title:.*Architecture" local/content/pages/
  - Match: pages/architecture/en.md
  - Target: pages/architecture/es.md
```

### Current Content Context

```
User viewing: https://site.com/manual/
User says: @translate-content this to de

Resolve:
  - Context URL: /manual/
  - Content: pages/manual/en.md
  - Target: pages/manual/de.md
```

### All Missing Locales

```
User: @translate-content this to missing

Resolve:
  - Check which locales exist for this content
  - Create translations for all missing locales
  - Example: EN exists, DE exists, ES missing, FR missing
  - Result: Create ES and FR translations
```

---

## Examples

### Example 1: Translate Specific Post

```
User: @translate-content blog/releases/the-idea to es

System:
  1. Resolve: local/content/blog/releases/the-idea/en.md
  2. Target: es
  3. Create: local/content/blog/releases/the-idea/es.md
  4. Translate frontmatter:
       title: "La Idea"
       slug: "the-idea" (or "la-idea")
       description: "Cómo surgió la idea..."
  5. Translate body content
  6. Verify: /es/the-idea/ returns 200
```

### Example 2: Translate by URL to Multiple Locales

```
User: @translate-content url:/manual/ to de,fr,es

System:
  1. Parse URL: /manual/ → pages/manual/en.md
  2. Targets: [de, fr, es]
  3. Create 3 files:
       pages/manual/de.md
       pages/manual/fr.md
       pages/manual/es.md
  4. Translate each with locale-appropriate slugs (decide strategy)
  5. Verify all 3 URLs return 200
```

### Example 3: Translate This to All Missing

```
User viewing: /blog/release-1-1-0/
User: @translate-content this to missing

System:
  1. Resolve from context: blog/releases/release-1-1-0/en.md
  2. Check existing translations:
       - de.md exists
       - fr.md missing
       - es.md missing
       - pl.md exists
  3. Create: fr.md and es.md
  4. Skip: de.md, pl.md (already exist)
```

---

## Error Handling

### Content Not Found

```
Input: @translate-content "NonExistent" to es
Error: "Could not find content matching 'NonExistent'. Searched titles and slugs."
```

### URL Not Mapped

```
Input: @translate-content url:/unknown/path/ to es
Error: "Could not resolve URL '/unknown/path/' to content file. Check if path exists."
```

### Target Locale Not Configured

```
Input: @translate-content pages/manual to ja
Error: "Locale 'ja' not found in _site.yaml. Configured locales: en, de, fr, pl, es"
```

### Already Exists

```
Input: @translate-content pages/manual to es
Warning: "Translation already exists: pages/manual/es.md. Use --force to overwrite."
```

---

## Summary

This skill provides flexible content translation supporting multiple input methods:

- **Locale-to-locale**: Batch translate all content
- **Path**: Specific file or directory
- **URL**: Full or relative URL
- **Title/slug**: Find by name
- **This**: Context-aware current content
- **Keywords**: `remaining` (all except source), `missing` (only untranslated)

The system resolves content location, determines target scope, executes translation with consistent strategy, and verifies results.