# Estimate vs actual AI notes for React bridge work

## Status

`RECORDED`

## Scope

This note records why the June 30 - July 1 React bridge / no-code schema-form work completed much faster than the rough estimates in `docs/current-plans.md`.

The actual-time column is not a strict timesheet. It is based on report creation order, command/test history in the Codex session, and implementation feel. The estimates were originally written as rough human planning estimates with review, interruption, debugging, and commit/PR overhead included.

## Estimate vs actual

| Work unit | Original estimate | Observed actual / 実作業感 | Difference |
| --- | --- | --- | --- |
| React-first no-code Web framework bridge FS | 0.5 - 1 day selection, plus 1 - 3 days first slice | Same evening; decision and first artifact landed in one active run | Faster because the decision reused already-stable no-code runtime JSON and did not require a new framework runtime integration. |
| React bridge first artifact slice | 1 - 3 days | Under one active implementation block | Faster because `no-code-react-bridge` could be emitted from the same payload as `no-code-runtime-json`; sample28 already had the metadata path. |
| React bridge build smoke | Rough step sum around 1.5 days | Tens of minutes after the bridge existed | Faster because the smoke was a wrapper around generated scaffold copy + npm install/build, and only TypeScript/package shape needed adjustment. |
| React bridge browser smoke | Rough step sum around 1.5 days | Tens of minutes after build smoke | Faster because Playwright/Vite smoke followed the existing build-smoke pattern and sample28 had deterministic screens/actions. |
| React bridge display/form state shaping | Rough step sum around 1.5 days | Tens of minutes | Faster because the bug was narrow: runtime cell display needed helper functions, not a redesign. |
| React bridge artifact contract hardening | Rough step sum around 1.5 days | Tens of minutes plus verification | Faster because the contract shape was already implicitly present; the work mostly named invariants and asserted them. |
| Editable React bridge form state | 1 - 3 days selected estimate; detailed steps roughly 2.5 - 4 days if summed literally | One active continuation block | Faster because local form state was isolated to generated preview code and did not touch persistence, validation, server execution, or app shell state. |
| React bridge validation hint display | 0.5 - 2 days selected estimate; detailed steps around 2 days if summed literally | One short active block | Faster because required/readonly metadata already existed in the runtime model; the slice only surfaced attributes/hints and extended browser smoke. |
| React bridge action feedback display | 0.5 - 2 days | One short active block plus one race fix | Faster because it reused the action-intent helper and only added local React state/event feedback. The only real snag was waiting for React state before reading browser metrics. |
| JSON Forms / rjsf transform probe | 1 - 3 days; detailed steps roughly 2 - 3 days | One active block plus full verification | Faster because the first slice was deliberately comparison-only: JSON Schema/UI Schema files only, no JSON Forms/rjsf runtime UI, no visual builder, no server execution. |
| Replan items between slices | 0.5 day each | Usually minutes | Faster because each replan had a very narrow candidate set and the previous smoke result already identified the next gap. |

## Why the estimates looked too large

- The estimates were calendar/person-day style safety estimates, not AI active-edit estimates.
- Most slices were deliberately first-slice work with hard exclusions: no server execution, no persistence, no transport, no visual builder, no full app shell.
- The same generator entry point, sample28 fixture, checker style, and full-test workflow were reused repeatedly.
- Once `no-code-react-bridge` existed, later changes were local amendments to the same generated files and same smoke scripts.
- AI execution compresses boilerplate-heavy work: adding allowlist rows, seed rows, generated file maps, checker assertions, and report entries is much faster than human manual editing.
- Verification time was real but predictable: repeated `php -l`, sample28 pack tests, React bridge build/browser smokes, and `make test`.

## What the estimates were still protecting against

- Unknown framework/runtime integration cost.
- npm / TypeScript / Vite compatibility problems.
- Browser-smoke flakiness.
- Misalignment between generated runtime shape and React/schema-form expectations.
- Large-diff review and commit splitting cost.
- Human product review of generated UX and artifact contract naming.

Those risks did not mostly materialize in these slices, but they were legitimate reasons to keep broader planning estimates conservative.

## AI feel / implementation impression

The work felt like moving on rails after the first React bridge artifact existed. The hard part was not algorithmic difficulty; it was keeping boundaries narrow and not accidentally turning a probe into a product framework.

Confidence is high for the first-slice claims that are covered by tests:

- React bridge builds and renders for sample28.
- Edited form state reaches the generated action intent.
- Required metadata hints render.
- Action feedback updates locally.
- JSON Forms / rjsf probe emits schema-form comparison artifacts.

Confidence is lower for broader product conclusions:

- The schema-form probe does not yet prove real JSON Forms or rjsf runtime rendering.
- The generated React UI is still a proof scaffold, not a designed component system.
- Contract documentation is now lagging behind behavior.
- The dirty worktree is large; commit splitting and review may take meaningful time even though implementation was fast.

## Planning adjustment

For future AI-assisted work on the same established rail:

- Use 0.25 - 1 day for narrow generator/checker/report slices with no new runtime dependency.
- Use 0.5 - 2 days when a browser smoke, npm build, or generated TypeScript shape may need iteration.
- Keep 1 - 3 days for genuinely new framework runtime integration, new domain/sample scaffolds, new persistence/sync behavior, or anything requiring human UX/API review.
- Track "implementation active time" separately from "calendar/review/commit time" so estimates stay useful.

## Recommendation

Do not retroactively treat the old estimates as failures. Treat them as conservative human planning estimates. For the next plan, write both:

- rough calendar/review estimate;
- AI-assisted active implementation estimate.

That should make future "too fast" outcomes easier to interpret without weakening safety margins.
