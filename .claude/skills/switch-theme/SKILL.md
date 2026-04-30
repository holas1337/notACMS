---
name: switch-theme
description: Switch the active theme/profile of a notACMS instance. Discovers available profiles and handles backup/removal of the local/ directory. Use when the user asks to "switch theme", "switch profile", "change theme", "deploy profile", "activate profile", or similar.
allowed-tools: Read, Glob, Grep, Bash, Write, Edit
---

# Skill: switch-theme

Switch the active theme/profile by symlinking `local/` to a profile directory. Profiles are discovered dynamically — three are always present (`bare`, `demo`, `old`) plus any `local-*` directories at the project root.

## Phase 1 — Discover available profiles

```bash
echo "=== Built-in profiles ==="
for p in bare demo old; do
  if [[ -d "docs/$p" ]] || [[ -d "docs/customization/$p" ]]; then
    echo "  $p"
  fi
done

echo "=== Custom profiles (local-*) ==="
for d in local-*/; do
  d="${d%/}"
  echo "  ${d#local-}  (from $d)"
done
```

If `bare` → target is `docs/bare`.
If `demo` → target is `docs/demo`.
If `old` → target is `docs/customization/old-template`.
For custom profiles named `X` → target is `local-X`.

List the profiles and ask the user which one they want.

## Phase 2 — Check and handle current local/

```bash
if [[ -L "local" ]]; then
    # Remove symlink silently
    rm "local"
elif [[ -d "local" ]]; then
    # Real directory — ask user
```

When `local/` is a real directory (not a symlink), ask:
- "`local/` is a real directory. Should I back it up to `local-bck/` or remove it entirely?"
- If backup: `mv local local-bck`
- If remove: `rm -rf local`
- If neither: abort

## Phase 3 — Create symlink and build

```bash
ln -s "$target" "local"
```

Then run `ddev build` and confirm the switch with a message showing what was activated.

## Phase 4 — Restore from backup (optional)

If the user asks to restore the previous theme:

```bash
if [[ -L "local" ]]; then rm "local"; fi
if [[ -d "local-bck" ]]; then mv "local-bck" "local"; fi
```
