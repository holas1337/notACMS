# Self-Hosted Fonts

Core uses system fonts by default. This example shows how to replace them with your own self-hosted WOFF2 files.

## What It Does

- Adds font preload hints to `<head>`
- Includes self-hosted WOFF2 font files for Inter and JetBrains Mono
- Uses `font-display: swap` for performance
- Provides instructions for obtaining WOFF2 files

## Files

```
templates/
└── base.html.twig          # Full replacement — adds font preloads
assets/
├── app.js                   # Imports app_local.scss
└── styles/
    ├── _fonts.scss          # @font-face declarations
    └── app_local.scss       # Imports fonts and sets font stacks
```

## How to Apply

### Step 1: Copy the template and assets

```bash
cp -r docs/customization/self-hosted-fonts/templates/* local/templates/
cp -r docs/customization/self-hosted-fonts/assets/* local/assets/
```

### Step 2: Obtain WOFF2 font files

Download Inter and JetBrains Mono WOFF2 files from:

- **Inter**: https://github.com/rsms/inter/releases
- **JetBrains Mono**: https://github.com/JetBrains/JetBrainsMono/releases

Place them in:
```
local/assets/fonts/
├── inter-400.woff2
├── inter-500.woff2
├── inter-600.woff2
├── inter-700.woff2
├── inter-800.woff2
├── jetbrains-mono-400.woff2
├── jetbrains-mono-500.woff2
├── jetbrains-mono-600.woff2
└── jetbrains-mono-700.woff2
```

### Step 3: Rebuild assets

```bash
ddev exec bin/console asset-map:compile
```

## Technical Details

Since the core `base.html.twig` uses system fonts by default, we need a full template replacement
to add `<link rel="preload">` for the font files and remove the generic system stack.

The base template:
1. Removes the built-in system fonts from CSS
2. Adds preload hints for critical font weights
3. Defines @font-face rules in SCSS
4. Uses `font-display: swap` for optimal loading

## Why Self-Host?

- **Privacy**: No requests to third-party servers
- **Performance**: Fonts can be preloaded and cached longer
- **Control**: Exact subset of weights and styles you need
- **GDPR**: Avoids potential issues with third-party fonts

## Result

Site uses self-hosted Inter and JetBrains Mono fonts instead of system fonts.
