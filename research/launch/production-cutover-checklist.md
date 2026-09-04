# Print Bureau India — Production Cutover Checklist

**Internal only — not deployed to WordPress.**

## Before replacing the live site
- [ ] Confirm preview homepage on desktop and mobile.
- [ ] Confirm dark/light logo variants.
- [ ] Confirm hero and all product images.
- [ ] Confirm product pages and quote buttons.
- [ ] Confirm phone, WhatsApp and email are real.
- [ ] Confirm business address and opening hours.
- [ ] Confirm quote form submission reaches the correct inbox/lead store.
- [ ] Confirm artwork upload works.
- [ ] Confirm GitHub Sync shows latest main commit.
- [ ] Backup current printbureauindia.com files and database.
- [ ] Backup email if any mailboxes are tied to the domain.
- [ ] Record existing important URLs for redirects.

## Cutover
- [ ] Put old live WordPress into maintenance mode.
- [ ] Export/migrate the approved preview WordPress into printbureauindia.com.
- [ ] Set WordPress Address and Site Address to https://printbureauindia.com if needed.
- [ ] Force HTTPS.
- [ ] Save Permalinks once.
- [ ] Clear WordPress/Hostinger/browser cache.
- [ ] Confirm GitHub Sync still works on production.

## SEO launch
- [ ] Confirm production site is indexable.
- [ ] Confirm preview/staging host remains noindex.
- [ ] Confirm canonical URLs point to printbureauindia.com.
- [ ] Confirm unique title/meta description on home and every key product page.
- [ ] Confirm Organization/Service schema contains only verified details.
- [ ] Add/verify Google Search Console property.
- [ ] Submit XML sitemap.
- [ ] Check Google Business Profile website URL.
- [ ] Add 301 redirects from useful old URLs to new equivalents.
- [ ] Run PageSpeed/Lighthouse on home, product, quote and contact pages.

## Conversion QA
- [ ] Test Call button on mobile.
- [ ] Test WhatsApp button on mobile.
- [ ] Test Get Quote from homepage.
- [ ] Test Get Quote from every product page.
- [ ] Test form validation/error states.
- [ ] Test thank-you/success state.
- [ ] Confirm lead attribution fields are captured.
- [ ] Confirm no placeholder names, numbers or fake reviews remain.
