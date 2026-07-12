# Schema Proposal Read-Only Review UI First Implementation

Status: `FIRST_SLICE_DONE`

## Result

The Sample19 schema proposal can now be inspected through a default-off, authenticated, read-only admin route. The page validates every fixed input before rendering and exposes no mutation control.

## Route and switch

- `GET /projects/SAMPLE19/schema-proposals/sample19-article-content-model-v1`.
- Auth-required route registration.
- Existing admin/config project bootstrap.
- Exact `SAMPLE19` project and exact proposal ID allowlist.
- `MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED`, passed to web-admin by root Compose and defaulting off.

Other projects and proposal IDs do not become generic file selectors.

## Integrity gate

The loader reads only the three fixed Sample19 assets and checks, in order:

1. source/proposal/snapshot readability;
2. proposal JSON decode and validation;
3. snapshot JSON object shape and validation;
4. exact source SHA-256;
5. declared diff equality with independently derived canonical diff.

Missing/invalid/hash failures return a 500 integrity result. Declared-derived mismatch returns 409. No fallback to README text or checked-in Markdown exists.

## Read-only presentation

The valid page shows stable markers for:

- `proposal_only`;
- `apply_supported=false`;
- no mutation;
- deterministic fixture and not-AI-authored provenance;
- proposal/project/version/time;
- source filename/media type/SHA verified/root pointer/redaction;
- four entities with field/key/relationship signatures and JSON Pointer evidence;
- independently derived diff category, severity, signatures, and review note;
- blocking questions and non-blocking assumptions;
- navigation back to the Sample19 project.

The current fixture aggregates to `reviewable` with four `unchanged` rows.

## Severity contract

- unchanged: `ok`;
- add: `info`;
- change: `review_required`;
- remove/conflict: `blocking`.

Page state becomes blocking before review-required. These labels have no approval or apply semantics.

## Zero-action contract

Fast coverage proves the rendered HTML contains no:

- form;
- button;
- script;
- POST method;
- runtime execution binding.

There is no CSRF token because there is no submission surface.

## Verification

- PHP syntax and direct fixture/render checks: passed.
- Hash mismatch, unreadable fixture, and diff mismatch coverage: passed.
- Route/auth and Compose pass-through coverage: passed.
- `git diff --check`: passed.
- Full suite: 448 tests, 14,018 assertions, 1 skipped.

## Boundary

- No arbitrary upload/path selection.
- No AI call, proposal persistence, review transition, SQL, import, or apply.
- No public/lab route.
- G-L4 remains pending real authenticated browser evidence.

## Next

#765 promotes this fast contract on the real admin stack and decides G-L4 qualification.
