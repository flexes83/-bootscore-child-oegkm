# OEGKM Project Context

## Project Type
- Existing WordPress Bootscore child theme.
- Not a greenfield project.
- Gutenberg patterns compose page layouts.
- Custom Gutenberg blocks live in `/blocks`.
- Bootstrap 5 provides base layout/navigation behavior.
- Custom PHP, CSS, and JS extend the child theme in place.

## Working Rules
- Do not rebuild or restructure the theme.
- Do not replace the existing theme architecture.
- Do not rewrite unrelated templates, CSS, or JS.
- Work minimally and incrementally.
- Preserve WordPress, Bootscore, Bootstrap, and Gutenberg conventions.
- Existing codebase is source of truth for logic.
- Figma is source of truth for layout, spacing, and design direction.
- Do not commit directly to `main`; `main` deploys automatically.

## Relevant Structure
- `functions.php`: theme setup, asset enqueueing, menu registration, nav filters, block registration, pattern/style setup.
- `header.php`: site header, logo, Bootstrap navigation, search control, member action.
- `footer.php`: footer markup and footer navigation.
- `inc/`: helper modules such as page header rendering, events, members, and legacy/alternate enqueue or block registration helpers.
- `patterns/`: Gutenberg pattern markup for homepage and page sections.
- `blocks/`: custom block folders with `block.json`, editor scripts/styles, frontend scripts, and render callbacks where needed.
- `assets/css/`: main theme styling, fonts, and navigation refinements.
- `assets/js/`: small frontend behaviors such as header search, scroll state, sliders, accordion, and member media.

## Current Design Areas
- Header/navigation alignment, dropdowns, search, member button, sticky/fixed behavior.
- Homepage hero/title section.
- Intro copy section.
- Ziele section and related custom block behavior.
- Info cards.
- FRAX CTA.
- Footer alignment.

## Deployment Context
- GitHub to Plesk auto deployment.
- `main` branch deploys automatically, so feature work should happen on branches.
