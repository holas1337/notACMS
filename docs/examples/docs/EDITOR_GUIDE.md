# Content Guide

This is the site-specific writing guide for this notACMS installation. It covers the categories available on this site, the approved tag list, writing voice, and image generation styles.

For the underlying content system documentation (frontmatter fields, URL structure, images, series, drafts, scheduled posts, etc.), see [`docs/EDITOR_GUIDE.md`](../EDITOR_GUIDE.md) in the repository.

---

## Available categories

There are 3 categories. Pick the one that fits best:

| Directory | EN slug | PL slug | Use for |
|---|---|---|---|
| `local/content/blog/projects/` | `projects` | `realizacje` | Completed projects — trail guides, tree databases, mapping surveys, tools built for fieldwork |
| `local/content/blog/tutorials/` | `tutorials` | `porady` | How-to guides, field identification, ecology explainers, seasonal walkthroughs |
| `local/content/blog/community/` | `community` | `spolecznosc` | Personal walks, observations, meetups, reflections, anything first-person and narrative |

---

## Tags

**Aim for 3–6 tags per post.** Prefer broad over specific. Tags must earn their place — they should help a reader find related content, not describe every detail mentioned in passing.

**Rules:**
- Use the broadest accurate tag: `ecology` not `soil` + `mycorrhizae` + `decomposition` separately
- Tag a subject only if the post is substantially about it (e.g. `fungi` on a mushroom foraging post, not on a post that mentions fungi once in passing)
- EN tags use English; PL tags translate general concepts (`trees` → `drzewa`) but keep scientific names in Latin or their standard form

**Approved tag list** (new tags can be proposed and added when genuinely needed):

`trees`, `forest`, `woodland`, `nature`, `ecology`, `seasons`, `botany`, `fungi`, `birds`, `fieldwork`, `trails`, `navigation`, `weather`, `photography`, `conservation`, `community`, `survey`, `mapping`, `foraging`, `winter`, `spring`, `autumn`

**Example — winter tree identification post:**
- Good: `trees`, `woodland`, `seasons`, `fieldwork`
- Bad: `trees`, `woodland`, `seasons`, `fieldwork`, `bark`, `buds`, `silhouette` (too specific — these are details, not tags)

---

## Writing for this site

### Who reads this site

People who enjoy being outside — walkers, amateur naturalists, foragers, and curious readers who want to learn more about the forest. They may not know scientific names, but they notice things. They value honest field notes over polished copy.

Write for someone who has been in the woods and wants to recognise what they saw.

### Voice

**Casual, specific, and grounded.** Use real place names, real species, real conditions:

| Instead of | Write |
|---|---|
| "a common woodland bird" | "a great tit, calling from the hornbeam canopy" |
| "the weather was difficult" | "two degrees, intermittent sleet, boots soaked by noon" |
| "many interesting plants" | "wood sorrel, dog's mercury, and a single patch of yellow archangel" |
| "I've been doing this for years" | "ten years of walking the same circuit in all seasons" |
| "nature is beautiful" | *(omit — show it through details instead)* |

**No generic nature writing.** Avoid: breathtaking, majestic, magical, awe-inspiring, in harmony with nature, Mother Nature, reconnecting with the wild. If it sounds like a wellness retreat brochure, cut it.

**Observational, not instructional unless asked.** A field note is not a manual. Describe what you saw and what it meant — not what the reader "should" do.

### Post titles

Good titles are concrete and promise something specific:

| Good | Bad |
|---|---|
| "Identifying trees in winter: bark, buds, and silhouette" | "Winter tree spotting tips" |
| "What the fungi on a fallen beech tells you about the forest" | "Amazing mushrooms I found" |
| "Reading deer tracks: gait patterns in soft mud" | "Signs of wildlife in the forest" |

Rules:
- Name the subject (species, season, skill) — not just the theme
- State what the reader will learn or observe, not just the topic
- No clickbait, no questions unless genuinely useful
- Keep it under 70 characters when possible (SEO)

### Descriptions (meta / listing excerpt)

One or two sentences. Appears in search results and post cards. Write it as a standalone pitch — someone who only reads this should understand why the post is worth their time.

**Good:** "A field guide to reading tree silhouettes, bark texture, and bud shapes in winter — with photos from a lowland mixed forest in central Poland."

**Bad:** "In this post I'll talk about trees in winter. It was a really interesting walk."

Rules:
- Lead with what the reader gets, not what you did
- Name the species, season, or location (good for SEO)
- No "In this post…" opener
- 120–160 characters ideal for meta description

### Post intros

First paragraph sets the scene and makes the reader commit to reading. Two to three sentences — no warm-up.

**Good:**
> The hornbeam canopy was bare but the forest floor was still green: wood sorrel, bramble, a few tufts of hart's-tongue fern. I'd walked this circuit a dozen times in winter and never looked at the bark properly. This post is about what I found when I finally stopped and did.

**Bad:**
> Hello! Today I want to share something I really enjoyed. I love going for walks in the forest and recently I had a lovely experience that I thought you'd all appreciate. Nature is so amazing!

Rules:
- No "Hello!" or greeting opener
- Set the scene or state the observation in sentence 1
- Say what the post covers in sentence 2 or 3
- No teaser cliffhangers ("you won't believe what I found")

### Body content

- Use headings to structure — readers scan before they read
- Photos are your primary evidence — describe what the photo shows if it isn't obvious
- When an identification was uncertain, say so and explain your reasoning
- Link to authoritative sources (field guides, species databases) not generic nature sites
- Keep paragraphs short (3–5 sentences max)
- Prefer numbered lists for sequences (trail stages, identification steps), bullet lists for options/notes

### EN and PL parity

Both versions must be equal in quality and detail. The Polish version is not a summary of the English one.

Rules:
- Same depth of observation and specificity in both
- Do not omit species names, locations, or field notes in the translation
- Scientific names stay unchanged in both languages
- Polish grammar note: species names follow standard Polish grammar; scientific names are indeclinable

### What makes a good post for this site

Topics that fit:
- Field notes from a specific walk: what you observed, where, in what conditions
- Species identification guides with photos and distinguishing features
- Seasonal guides (what to look for in a particular month or weather)
- Ecology notes: relationships between species, signs of disturbance, succession
- Practical skills: navigation, reading animal tracks, foraging (legal, ethical, specific)

Topics that don't fit:
- Generic "go outside, it's good for you" wellness content
- News posts or link roundups with no original observation
- Opinion pieces with no field evidence
- Content that could have been a single Instagram caption

---

## Featured image

Every post **must** have a featured image. It is used in:
- The post header
- Post cards on listings and the homepage
- `og:image` for social sharing

Without a featured image the post card renders incomplete and og:image is missing.

**Requirements:**
- 1280×720px, WebP, quality 82 (16:9)
- Place in `files/` subdirectory: `local/content/blog/{category}/{post-dir}/files/photo.webp`
- Reference in frontmatter: `image: /media/{post-dir}/photo.webp`
- Always add `image_alt:` with a descriptive visual description (what the image shows — not the post title)

If no real photo is available, generate one with Draw Things (see below).

---

## AI image generation — preferred styles

These are **preferred styles**, not requirements. Always try something new — if it works, add it here.

**Always generate 8 images in parallel** — split as:
- **4 images** from one or more preferred styles (A, B, or a mix)
- **4 images** trying something new — different subject, composition, or mood, but still fitting the content

This gives enough choice without being wasteful. The "something new" batch is how new preferred styles get discovered.

### Draw Things model selection

Two models are available. **Schnell is the default** for quick iteration; use **Klein 9B** for final quality generation.

**MCP tool limitation:** `sampler`, `shift`, `guidance_embed`, and `speed_up_with_guidance_embed` cannot be passed via the MCP API — set them in Draw Things directly.

#### FLUX.1 Schnell — `flux_1_schnell_q8p.ckpt`

**Use for:** quick iteration, prompt testing, any situation where speed matters.

```
model=flux_1_schnell_q8p.ckpt
width=1024, height=576
steps=4
cfg_scale=4.5
sampler=Euler A Trailing
guidance_embed=3.5
seed=-1 (random)
```

#### FLUX.2 Klein 9B — `flux_2_klein_9b_q8p.ckpt`

**Use for:** final image generation where quality matters.

```
model=flux_2_klein_9b_q8p.ckpt
width=1024, height=576
steps=4
cfg_scale=1.0
sampler=DDIM Trailing
shift=3
guidance_embed=3.5
speed_up_with_guidance_embed=true
seed=-1 (random)
```

| Situation | Model |
|---|---|
| Testing a prompt, quick iteration | Schnell |
| Final image, higher quality needed | Klein 9B |
| Unsure | Schnell |

After generating, aim for 1024×576, then crop/resize to 1280×720 with ImageMagick before committing to `files/`.

**After choosing a featured image:** always set `image_alt` in both EN and PL frontmatter before considering the article done.

### Style A — Atmospheric forest / natural light

Used in: seasonal walk posts, ecology notes, old-growth forest content

**Characteristics:**
- Soft, diffused natural light — overcast sky or early morning mist
- Dominant palette: greens, grey-greens, muted browns
- Accent: warm golden light filtering through a gap in the canopy or along a forest path
- Shallow depth of field — foreground detail sharp, background fades into bokeh
- Photorealistic or painterly-photorealistic rendering
- Works best for wide shots: forest interior, path through trees, canopy looking up

**Example subjects:** misty forest path with soft morning light, old beech trees in autumn, mossy forest floor with ferns and dappled shade, silver birch trunks in winter

**Sample prompt structure:**
```
[subject], atmospheric forest, soft diffused natural light, misty morning,
muted green palette, golden accent light through canopy, shallow depth of field,
photorealistic, 16:9
```

**Negative prompt:** `cartoon, bright artificial light, white background, city, buildings`

### Style B — Close-up macro / nature detail

Used in: species identification guides, fungi posts, bark/lichen/track posts

**Characteristics:**
- Extreme foreground detail — subject fills most of the frame
- Soft bokeh background in green or brown earth tones
- Natural, even light — no harsh shadows
- Subject textures emphasized: bark texture, fungal gills, leaf veins, soil, feathers
- Works well for any post where identifying a specific visual feature is the point

**Example subjects:** bracket fungus on a fallen oak, deer hoofprint in soft mud, bud close-up on a hornbeam twig in winter, lichen crust on a boulder

**Sample prompt structure:**
```
[subject] extreme close-up, macro photography, sharp detail, soft green bokeh background,
natural diffused light, nature photography, 16:9
```

**Negative prompt:** `wide shot, landscape, people, city, artificial light, illustration`

### Style C — Golden hour / seasonal mood

Used in: seasonal overview posts, community walks, conservation content

**Characteristics:**
- Warm golden or amber light — late afternoon sun, autumn colour, or dawn glow
- High contrast between lit areas and deep shadow
- Silhouette elements work well (tree line against sky, single figure on a path)
- Seasonal palette is key: spring green, summer lush, autumn gold/rust, winter blue-white
- Cinematic feel — wide, landscape-oriented compositions

**Example subjects:** autumn beech forest in golden hour, silhouette of bare oaks against a pale winter sky, single birch on a misty hilltop, wildflower meadow at forest edge at sunrise

**Sample prompt structure:**
```
[subject], golden hour light, [season] forest, warm amber glow, deep shadows,
cinematic landscape, wide composition, photorealistic, 16:9
```

**Negative prompt:** `overcast, flat light, city, tech, cartoon, white background, people indoors`
