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
- No Elementor or page-builder dependency.

## First activation
1. Upload/activate this theme in WordPress.
2. Theme activation automatically creates **Home, Contact, Get a Quote and Blog** pages and starter product records.
3. Go to **Appearance → Customize → Print Bureau — Brand & Contact**.
4. Add phone, WhatsApp, email, address and business hours.
5. Set the dark and light Print Bureau logo variants. The repository already contains SVG variants under `Logo/SVG/`.
6. Add a homepage hero image if desired. If left empty, the premium CSS print-composition fallback is used.
7. Go to **Products** and replace Featured Images with real Print Bureau work.
8. Go to **Portfolio** and add real completed projects.

## Images
Use real Print Bureau photography whenever possible. For every Product/Portfolio item, click **Set featured image** in WordPress. The theme automatically uses that image across cards, product pages and visual sections.

Recommended export:
- Product card: 1400×1000 WebP, under 220 KB where practical.
- Hero/project: 1800–2200 px wide WebP, under 450 KB where practical.
- Descriptive filenames such as `brochure-printing-chikmagalur.webp`.

## GitHub → WordPress
This repo is deliberately structured so the repository root is the WordPress theme root. If Hostinger Git deployment is pointed at the active theme directory, code updates can be deployed without manually editing WordPress files.

Recommended deployment target:
`/public_html/wp-content/themes/print-bureau-premium/`

Do **not** deploy the GitHub repo directly over `/public_html/`; only point the deployment at the theme directory.

## CRM / Odoo
Go to **Appearance → Customize → Print Bureau — Brand & Contact → Optional CRM / Odoo webhook URL**. Every quote/contact submission can be POSTed as JSON to that endpoint while still being stored in WordPress Leads.

## Important
The quote page intentionally does not invent pricing. Pricing can be added later once Print Bureau's actual pricing rules are supplied.
