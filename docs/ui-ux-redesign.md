# PASYA UI/UX redesign brief

## Product model

PASYA is a three-sided agricultural operations system:

1. Farmers plan crops, follow schedules and weather, monitor prices, and report damage or harvests.
2. LGU validators review those submissions before they become official records.
3. DA administrators maintain agricultural data and use validated records for analysis and reporting.

The product spine is:

`Farmer plan → LGU review → revision or approval → DA reporting → damage and harvest updates`

The interface should make this handoff visible. PASYA is not primarily a collection of dashboards.

## Why the previous interface felt generated

The frontend is Laravel Blade, Tailwind, and Alpine. The framework is not the design problem. The codebase had no shared semantic visual system, so individual pages repeatedly invented gradients, colors, radii, shadows, control styles, and motion.

At the time of the audit:

- 78 Blade views shared roughly 19,000 lines of markup.
- 286 large shadow utilities and 50 background gradients appeared across views.
- Large screens mixed markup, network calls, formatting, dialogs, and state in single files.
- Admin, farmer, LGU, public, and authentication areas used different visual conventions.
- Most pages bypassed the existing Blade input and button components.

TypeScript would not solve those problems. It becomes useful later for the behavior-heavy Crop Planner, maps, and analytics modules, but visual quality starts with hierarchy and a design system.

## Design principles

### Calm hierarchy

Use typography, spacing, grouping, and alignment before color or decoration. Operational screens should not use decorative gradients.

### Product-specific priority

Show what needs attention, what happens next, and which record state is authoritative. Generic counters and promotional copy come later.

### One visual language

- Platform-native system font stack
- Warm off-white canvas
- White working surfaces
- Near-black primary text and neutral secondary text
- One dark agricultural green for identity and primary actions
- Status colors only for status
- Borders for structure; shadows only for hierarchy and floating layers
- 44px minimum target for primary controls

### Deliberate motion

Motion explains a state change. It is not applied to every link, button, and card. Reduced-motion preferences are respected.

### Honest affordances

Unavailable actions must look unavailable. Controls must do what their labels promise. High-impact validation decisions need context and confirmation.

## Information architecture direction

### Farmer

- Today
- My Crops
- Crop Planner
- Market
- Map
- Help

The first screen should answer:

1. What needs my attention today?
2. How are my crops progressing?
3. Does weather change today's work?
4. What should I do next?

### LGU

Unify the queue around a review inbox. Lead with pending work, then segment Crop Plans, Damage Reports, and Harvest Reports. A future detail drawer should contain evidence, audit history, and deliberate Approve / Return for correction actions.

### DA administration

- Overview
- Operations: Farmers, LGU Validators, Announcements
- Agricultural Data: Production Records, Imports, Crop Catalog, Municipalities, Name Mappings, Prices
- Intelligence: Trends, Map, Weather, Forecasts
- Reports: Planting Report, Exports

Historical imported production and live LGU-approved farmer submissions must always be labeled as different data sources.

## Delivery roadmap

### Foundation — implemented in this pass

- Shared visual tokens and native typography
- Unified authenticated shell treatment across admin, farmer, and LGU views
- Restrained sidebar, top bar, surfaces, form controls, focus states, and motion
- Correct mobile header scroll direction
- Accessible navigation labels on shell menu controls
- Farmer home hero and summary surface refinement
- Honest disabled state for the unfinished admin export
- Labeled admin filters
- Consolidated LGU overview from nine cards to three review-oriented groups
- Rebuilt login surface with clear Farmer / DA-LGU account selection
- Removed misleading nonfunctional calendar controls from Farmer Home

### Next

1. Turn `farmers/calendar.blade.php` into a focused Crop Planner with a step-based plan form and visible LGU status timeline.
2. Convert LGU review into a unified queue and evidence drawer with confirmation or undo.
3. Reorder Farmer Home around corrections, harvest due dates, and actionable weather.
4. Reframe DA analytics around data source, freshness, decision KPIs, exceptions, and drill-downs.
5. Extract repeated controls into semantic Blade components before migrating behavior-heavy JavaScript to TypeScript modules.

## Product risks requiring a policy decision

These are not cosmetic issues and should be resolved with the product owner:

- Registration captures municipality while an LGU validator can be barangay-scoped. Some new farmer submissions may not enter the intended queue.
- Registration copy promises OTP on each login, while existing farmer IDs can currently authenticate without that flow.
- Farmers can delete approved crop plans; official records need amend/cancel/audit semantics.
- Public impact and forecast-accuracy claims need a source, methodology, or softer wording.
- Offline warnings exist, but field submissions have no draft/sync lifecycle.

## Validation plan

Measure task outcomes rather than preference:

- Plan completion time and completion rate
- Preview-to-submit rate
- LGU median review time and return-for-correction rate
- Farmer correction completion rate
- Damage and harvest report completion
- Valid barangay/location coverage
- Success at 360px width, low bandwidth, and intermittent connectivity

Run scenario-based sessions with 5–7 farmers, 3–5 LGU validators, and 2–3 DA staff before broadening the redesign to all 78 views.
