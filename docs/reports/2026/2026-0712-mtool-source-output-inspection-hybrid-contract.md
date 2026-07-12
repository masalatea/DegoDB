# Mtool Source Output Inspection Hybrid Contract

## Status

`DONE`

## Summary

The existing default-off MTOOL Source Output no-code inspection route now exposes a machine-readable hybrid contract.

This makes the first contained Mtool hybrid workflow explicit without expanding behavior:

- no new route;
- no generated mutation;
- no Source Output CRUD/build/publish replacement;
- no review request persistence;
- no CSRF/idempotency/Transaction Full wiring;
- no public/lab/current/alias exposure.

## Implementation

Added `app_no_code_mtool_source_output_inspection_hybrid_contract()` with:

- contract version: `no-code-mtool-source-output-inspection-hybrid-v0`;
- workflow key: `mtool_source_output_inspection`;
- route method/path/feature flag/default state/project/site;
- generated/no-code ownership;
- custom Mtool ownership;
- row adapter field list;
- selector policy;
- authority boundary;
- rollback boundary;
- excluded operations;
- verification pointers.

The inspection HTML now includes:

```html
<script type="application/json" data-mtool-no-code-hybrid-contract="true">...</script>
```

The JSON is encoded with HTML-safe flags and is intended as a stable inspection marker for tests and future browser/product checks.

## Test coverage

`NoCodeMtoolSourceOutputInspectionTest` now asserts:

- contract version and route identity;
- feature flag and default-off route boundary;
- generated ownership and custom Mtool ownership;
- exact row adapter fields;
- fail-closed selector policy;
- GET-only authority boundary;
- no POST support;
- rollback state change is `none`;
- generated POST execution is explicitly excluded;
- rendered HTML exposes the hybrid contract marker while still hiding form/execution bindings.

## Verification

- `php -l mtool/app/no_code_mtool_source_output_inspection_page.php`
- `php -l tests/Integration/NoCodeMtoolSourceOutputInspectionTest.php`
- `git diff --check`
- `make test`
  - `467 tests`
  - `14,214 assertions`
  - `1 skipped`

## Next

#798 should close this contract slice and decide the next small step:

- browser evidence for the new contract marker;
- entry-point hardening around the existing default-off route;
- or another small productization step.

Mutation or broad Source Output replacement remains out of scope unless a separate authority/mutation preflight is created.
