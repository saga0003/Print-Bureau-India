# Print Bureau GitHub-managed images

When **Appearance → Customize → Print Bureau — Brand & Contact → Prefer GitHub-managed website images** is enabled, the theme automatically looks in this folder first.

Use these exact filenames:

- `hero.webp`
- `business-cards.webp`
- `brochures.webp`
- `packaging.webp`
- `stationery.webp`
- `books-catalogs.webp`
- `invitations.webp`
- `stickers-labels.webp`
- `large-format.webp`
- `institutional-printing.webp`

Recommended format: WebP, 4:3 for category/product images, approximately 1400–1800 px wide, optimized for web.

## Workflow

1. Upload or replace an image in this GitHub folder using the exact filename above.
2. Commit to `main`.
3. WordPress GitHub Sync pulls the theme update automatically, or use **Appearance → GitHub Sync → Sync from GitHub Now**.
4. The new image appears automatically on the homepage/product archive/product detail page.

If a matching GitHub image does not exist, the WordPress Featured Image or Customizer hero image is used as the fallback.
