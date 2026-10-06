# Claude Code Handoff: durolord portfolio

Companion to `LARAVEL_REBUILD.md` (the spec; section numbers below refer to it). Use this as the working brief. Put the "Project rules" block in `C:\Users\ucgni\Herd\durolord\CLAUDE.md` so every session follows it, then run the phases in order, one session per phase, committing at the end of each.

### Project rules (copy into CLAUDE.md)
```md
# durolord (portfolio)
- Laravel + Filament v5, Livewire, Alpine, Tailwind v4, Spatie Media Library. Source of truth for scope: LARAVEL_REBUILD.md (copy it into docs/).
- Content lives in the DB and is edited in /admin. Never hard-code portfolio content in views.
- Reuse Blade components in resources/views/components; no one-off CSS. Colours/spacing come from @theme tokens in resources/css/app.css; support light/dark and all 14 palettes via CSS variables.
- Every animation must respect prefers-reduced-motion and must not cause layout shift. Keep Lighthouse >= 90 and JS budget small; lazy-load Three.js.
- Accessibility is a requirement (WCAG 2.2 AA, keyboard, focus-visible, aria-live for carousels).
- Secrets only in .env. Never commit .env, vendor, node_modules, or zips.
- Run `vendor/bin/pest`, `vendor/bin/pint`, and `npm run build` before declaring a task done; show output.
- Ask before running migrate:fresh on anything but local SQLite.
```

### Phase prompts
1. **Scaffold + data layer:** "Read docs/LARAVEL_REBUILD.md. Implement §2, §4 and §12 (migrations, models, enums, factories, seeders). Seed from `C:\Users\ucgni\Herd\duro-portfolio` (read-only) and the CV docx. Don't build UI yet."
2. **Filament admin:** "Implement §5 on Filament v5. Verify each resource by creating a project with a cover and 3 screenshots in the admin."
3. **Public pages + project browser:** "Implement §6.1-6.8 and the redirects in §3. Livewire `ProjectBrowser` with URL-synced filters."
4. **Contact + CV + SEO:** "§6.6-6.9 and §10. Add Pest tests for contact validation, honeypot, throttling and stored+queued mail."
5. **UI and animation polish** (below).
6. **Deploy:** "Follow §12b, produce a `deploy.sh`/`.cpanel.yml`, and a go-live checklist. Do not run anything against production."

### Phase 5: UI and animation polish (do after the site works)
Work page by page and take before/after screenshots (use the Playwright setup already in the old repo's `record-demo.js`, or `npx playwright screenshot`) at 375, 768 and 1440 px, in light and dark.

**A. Foundations**
- Motion tokens in CSS: `--ease-out: cubic-bezier(.22,1,.36,1)`, `--dur-fast: 150ms`, `--dur-base: 300ms`, `--dur-slow: 600ms`. All animations use them.
- Global `@media (prefers-reduced-motion: reduce)` that disables parallax, WebGL, auto-rotation and large transitions (keep opacity fades only).
- A tiny `reveal` Alpine directive (`x-reveal`) built on IntersectionObserver: fade+translate-up, stagger via `--i`, runs once. Use it everywhere instead of ad-hoc code.
- Smooth scrolling (CSS `scroll-behavior`), `scroll-margin-top` for sticky header anchors, and view transitions (`@view-transition { navigation: auto }` plus `wire:navigate`).

**B. Components**
- Header: transparent over hero, becomes frosted glass on scroll (class toggled by scroll position); sliding active-link pill; mobile menu with staggered link entrance and scroll lock.
- Buttons: press scale, gradient sheen sweep on hover, focus ring in `--accent`. Cards: hover lift, border-gradient glow, cursor-following spotlight (CSS vars `--mx/--my`), 3D tilt on project cards (max 6deg).
- Badges/chips: subtle pop-in; tech chips show icon + name.
- Skeleton loaders for Livewire states (`wire:loading`), toast with spring entrance, form fields with floating labels and inline validation animation (shake once on error).
- Theme switch: circular reveal transition from the toggle button using the View Transitions API, with fallback to a fade.

**C. Pages**
- **Home hero:** Three.js axis beam + particles (§9.3), headline word-by-word reveal, animated gradient text, scroll cue. Featured-guardian card cycles with crossfade. Stat counters animate once when visible.
- **Projects browser:** staggered grid entrance, smooth layout animation when filters change (FLIP via `@alpinejs/motion` or Livewire transitions), filter chips with animated count, empty-state illustration, infinite-scroll loader.
- **Case study:** shared-element transition from the card cover to the hero, sticky TOC with scrollspy underline, gallery masonry with lightbox (pinch/zoom, swipe, keyboard, captions), parallax on the hero image (disabled on reduced-motion), next/previous project teaser with hover reveal.
- **Guardians:** per-guardian accent glow and tinted background crossfade, inertia on drag, auto-rotate that pauses on hover/focus, thumbnail strip, grid-view toggle with staggered entrance.
- **Certifications:** group accordions with height animation, badge status pulse, lightbox for certificate images.
- **CV:** timeline line that draws as you scroll, skill bars/chips with staggered fill, tidy print stylesheet.
- **Contact:** multi-step feel (progress dots) or grouped sections, success state with confetti-lite checkmark animation.
- **404:** animated guardian illustration with a "back home" CTA.

**D. Visual quality**
- Audit spacing/typography against a 4/8px scale, a fluid type scale (`clamp()`), consistent radii/shadows, and balanced line lengths (60-75ch).
- Replace flat sections with layered backgrounds (aurora + grain + subtle grid), but keep text contrast >= 4.5:1 in every palette.
- Optimise imagery (AVIF/WebP, `sizes`, blur placeholders) and verify no CLS.

**E. Performance and accessibility guardrails**
- Animate only `transform` and `opacity`; use `will-change` sparingly; pause off-screen animations; cap WebGL DPR at 2 and 60 fps; code-split Three.js and GSAP behind dynamic `import()`.
- Lighthouse >= 90 on Home, `/projects`, a case study (mobile profile). Fix any regression before moving on.
- Keyboard-test every interactive component; add `aria-live` announcements for the carousel and filters.

**Definition of done for phase 5:** side-by-side screenshots for each page at three widths and both themes, a short motion-inventory table in `docs/motion.md` (what animates, trigger, duration, reduced-motion behaviour), Lighthouse numbers, and all tests green.

### Handoff for the HRMS repo (separate Claude Code session in `C:\Users\ucgni\Herd\hrms`)
"On branch `deploy-prep`: run the checks in §12c, finish the remaining items (screenshots via Playwright against the seeded demo, click-through per role), commit, merge to `main`, then deploy to `hrms.durolord.com` following §12c. Do not upgrade the framework in this session."
