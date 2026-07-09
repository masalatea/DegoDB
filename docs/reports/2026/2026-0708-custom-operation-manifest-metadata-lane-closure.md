# Custom Operation Manifest Metadata Lane Closure

Date: 2026-07-08

Status: `DONE`

## Summary

#451 closes the first custom operation manifest metadata lane before adding any execution route.

The lane is intentionally metadata-first. It makes Mtool-specific operations visible, inspectable, and adapter-ready while keeping generated runtime actions disabled and non-mutating.

## Accepted Capability

- Custom operation manifest inventory defines operation identity, category, target, side-effect class, policy/auth/CSRF expectations, audit expectations, generated HTML binding expectations, adapter handoff, and explicit non-goals.
- `contract_metadata.custom_operations` is normalized into the no-code screen definition.
- Mtool Source Output review declares non-executing Review Artifact and Request Publish operations.
- Runtime preview JSON carries custom operation manifests per rendered screen.
- Generated operator action panel buttons expose stable operation bindings through `data-extension-slot-operation` and `data-extension-slot-operation-key`.
- Mtool dogfooding inspection reports custom operation keys, categories, side-effect classes, availability states, adapter handoff keys, and per-screen carry-through.

## Preserved Boundary

- No custom operation execution route is added.
- No build, publish, review-request, approval, or mutation action is wired.
- Generated operator action buttons remain disabled.
- No custom React/component execution is added.
- Manifest metadata remains the source of truth for future UI/adapter handoff work.

## Next Candidates

- Local commit stack review before any push decision.
- Add explicit unavailable-reason or policy-missing display for custom operations.
- Add React adapter operation manifest handoff.
- Add execution routes only after policy, auth, CSRF, audit, and approval boundaries are explicit.

## Verification

Latest code verification remains #450:

- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- Focused PHPUnit: `OK (8 tests, 139 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11291, Skipped: 1.`

This closure is docs-only. `git diff --check` was run for #451.
