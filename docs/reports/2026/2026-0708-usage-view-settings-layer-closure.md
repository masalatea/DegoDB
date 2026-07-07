# Usage / View / Settings Layer Closure

Date: 2026-07-08

## Status

Done as #430.

This closes the current no-code usage / view / settings layer that started with the interface usage and view-layer planning work.

## Accepted Capability

The no-code layer now has a small but durable metadata path above generated screens:

- Derived interface usage remains available from existing role metadata.
- Explicit contract-level usage intent is persisted in `project_shared_contracts.usage_intent`.
- Contract-level view preference is persisted in `project_shared_contracts.view_variant_preference`.
- Screen definition carries usage intent, view preference, existing generated screen variants, and traceability.
- Operator/admin Source Outputs inspection shows interface profiles.
- Shared Contracts provides the first admin edit UI for usage intent and view preference.
- Interface profiles link back to related settings without exposing internal admin links in public runtime previews.

## Verification Baseline

Latest full verification:

- `git diff --check`
- `make test`

Result:

- `Tests: 342, Assertions: 11195, Skipped: 1.`

## Remaining Candidates

Good next candidates:

- Mtool no-code dogfooding probe: pick one low-risk Mtool read/review surface and generate/use it as a no-code validation target.
- Operation-specific deep links: extend traceability with DB Access source/function route context so managed operations can link to exact function detail pages.
- View preference adapter behavior: let one generated adapter consume `view_variant_preference` rather than only carrying/displaying it.
- Shared Contracts UI polish: add filtering/grouping when contract counts grow.
- Commit cleanup: this stack now spans #413-#430 and should be grouped before push.

## Boundary

This closure does not claim full Mtool self-replacement.

Mtool self no-code remains A7, but small Mtool no-code probes are explicitly valuable as dogfooding and verification work before any full replacement program.

