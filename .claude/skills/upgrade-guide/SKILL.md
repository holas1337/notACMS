---
name: upgrade-guide
description: Generate an UPGRADE-X.Y.md file for a notACMS release by analysing git diff between two refs and categorising breaking changes. Use when the user asks to "write an upgrade guide", "create UPGRADE-X.Y.md", "document breaking changes", or similar.
allowed-tools: Read, Glob, Grep, Bash, Write, Edit
---

# Skill: Generate Upgrade Guide

Produce an `UPGRADE-X.Y.md` file at the project root following the format defined in `AGENTS.md`.

## Usage

```
/upgrade-guide {from-version} {to-version} [{base-ref} {head-ref}]

Examples:
  /upgrade-guide 1.1 1.2
  /upgrade-guide 1.0 1.1 main feature/redesign
```

If refs are omitted, default to `main` as base and `HEAD` as head.

---

## Step 1 — Read the format rules

Read the **Upgrade guides** section of `AGENTS.md` to confirm the current format conventions before writing anything.

---

## Step 2 — Analyse the diff

Run these commands to build a complete picture of what changed:

```bash
# Which files changed
git diff {base}..{head} --name-only

# Translation keys added/removed
git diff {base}..{head} -- translations/

# Config changes
git diff {base}..{head} -- config/

# Template changes (list only)
git diff {base}..{head} -- templates/ --stat

# SCSS changes (list only)
git diff {base}..{head} -- assets/styles/ --stat

# PHP changes (list only)
git diff {base}..{head} -- src/ --stat

# Full diff for targeted files (run as needed per area)
git diff {base}..{head} -- {file}
```

---

## Step 3 — Categorise changes

Sort findings into:

| Category | Breaking? | Typical signals |
|----------|-----------|-----------------|
| Template structure | Yes — if blocks renamed/removed | Changed Twig block names, removed `{% block %}` |
| SCSS entrypoint/variable | Yes — if names changed | File renamed, `$var` → `var(--)` |
| Translation keys | Yes — if keys removed | Lines starting with `-` in translation diff |
| Config keys / file paths | Yes — if renamed or removed | `config/` diff |
| PHP public API | Yes — if interface changed | Method signature diff in `src/` |
| Internal refactor | No | Private method / test changes only |
| Bug fix | No | Fix with no API surface change |

For each breaking change, determine:
- **Who is affected** — only users who override that specific file/key/variable
- **What they must do** — concrete rename, find-replace, or template edit
- **Before/after** — always include code blocks

---

## Step 4 — Write the file

Create `UPGRADE-X.Y.md` at the project root using this structure:

```markdown
# UPGRADE FROM `X.0` TO `X.Y`

## {Highest-impact area, e.g. "Core template redesign"}

Brief explanation of the change and its scope.

**If you want to preserve the previous behaviour**, [link to compatibility package or workaround].

---

### {Specific breaking change title}

**Breaking if you have {precise condition}.**

{Explanation}

\`\`\`{lang}
# Before
{old code}

# After
{new code}
\`\`\`

{Migration steps as numbered list if more than one action needed}

---

### {Next breaking change}

...

---

## Non-breaking changes

- **{Area}** — {one-sentence description, no action required}
- ...
```

**Content rules (from AGENTS.md):**
- Every breaking section starts with `**Breaking if you have …**`
- Removed translation keys → table with key + reason
- Added translation keys → separate table
- SCSS variable renames → before/after table
- Internal-only changes → `## Non-breaking changes` at the bottom, bullet list only
- If an `old-template` compatibility package was prepared, reference it at the very top under the first section

---

## Step 5 — Verify

After writing the file:

1. Check that every breaking change has a `**Breaking if …**` lead
2. Check that every renamed file/key/variable has a before/after code block
3. Check that non-breaking changes are not mixed into breaking sections
4. Read `CHANGELOG.md` to confirm the version numbers are correct
