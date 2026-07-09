# Detailed No-Code Availability Plan

Date: 2026-07-09

Status: `DONE`

## Summary

#537 expands the next no-code availability work into concrete gates. The previous `availability enablement preflight` was too broad to start safely, so it is split into inventory, gate matrix, metadata-only read model, UI preview, first availability slice, and sample UI replan. It also adds a bridge from availability to L1 sample UI no-code conversion so the roadmap does not jump straight from internal review workflow work to broad sample conversion.

## Immediate Plan

| Order | Work | Outcome |
| --- | --- | --- |
| #538 | Review workflow availability surface inventory | List current surfaces, route boundaries, guard outcomes, metadata, disabled reasons, and test gaps. |
| #539 | Review workflow availability gate matrix | Define exact available/blocked/deferred/stale/missing-CSRF/unauthorized states and required UI/audit behavior. |
| #540 | Metadata-only availability read model | Expose availability and unavailable reasons without enabling generated button execution. |
| #541 | Availability UI preview contract | Render availability state and next action explanation while mutation buttons remain disabled. |
| #542 | Review request availability first slice | Enable the narrowest availability path only after gate/read-model coverage is in place. |
| #543 | Post-availability sample UI replan | Pick the first sample UI conversion target and define measurable no-code gaps. |

## Bridge to L1 Sample UI No-Code

| Step | Work | Outcome |
| --- | --- | --- |
| B1 | Finish metadata-first review workflow availability | Availability, unavailable reason, route boundary, guard outcome, and UI explanation are visible without surprising mutation. |
| B2 | Inventory sample UI candidates | Compare sample UIs by domain shape, data access, form complexity, actions, browser smoke coverage, and expected no-code gaps. |
| B3 | Define the no-code capability checklist | Make the minimum screen/action/schema/navigation/validation/audit/browser-smoke requirements explicit before conversion starts. |
| B4 | Freeze a golden sample fixture | Pick one representative sample route with stable data and expected DOM/screenshot behavior. |
| B5 | Extract readonly no-code metadata | Render the chosen sample through no-code runtime without replacing its current hand-coded UI. |
| B6 | Add action dry-run metadata | Describe actions with route boundaries and disabled/dry-run behavior before any mutation is enabled. |
| B7 | Close the first sample conversion slice | Decide whether the first sample qualifies as L1 entry or yields a concrete no-code gap list. |

## Bridge Work Units

| Order | Work | Outcome |
| --- | --- | --- |
| #544 | L1 bridge sample UI candidate inventory | Choose the first candidate using explicit selection criteria rather than intuition. |
| #545 | L1 bridge no-code capability checklist | Define the capability floor for sample UI conversion. |
| #546 | L1 bridge golden sample fixture | Preserve the existing sample behavior as the comparison target. |
| #547 | First sample UI metadata extraction spike | Produce readonly metadata without replacing the existing UI. |
| #548 | First sample UI readonly no-code preview | Compare generated no-code rendering against the golden sample. |
| #549 | First sample UI action dry-run contract | Represent actions as safe, route-boundary-aware no-code operations. |
| #550 | First sample UI conversion closure | Mark L1 entry or record blockers before expanding to more samples. |

## Long-Term Gates

- Convert sample UIs first to expose practical no-code gaps.
- Move to Mtool self no-code only after sample conversion yields reusable screen/action/schema patterns.
- Start AI structural normalization with reviewable schema proposals before mutation.
- End goal: materials in, normalized structure plus comprehensive Q&A plus no-code UI out.

## Boundary

- This is a planning slice only.
- Availability remains parked.
- Generated button execution remains disabled.
- No route mutation, generated action execution, push, or force-push is performed.

## Verification

- `git diff --check`
