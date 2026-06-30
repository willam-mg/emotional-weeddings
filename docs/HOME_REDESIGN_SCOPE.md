# Home Redesign Scope

Generated: 2026-06-30

## Purpose

This document defines the first implementation slice for the Emotional Weddings redesign.

The first slice is the Home page because it establishes the full redesign system: hero, typography, image rhythm, CTA hierarchy, story, services teaser, and footer transition.

## Prototype

Static prototype:

`exports/templates/home-redesign-v1.html`

WordPress content template:

`exports/templates/home-redesign-v1.wp.html`

Local WordPress stylesheet:

`wordpress/wp-content/mu-plugins/emotional-weddings-redesign/home-v1.css`

Apply script:

`tools/apply-home-redesign.php`

The first version has been applied to the local WordPress Home page after creating a database backup.

## Why Home First

- The current authenticated Home page has the strongest brand direction but also the most obvious first-viewport issue.
- It defines the site's editorial tone.
- It can introduce reusable components for other pages.
- It creates the primary inquiry journey.

## Current Problems To Solve

| Problem | Redesign Response |
| --- | --- |
| Blank/loading first viewport | Finished hero with image and positioning copy |
| Gallery blocks appear incomplete/dark | Curated image mosaic using real assets |
| CTA hierarchy is inconsistent | Primary CTA: Check availability; secondary CTA: View the work |
| Text can become dense | Short editorial copy blocks |
| Services feel transactional | Collections introduced as guided choices |

## Proposed Home Sections

1. Editorial hero
2. Greg/experience story
3. Selected portfolio mosaic
4. Why work with me principles
5. Collections teaser
6. Inquiry CTA
7. Footer

## Divi Implementation Notes

- Current implementation uses HTML content plus a local mu-plugin stylesheet so WordPress/Divi reliably applies the layout.
- Convert into reusable Divi sections after visual approval if the client wants fully editable Divi modules.
- Use the existing global palette: ivory, black, muted gold, warm gray.
- Keep body text narrow.
- Use fixed image aspect ratios to avoid layout shift.
- Reuse real uploaded images, but replace prototype selections with final curated images.
- Keep navigation restrained.
- Clear SpeedyCache/Divi static CSS after implementation.

## Acceptance Criteria

- First viewport has no blank loading gap.
- Hero communicates positioning in under 10 seconds.
- At least one strong image appears above the fold.
- Gallery preview displays real visible images.
- Services are introduced through value, not only price.
- Inquiry CTA appears before footer.
- Desktop and mobile layouts are both intentional.
