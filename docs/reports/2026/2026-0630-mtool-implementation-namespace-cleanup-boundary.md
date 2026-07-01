# Mtool Implementation Namespace Cleanup Boundary

Status: `BOUNDARY_RECORDED`

Date: 2026-06-30

## Scope

Recorded the boundary for a possible future Mtool implementation namespace cleanup.

This is intentionally separate from generated PHP output namespace support, which is already complete. This report covers Mtool's own implementation files under `mtool/app`, `mtool/admin`, `mtool/lab`, `mtool/shared`, and `mtool/scripts`.

## Current Reading

- Composer is present for third-party dependencies, but `composer.json` does not define PSR-4 autoload for Mtool implementation code.
- Mtool implementation code currently uses explicit `require_once` include wiring and `app_*` / `app_cli_*` function prefixes.
- Generated PHP output namespace support is complete and should not be mixed with repo-wide implementation namespace changes.
- The practical implementation surface is broad:
  - PHP files under the reviewed Mtool implementation roots: 365.
  - Top-level function declarations in that surface: about 3152.
  - Top-level `require_once` / `include_once` lines in that surface: about 1238.
  - CLI-like script files with `app_cli_*` / usage helpers: about 72.
- The observed `namespace` matches in `mtool/app` are generated-output template strings, not evidence that Mtool implementation is already namespaced.

## Boundary Decision

Do not start with a repo-wide namespace migration.

The safe first implementation slice, if this cleanup is later chosen, should be one of these narrow options:

1. Add a small autoload / namespace adapter for one isolated helper cluster that has no route entrypoint and no generated-output compatibility impact.
2. Move a tiny pure-function helper group behind a namespaced wrapper while keeping existing `app_*` functions as compatibility shims.
3. Add a static audit script that classifies include/function dependencies before any runtime code moves.

Avoid these in a first slice:

- Converting `mtool/app` wholesale to namespaced classes.
- Replacing all `require_once` wiring with Composer autoload.
- Moving admin/lab route entrypoints.
- Changing generated output runtime compatibility paths.
- Combining namespace cleanup with route authorization hardening.

## Recommended Status

Keep this item parked unless there is a clear maintenance goal, such as reducing include-order fragility in one helper cluster or preparing a specific module for a class-based service boundary.

If reopened, create a scoped plan with:

- exact file cluster,
- compatibility shim policy,
- affected route / CLI entrypoints,
- test target,
- rollback strategy.

## Verification

Docs-only boundary recording. No runtime behavior changed.

Inspection commands used:

- `find mtool/app mtool/admin mtool/lab mtool/shared mtool/scripts -type f -name '*.php' | wc -l`
- `rg "^function\\s+"` over the same PHP file set
- `rg "^require_once|^include_once"` over the same PHP file set
- `rg "^namespace\\s+|^class\\s+|^final class\\s+|^abstract class\\s+"` over the same PHP file set

## Next

No immediate implementation is recommended. Future work should be a fresh scoped cleanup decision, not an automatic continuation from the completed no-code MVP.
