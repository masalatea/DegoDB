# Mtool Source Output Hybrid Replacement Preflight

## Status

`DONE`

## Selected first product slice

Add an explicit hybrid contract to the existing MTOOL Source Output no-code inspection route.

This is the first product slice after selecting the contained Mtool workflow. It should strengthen the selected surface without broadening scope.

## Why this slice

The existing route already proves the important operational basics:

- default-off;
- admin/auth protected;
- MTOOL-only;
- GET-only;
- live repository read;
- read-only generated list/detail rendering;
- no generated execution controls;
- canonical return path;
- rollback by unsetting the feature flag.

The missing product-facing piece is not another route or a mutation path. The missing piece is an explicit contract that says which parts are generated, which parts remain custom, which fields are handed off, how rollback works, and which operations are deliberately excluded.

That mirrors the Sample28 `hybrid_ownership_contract` pattern, but applies it to Mtool self-use.

## #797 implementation boundary

#797 should add a machine-readable inspection hybrid contract for the existing route.

Required contract fields:

- contract version;
- route path and feature flag;
- generated/no-code ownership;
- custom Mtool ownership;
- row adapter fields;
- selector policy;
- authority boundary;
- rollback boundary;
- excluded operations;
- verification commands or evidence labels.

Recommended exposure:

- a helper such as `app_no_code_mtool_source_output_inspection_hybrid_contract()`;
- a stable HTML marker or JSON script block in the inspection page;
- fast tests asserting the contract and confirming that exclusions remain non-executable.

## Explicit non-goals

#797 must not add:

- a new route;
- Source Output create/edit/delete/reorder/build/publish;
- generated POST execution;
- review request persistence;
- audit append;
- CSRF/idempotency/Transaction Full wiring;
- public/lab/current/alias exposure;
- replacement of the canonical Source Outputs page.

If any of those become necessary, they require a separate authority and mutation preflight.

## Verification expectation

Because #797 will touch runtime PHP and tests, it should run:

- PHP syntax checks for changed PHP files;
- focused inspection tests;
- `git diff --check`;
- full `make test` before commit.

No browser smoke is required unless #797 changes route behavior or visible browser interaction beyond adding a stable contract marker.
