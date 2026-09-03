# Print Bureau Premium — WordPress Theme

Premium, conversion-first WordPress theme for **Print Bureau India**.

## What is already built
- Premium dark visual system based on the approved mockups.
- One-click dark/light colour inversion with browser persistence.
- Separate logo controls for dark and light backgrounds.
- Homepage, Product archive, Product detail, Blog archive, Article, Contact and Quote experiences.
- Mobile sticky **Call / WhatsApp / Get Quote** actions.
- WordPress-editable Products and Portfolio content types.
- Featured Image controls all product/project imagery — no code edit needed to replace photos.
- Lead capture stored inside WordPress under **Leads** + email notification.
- UTM/referrer/landing-page/device capture on enquiry and quote forms.
- Optional CRM/Odoo webhook URL in the Customizer.
- Lightweight SEO defaults + Organization/WebSite/Service schema, with automatic handoff if Rank Math/Yoast/SEOPress is later installed.
- Built-in **GitHub Sync** so future theme-code updates do not require Hostinger Git or repeated ZIP uploads.
- No Elementor or page-builder dependency.

## First activation
1. Download the repository ZIP once and upload/activate it in WordPress.
2. Theme activation automatically creates **Home, Contact, Get a Quote and Blog** pages and starter product records.
3. Go to **Appearance → GitHub Sync**. Automatic sync is ON by default.
4. Go to **Appearance → Customize → Print Bureau — Brand & Contact**.
5. Add phone, WhatsApp, email, address and business hours.
6. Set the dark and light Print Bureau logo variants. The repository already contains SVG variants under `Logo/SVG/`.
7. Add a homepage hero image if desired. If left empty, the premium CSS print-composition fallback is used.
8. Go to **Products** and replace Featured Images with real Print Bureau work.
9. Go to **Portfolio** and add real completed projects.

## Automatic GitHub → WordPress sync
Hostinger Git integration is **not required**.

After the theme has been installed once, WordPress automatically checks the public `main` branch of:

`saga0003/Print-Bureau-India`

approximately every 10 minutes through WP-Cron. When a newer GitHub commit is found, the installed theme files are updated automatically.

You can also force an immediate update at:

**WordPress Admin → Appearance → GitHub Sync → Sync from GitHub Now**

The sync system:
- is restricted to the hard-coded Print Bureau repository and `main` branch;
- validates the downloaded archive as the Print Bureau Premium theme before copying it;
- creates one local safety backup before replacing theme code;
- does not delete WordPress posts, media, leads or database content;
- can be turned OFF from **Appearance → GitHub Sync** if a deployment needs to be held.

Because WP-Cron runs when WordPress receives traffic, a completely idle preview site may not update until the site receives a request. The manual **Sync from GitHub Now** button is immediate.

## Images
Use real Print Bureau photography whenever possible. For every Product/Portfolio item, click **Set featured image** in WordPress. The theme automatically uses that image across cards, product pages and visual sections.

Recommended export:
- Product card: 1400×1000 WebP, under 220 KB where practical.
- Hero/project: 1800–2200 px wide WebP, under 450 KB where practical.
- Descriptive filenames such as `brochure-printing-chikmagalur.webp`.

## Optional Hostinger Git deployment
If a future Hostinger plan includes Git deployment, the repository root is deliberately also the WordPress theme root.

Recommended deployment target:
`/public_html/wp-content/themes/print-bureau-premium/`

Do **not** deploy the repository over `/public_html/`; only use the active theme directory.

## CRM / Odoo
Go to **Appearance → Customize → Print Bureau — Brand & Contact → Optional CRM / Odoo webhook URL**. Every quote/contact submission can be POSTed as JSON to that endpoint while still being stored in WordPress Leads.

## Important
The quote page intentionally does not invent pricing. Pricing can be added later once Print Bureau's actual pricing rules are supplied.
