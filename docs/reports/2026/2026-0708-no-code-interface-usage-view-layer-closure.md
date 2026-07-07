# No-Code Interface Usage And View-Layer Closure

Date: 2026-07-08

## Summary

#414-#419 close the first no-code interface usage and view-layer slice. The implementation keeps the existing database-first no-code kernel and adds derived screen-definition metadata for interface usage intent, view variants, and traceability targets.

This is intentionally not a visual builder. The generated UI remains rooted in shared contracts, managed operations, Source Output artifacts, publish candidates, current/alias routing, and outbox/review state.

## Accepted Scope

- #414 inventoried existing interface usage signals:
  - `no_code_role=managed-screen` already identifies screen-generating contracts.
  - `sync_role` and `app_persistence_role` already identify sync/App-local concerns.
  - managed operations identify action intent and submit capability.
  - Source Output, publish candidate, current revision, alias, runtime-data, and outbox repositories already provide delivery/review roots.
- #415 added a minimal derived interface usage contract to no-code screen definitions.
- #416 added first-slice view variants for existing generated screens:
  - `standard_table`
  - `detail_record`
  - `edit_form`
- #417 added traceability metadata from generated screen definitions back to:
  - shared contract
  - canonical fields
  - managed operations
  - `NO-CODE-RUNTIME` Source Output
  - publish candidate review
  - current public revision
  - public alias
  - sync outbox review
- #418 exposed usage intent, view variants, and traceability target counts in the admin/operator no-code inspection surface.
- #419 closes this layer with docs and verification.

## Boundary Decisions

- No database schema migration was added in this first slice.
- No public preview internal-setting links were exposed.
- No drag-and-drop or broad visual builder was introduced.
- The metadata is derived and generated first, so the product can prove the model before adding persistent UI editors.
- Presentation remains a layer above interface usage; it does not change the source contract.

## Verification Baseline

Focused checks:

```text
php -l mtool/app/no_code_screen_definition.php
php -l mtool/app/no_code_operator_inspection.php
php -l mtool/app/project_source_outputs_page.php
git diff --check
```

Full regression:

```text
make test
Tests: 339, Assertions: 11174, Skipped: 1.
```

## Next Candidates

The next layer can now choose one of these:

| Candidate | Notes |
| --- | --- |
| Persistent usage intent fields | Promote derived usage intent into editable metadata after the generated contract proves useful. |
| Persistent view variant metadata | Let operators choose variants per contract/screen without changing the underlying interface. |
| Traceability deep links | Turn target descriptors into concrete admin links where authorization and route boundaries are clear. |
| Public-safe explanation panel | Show public users non-sensitive reasons for the generated view without exposing internal settings. |
| External integration intent | Add a non-screen interface proof for API/import/export style contracts. |
