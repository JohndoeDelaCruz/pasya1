# PASYA UI/UX redesign brief

## Product model

PASYA is a three-sided agricultural operations system:

1. Farmers plan crops, follow schedules and weather, monitor prices, and report damage or harvests.
2. LGU validators review those submissions before they become official records.
3. DA administrators maintain agricultural data and use verified records for analysis and reporting.

The product spine is:

`Farmer plan -> LGU review -> revision or verification -> DA reporting -> damage and harvest updates`

The interface must make this handoff visible. PASYA is not primarily a collection of dashboards.

## Why the previous interface felt generated

The frontend is Laravel Blade, Tailwind, and Alpine. The framework is not the design problem. The codebase lacked a shared semantic visual system, so individual pages repeatedly invented gradients, colors, radii, shadows, control styles, and motion.

At the time of the audit:

- 78 Blade views shared roughly 19,000 lines of markup.
- Large shadows, rounded surfaces, gradients, and green utilities were applied without a component contract.
- Behavior-heavy screens mixed markup, network calls, dialogs, formatting, and state in single files.
- Admin, Farmer, LGU, public, and authentication areas used different visual conventions.
- Most feature pages bypassed the existing Blade input and button components.

TypeScript would not solve those problems by itself. It may be useful later for the behavior-heavy Crop Planner, maps, and analytics modules, but visual quality starts with information hierarchy, content design, and a design system.

## Design principles

### Calm hierarchy

Use typography, spacing, grouping, and alignment before color or decoration. Operational screens should not rely on decorative gradients.

### Product-specific priority

Show what needs attention, what happens next, and which record state is authoritative. Generic counters and promotional copy are secondary.

### One visual language

- Platform-native system font stack
- Warm off-white canvas
- White working surfaces
- Near-black primary text and neutral secondary text
- PASYA green for navigation identity and primary actions
- Status colors only for status
- Borders for structure; shadows only for hierarchy and floating layers
- 44px minimum target for primary controls

### Deliberate motion

Motion explains a state change. It is not applied to every link, button, and card. Reduced-motion preferences are respected.

### Honest affordances

Unavailable actions must look unavailable. Controls must do what their labels promise. High-impact validation decisions need context and confirmation.

## Shared product language

Review status is consistent across Farmer, LGU, and DA:

| Backend value | Visible status | Decision/action language |
| --- | --- | --- |
| `pending` | Pending review | Submit for review |
| `approved` | Verified | Verify record |
| `rejected` | Needs correction | Return for correction / Revise and resubmit |

Review status is separate from crop lifecycle states such as Draft, Growing, Harvest due, and Harvested.

Data provenance must also remain explicit:

- Imported production records are administrative historical data.
- Verified farmer harvest reports are official reported actuals.
- Model output is a planning estimate, not an official total or a guaranteed outcome.

## Information architecture

### Farmer

- Home
- Crop Planner
- My Crops
- Price Watch
- Farm Map
- Help & Guidance
- Profile in the account menu

The home screen should answer:

1. What needs my attention today?
2. How are my crops progressing?
3. Does weather change today's work?
4. What should I do next?

### LGU

- Review Queue
- Review History

The queue leads with pending work, then separates Crop Plans, Damage Reports, and Harvest Reports. Every decision explains whether a submission becomes eligible for official DA reporting.

### DA administration

- Overview
- Operations: Farmer Accounts, LGU Accounts, Announcements
- Agricultural Data: Production Records, Imports, Crops & Municipalities, Market Prices
- Intelligence: Crop Trends, Map, Weather, Forecasts
- Reports: Planting Report and exports

Historical imports and live verified farmer submissions must always be labeled as different sources.

## Implemented feature slices

### Foundation and shells

- Shared visual tokens, native typography, surfaces, form controls, focus states, and reduced motion
- Restored role-specific PASYA green sidebars with clearer spacing and active states
- Shared page header, surface, status badge, and empty-state Blade components
- Clearer Farmer and Admin navigation labels
- Correct mobile header behavior and safe content offsets across all three role shells

### Farmer

- Crop Planner with labeled filters, an explicit create action, mobile-fit calendar, and a clearer plan-estimate form
- My Crops with review state, lifecycle state, next actions, and honest empty states
- Farmer Home hierarchy and working route labels

### LGU

- Task-focused review queue for crop plans, damage reports, and harvest reports
- Shared verify/return-for-correction decision treatment
- Review history that separates verified official data from records needing correction

### DA administration

- Data & Analytics source/provenance hierarchy
- Production records, import, statistics, crop trends, and crop catalog relayouts
- Planting report that defaults to verified records and labels forecasts as planning estimates
- Fixed the municipality detail panel so it remains inside its Alpine state owner

### Public and authentication

- A single clear homepage action hierarchy and an honest three-step PASYA workflow
- Removed unsupported impact/accuracy metrics, the dead newsletter form, and dead footer links
- Farmer/staff login mode selection with a restrained, mobile-first layout

## Product risks requiring policy decisions

These are not cosmetic issues:

- Registration captures municipality while an LGU validator can be barangay-scoped. Some new submissions may miss the intended queue.
- Registration and login copy must match the actual passwordless Farmer ID and OTP policy.
- Farmers can delete approved crop plans; official records need amend/cancel/audit semantics.
- Offline warnings exist, but field submissions have no draft/sync lifecycle.
- The Admin export-summary endpoint is still a placeholder; it must not be presented as a finished primary action.

## Validation plan

Measure task outcomes rather than visual preference:

- Plan completion time and completion rate
- Preview-to-submit rate
- LGU median review time and return-for-correction rate
- Farmer correction completion rate
- Damage and harvest report completion
- Valid barangay/location coverage
- Success at 360px width, low bandwidth, and intermittent connectivity

Run scenario-based sessions with 5-7 farmers, 3-5 LGU validators, and 2-3 DA staff before behavior-heavy map and JavaScript migrations.
