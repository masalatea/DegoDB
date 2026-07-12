# Sample29 L1 Qualification Gap Inventory

Status: `DONE_ONE_GAP`

## Decision

Sample29 is one bounded unit short of L1 qualification. The single gap is production-shaped UI execution authority for `update_support_case`.

## Checklist result

| Area | Existing evidence | Result |
| --- | --- | --- |
| Sample boundary | Stable support-case seed and explicit second-domain scope. | Pass |
| Shared schema | Key/read-only context fields plus editable subject/status/severity/next-action metadata. | Pass |
| Screen shape | Generated `support_case_list`, `support_case_detail`, and `support_case_form`. | Pass |
| Read behavior | Current/alias runtime-data rows, selection, multi-filter/search/sort/pagination, invalid numeric-operator fail-closed coverage. | Pass |
| Action contract | Keyed `update_support_case`, four required input fields, editor role, `support_case:write` scope, and submit/outbox metadata. | Pass |
| Static safety | Artifact preview has no execution binding; current/alias binding is selector-specific. | Pass |
| Authorization | Authenticated scoped stub principal and denied/disabled platform contracts. | Pass |
| Server processing | Real endpoint enqueues pending managed-operation outbox work; generated server DBAccess processing updates isolated SQLite `next_action`. | Pass |
| Failure/recovery | Shared outbox processor marks failures, exposes operator inspection, supports guarded requeue/retry, and preserves attempt/error state. | Pass through shared execution model |
| Test pyramid | Focused artifact PHPUnit plus runtime/public browser and endpoint/outbox-processing smokes. | Pass |
| UI execution authority | Browser smoke rewrites the preview action to enabled inside the test before real fetch. No server-injected Sample29 UI gate/allowlist plus live availability decision is consumed. | **Gap** |

## Why the current real fetch is insufficient

The current public runtime browser harness proves payload assembly, authenticated endpoint acceptance, outbox response handling, status/refresh UI, and generated live-row refresh. Before clicking, however, it changes the in-page action model to enabled as test setup.

That is useful endpoint/UI integration coverage but not an authority proof. A qualified executable UI slice must show that the server supplied the authority and that the browser validated live selector-bound availability before enabling the action. Test code must not manufacture the permission being tested.

## Required bounded unit

Sample29 needs one reusable authority slice:

- project/action-scoped configuration for `SAMPLE29:update_support_case`;
- default-off behavior;
- current/alias preview only, with immutable artifact identity;
- authenticated live availability including editor role and `support_case:write` scope;
- narrow allowlist for `update_support_case`;
- real POST continues to target the existing managed-operation execution endpoint and returns pending outbox state;
- static, flag-off, denied, unavailable, stale, and non-allowlisted paths issue zero POSTs;
- current and alias all-gates paths issue exactly one real or appropriately isolated POST without mutating action state in the test.

## Architecture constraint

Do not add another Sample18-style hardcoded helper or environment variable family. The next slice must define how per-project/action UI authority is configured from a reusable policy map or metadata boundary while keeping every project/action default-off.

## Estimate

The preflight is 0.5–1 day. Implementation and browser integration should be estimated after the reusable configuration boundary is fixed; likely 1–2 additional days if existing availability and execution bindings can be reused unchanged.

## Next

#750 defines that reusable authority contract, migration compatibility for Sample18, and the exact Sample29 browser matrix before code changes.
