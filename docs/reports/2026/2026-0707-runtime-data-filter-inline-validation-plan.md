# Runtime-data filter inline validation plan

Date: 2026-07-07

## Summary

#374 chooses browser-side inline validation for generated runtime-data filter values before fetch.

The previous lane made filter value controls native typed inputs. The next useful slice is to stop obviously invalid generated filter requests locally before calling `runtime-data.json`, while keeping endpoint validation as the authoritative fail-closed contract.

## Planned Scope

- Validate only generated current/alias runtime-data filter controls.
- Validate only populated filter rows that would be included in the query.
- Use native browser validity where available.
- Add explicit checks for:
  - integer;
  - number;
  - date;
  - datetime;
  - time.
- Stop fetch and show a local runtime-data error status when validation fails.

## Preserved Boundaries

- Endpoint validation remains authoritative and fail-closed.
- URL replay and history replay remain server-validated.
- Artifact-key preview behavior remains static.
- Mutation, retry, outbox processing, and status polling remain unchanged.
- Empty filter values continue to mean "no filter row".

## Verification Target

The first implementation should run:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` can remain deferred if the code change is limited to generated browser-side filter validation and the smoke asserts that invalid values do not fetch.

## Push Status

No push was performed for #374.
