# Sample18 Enabled-Candidate Browser Smoke Preflight

Date: 2026-07-10

Status: `DONE`

## Summary

#698 defines the enabled-candidate browser smoke boundary for sample18 before any generated default-state change. The smoke should be an outer UI confirmation, not a real mutation proof.

## Boundary

The first enabled-candidate browser smoke should use a browser-side overlay or fetch stub so the public preview can observe:

- `create_task_card`, `update_task_card`, and `complete_task_card` as enabled availability candidates;
- `reopen_task_card` and `delete_task_card` still disabled or absent from executable candidates;
- route-compatible submit URL, CSRF handoff, selected row key, payload assembly, and guarded click markers;
- rendered feedback from a stubbed generated-submit response.

The smoke must not perform real DB mutation unless a later slice explicitly opts into the already guarded `enabled-real-fetch` path.

## Decision

Promote #699: `Sample18 enabled-candidate browser smoke first slice`.

The first implementation should add a separate make target / script path instead of widening the disabled-action smoke. The disabled-action smoke remains the ordinary generated preview regression guard.

## Verification

Docs-only preflight. No runtime tests were required for this planning commit.
