# Persistent Usage Intent Closure

Date: 2026-07-08

## Summary

#422-#425 close the first persistent usage intent slice. The explicit interface usage intent can now live in contract-level shared contract metadata and flow through the shared contract manifest into no-code screen definitions.

## Accepted Scope

- Added `project_shared_contracts.usage_intent`.
- Added repository read/write support for contract-level `usage_intent`.
- Added manifest normalization for `usage_intent`.
- Updated no-code screen definitions so explicit `usage_intent` overrides derived intent.
- Preserved fallback from `no_code_role=managed-screen`, sync role, and app-persistence role.
- Kept public previews free of internal admin links.
- Kept Mtool self no-code replacement parked as a later dogfooding/replacement program.

## Boundary

This slice intentionally does not add:

- A broad visual builder.
- Public internal-setting links.
- View-variant editing UI.
- Mtool self replacement.
- A large metadata editor redesign.

## Verification Plan

Focused checks:

```text
php -l mtool/app/no_code_screen_definition.php
php -l mtool/app/shared_contract_metadata_repository_pdo.php
php -l mtool/app/shared_contract_manifest.php
git diff --check
```

Full regression:

```text
make test
Tests: 340, Assertions: 11178, Skipped: 1.
```

## Next Candidates

| Candidate | Notes |
| --- | --- |
| Usage intent edit UI | Add a small admin/operator control for explicit usage intent. |
| View variant persistence | Store selected view variants per contract/screen. |
| Traceability deep links | Turn traceability descriptors into concrete authorized admin links. |
| Public-safe explanation panel | Explain generated views to public users without exposing internal settings. |
| Mtool self no-code inventory | Parked A7; begin only after smaller usage/view layers stabilize. |
