# Current Site Audit

Generated: 2026-06-30

## Executive Diagnosis

The local site is already moving toward a luxury editorial direction, but it is not yet stable enough to support a premium inquiry journey. The authenticated pages show elegant black/ivory sections, serif headlines, gold accents, and photography-led layouts. However, the public visitor currently sees a maintenance screen, the authenticated homepage has a large blank/loading area at the top, several visual modules appear incomplete or under-rendered, and the commercial/service content is too dense for a luxury buying experience.

The redesign should preserve the best current direction: emotional copy, black editorial contrast, the "quiet eye" positioning, and category pathways such as weddings, engagement, and bridal boudoir. It should remove friction: loading gaps, dense pricing cards, weak image rendering, inconsistent hierarchy, and unclear inquiry flow.

## Evidence Reviewed

| Source | Finding |
| --- | --- |
| Local public site | Public screenshots show "Pagina en construccion", likely from the maintenance plugin. |
| Authenticated Playwright screenshots | Real pages are visible after admin login and saved as `screenshots/audit-*.png`. |
| WordPress database | 7 published pages, 2 published posts, 5 FAQ entries, 1 testimonial, 1 active menu. |
| Divi data | Divi 4.27.6, Theme Builder header/footer templates, global colors, and body integration script are active. |
| Reference sites | Sophie Kaye and KT Merry both use image-led storytelling, restrained navigation, clear inquiry paths, and press/portfolio credibility. |

## Current Strengths

- The brand has a strong emotional premise: documentary/editorial wedding photography with a quiet, observant point of view.
- The homepage already includes a premium-sounding positioning block: "A quiet eye for unrepeatable moments."
- The design palette has usable luxury foundations: ivory, black, muted gold, warm gray, and deep green.
- The site has real service/package content, not placeholder copy.
- The footer has useful location and navigation signals.
- The journal has SEO-relevant local wedding content around London venues.
- The Divi Theme Builder is already used for global header/footer control.

## Current Weaknesses

| Area | Issue | Impact |
| --- | --- | --- |
| Public access | Maintenance page appears for non-authenticated users. | Public QA cannot see the actual site without login. |
| Homepage first viewport | Large blank area/loading indicator before content. | Weak first impression; premium sites must feel intentional immediately. |
| Visual rendering | Some gallery blocks appear dark/empty in authenticated screenshots. | Undermines photography-first promise. |
| Services | Pricing cards are text-heavy and visually compressed. | Luxury buyers may feel they are comparing commodity packages. |
| Contact | Form area appears minimal and visually ambiguous. | Inquiry path needs more confidence and clarity. |
| Copy density | FAQ and service details are useful but long. | Causes text fatigue and weakens editorial pacing. |
| Navigation | Menu is functional but not yet luxury-editorial. | Needs stronger hierarchy and clearer inquiry emphasis. |
| SEO | Journal exists but taxonomy and internal linking are underdeveloped. | Limits regional/venue search opportunity. |

## Page-by-Page Audit

| Page | Current Role | Audit Notes | Redesign Priority |
| --- | --- | --- | --- |
| Home | Brand introduction and journey entry. | Strong editorial intent, but starts with blank/loading area and has incomplete-looking gallery blocks. | Very high |
| About | Greg's credibility and personality. | Content exists and should become a tighter story with portrait-led rhythm. | High |
| Services | Packages and value explanation. | Critical conversion page, but pricing/cards are dense and hard to scan. | Very high |
| Gallery | Portfolio proof. | Must become the strongest image-led page; current implementation needs curation and loading reliability. | Very high |
| Journal | SEO and storytelling. | Only two posts currently published; useful London venue content exists. | Medium-high |
| FAQ | Objection handling. | Good client questions, but copy should be edited and grouped into a calmer accordion experience. | Medium |
| Contact | Inquiry conversion. | Has personal voice and contact details, but the form experience needs clearer structure and trust cues. | Very high |

## Visual Hierarchy Problems

- The first viewport is not consistently occupied by a finished hero experience.
- Page titles like "services" and "FAQ" are functional but not emotionally positioned.
- Some sections rely on very small body text against large visual areas.
- Pricing cards use many small bullets where a luxury page should lead with outcomes, reassurance, and selective detail.
- CTA hierarchy is inconsistent: "Learn More", "View Full Gallery", "Check Availability", and "Enviar" compete without a unified journey.

## Copy Density Problems

- Service features are packed into plan cards instead of staged progressively.
- FAQ answers are practical but should be edited for clarity and warmth.
- The about/story content should be broken into narrative beats: origin, approach, emotional promise, credibility, invitation.
- Some important differentiators are buried in long blocks rather than elevated as editorial statements.

## Mobile Experience Risks

Mobile screenshots still need a dedicated pass, but current desktop/tablet-like captures suggest likely issues:

- Pricing cards may stack into long, hard-to-read blocks.
- Large blank/loading regions will feel worse on mobile.
- Form fields and dark sections need contrast and spacing validation.
- Gallery layouts need stable aspect ratios and lazy-loading checks.

## SEO Observations

- Strong regional opportunities: London, Surrey, Kent, Sussex, United Kingdom, destination weddings.
- Existing posts already target London venue/micro-wedding terms.
- Journal structure is underdeveloped: only two published posts.
- Image alt text should be reviewed; current media naming appears photographer/file-code heavy.
- Service page can target "London wedding photographer", "editorial wedding photographer UK", and package-related intent without sounding transactional.

## Divi And Technical Concerns

- Divi Theme Builder is active and should be used for global header/footer consistency.
- Body integration contains custom JavaScript for sidebar and sticky behavior; this should be reviewed before layout rebuild.
- Maintenance plugin affects public QA and should be explicitly managed during development.
- Speed/cache plugins are active; cache should be cleared during design work and before QA.
- The site has local SQL dumps and logs ignored by Git, which is correct.

## Conversion Path Audit

Current path:

Home -> Gallery/About/Services -> Contact/Check Availability.

Recommended path:

Home hero -> Emotional positioning -> Selected portfolio proof -> Experience/process reassurance -> Collections overview -> Testimonial/social proof -> Inquiry CTA.

Every page should contain a restrained but clear inquiry path:

- Primary CTA: "Check Availability" or "Inquire"
- Secondary CTA: "View Gallery" or "Explore Services"
- Contact page CTA: "Tell me about your day"

## Priority Fixes

1. Remove or intentionally manage the maintenance screen during QA.
2. Fix the blank/loading first viewport.
3. Rebuild Home around a complete photography-led hero.
4. Redesign Services around value and experience before price comparison.
5. Rebuild Gallery as the strongest image proof page.
6. Improve Contact form clarity, labels, trust cues, and CTA language.
7. Create mobile-first spacing and typography rules.
8. Define SEO structure for Journal and regional landing opportunities.

## Redesign Opportunities

- Position Greg as a calm, emotionally observant editorial/documentary photographer.
- Use fewer but stronger words.
- Turn packages into "collections" with a guided buying narrative.
- Use the best images as full-width editorial moments, not small thumbnails.
- Build a press/publication module if proof exists.
- Turn testimonials into narrative proof rather than generic quotes.
- Make the Journal a venue and story library for London/UK/destination SEO.

## Acceptance Criteria For Redesign Start

- Public or authenticated QA can view the real pages reliably.
- Home has a finished first viewport with no blank loading gap.
- Every primary page has one clear emotional purpose and one clear CTA.
- Services can be understood in under 30 seconds before reading details.
- Gallery loads visible images above the fold.
- Contact page makes the next step obvious.

