---
name: generate-featured-image
description: Generate, review, and deploy a featured image for a blog post using Draw Things. Covers prompt design, parameter handling, style selection, and post-processing. Use when the user asks to "generate image", "make featured image", "need a photo for a post", "generate images", or similar.
allowed-tools: Read, Glob, Grep, Bash, Write, Edit, draw-things_get_config, draw-things_generate_image
---

# Generate Featured Image

## Before starting — read required docs

1. `local/docs/EDITOR_GUIDE.md` — **Featured image** section for image specs. **AI image generation** section for styles A/B/C, model selection, negative prompts, and sample prompt structures.
2. `docs/EDITOR_GUIDE.md` — **Images** section for `image:` and `image_alt:` frontmatter fields.

## Mandatory steps — do not skip

1. Run `draw-things_get_config`. Copy `width`, `height`, `steps`, `guidance_scale`, and `model` from the response. Use them exactly as-is in every call.
2. Generate images **one at a time** — no parallel calls.
3. **Always pass ALL parameters** explicitly. Omitting one causes the tool to default to 512×512, 20 steps, etc.
4. Create `.generated/{post-dir}/` before generating.

## Generation call template

```
draw-things_generate_image:
  prompt: "[prompt]"
  width: {from config}
  height: {from config}
  steps: {from config}
  cfg_scale: {from config}
  seed: -1
  negative_prompt: {from local/docs/EDITOR_GUIDE.md for chosen style}
  model: {from config}
  output_path: ".generated/{post-dir}/{nn}.png"
```

## Review

Show `ls -laS .generated/{post-dir}/` for the user to pick.

## Post-process

Derive the category from the post's `category:` frontmatter (e.g. `tutorials`, `projects`, `community`). Then convert via ddev:

```bash
ddev exec convert .generated/{post-dir}/{nn}.png \
  -resize 1280x720^ -gravity center -extent 1280x720 \
  -quality 82 -strip -define webp:method=6 \
  local/content/blog/{category}/{post-dir}/files/featured.webp
```

Then update `image_alt:` in both `en.md` and `pl.md`.
