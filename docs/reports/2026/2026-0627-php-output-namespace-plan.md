# 2026-06-27 PHP output namespace support plan

## Status

`COMPLETED`

This plan is about namespace support for generated PHP output. It is intentionally narrower than a repository-wide Mtool namespace migration.

## Scope

MUST for this lane:

- Add a project-level PHP namespace setting.
- Emit namespace declarations in generated PHP DataClass output.
- Emit namespace declarations in generated PHP DBAccess output.
- Apply the setting to representative tutorial samples and regenerated references.
- Keep runtime smoke checks and generated-output contract tests namespace-aware.

Later / optional for this lane:

- Apply namespace settings to Mtool self-output once generated sample output is stable.
- Namespace Mtool's own implementation files if it helps maintenance.

Non-goals for the first pass:

- Reorganizing generated files into PSR-4 directory trees.
- Renaming generated PHP files.
- Converting the whole Mtool codebase to namespaced PHP.
- Changing OpenAPI / proxy surface names unless a namespace interaction requires it.
- Forcing every sample to use a namespace.

## Implementation order

1. Output support and representative sample application.
2. All tutorial sample application.
3. Mtool self-output namespace application where it uses generated Output.
4. Broader Mtool implementation namespace cleanup, only if still useful.

## Initial technical reading

Current generated DataClass and DBAccess PHP templates do not emit a namespace declaration. Generated proxy client output already has its own namespace handling, so this lane should start with DataClass / DBAccess rather than a broad generator rewrite.

The simple version is project-level `namespace Foo\Bar;` at the top of every generated PHP output file for that project. The default should remain empty / no namespace so existing projects and beginner samples keep their current shape. The practical work is slightly larger because tests, runtime smoke checks, and class discovery may currently refer to short class names.

DataClass and DBAccess should initially share the same project-level namespace. DBAccess generated methods currently instantiate DataClass objects with short class names such as `new SupportTicketData()`. When both generated outputs are in the same namespace, PHP resolves those short names without `use` statements. `use Project\Data\SupportTicketData;` becomes necessary only if DataClass and DBAccess are intentionally split into different namespaces later.

Sample rollout should be mixed rather than all-or-nothing. Keep the earliest / simplest samples namespace-free as the approachable default path, and make later or representative samples namespaced so both contracts stay covered.

## Definition of done

- Project metadata can store an empty or non-empty PHP namespace.
- Empty namespace preserves current generated PHP output behavior.
- Non-empty namespace produces valid `namespace ...;` declarations in DataClass and DBAccess base / wrapper files.
- Generated code references still work for parent DataClass and DBAccess wrapper/base relationships.
- At least one DataClass-only sample and at least one DataClass + DBAccess sample declare the namespace in seed metadata and references.
- At least one simple sample remains namespace-free and is verified as the default behavior.
- Contract tests verify both namespace-free and namespaced output and remain green for relevant samples.
- Current status docs record what is complete and what is deferred.

## Progress checklist

| Item | Status | Notes |
| --- | --- | --- |
| Scope memo and active-plan promotion | `DONE` | This file records the scoped plan and `docs/current-plans.md` tracks the generated-output lane separately from Mtool implementation cleanup. |
| Project namespace metadata design | `DONE` | `projects.php_namespace` is persisted through schema bootstrap, migration, project settings, repository reads/writes, and metadata bundle import/export. |
| DataClass namespace generation | `DONE` | Canonical DataClass base/wrapper templates accept an optional namespace section; empty namespace preserves the previous output shape. |
| DBAccess namespace generation | `DONE` | Canonical DBAccess base/wrapper templates use the same project namespace as DataClass output, so generated short class references continue to resolve without `use`. |
| Representative sample verification | `DONE` | `sample04` covers DataClass-only namespaced output; `sample10` covers DataClass + DBAccess namespaced output. |
| Mixed sample rollout | `DONE` | `sample01` remains namespace-free as the default-path check; sample15/sample26 metadata bundle references cover the new empty namespace field. |
| Mtool self-output scope check | `DEFERRED` | Mtool self-output and Mtool implementation namespace cleanup are useful later but not required for generated PHP output namespace support. This lane intentionally stops before namespacing Mtool's own generated/self-output code. |
| Final tests and commit | `DONE` | Targeted tests and `make test` are green. |

## Verification

- `php -l` passed for the changed PHP implementation files.
- `make sample01-pack-runtime-test` passed for namespace-free default output.
- `make sample04-pack-runtime-test` passed for namespaced DataClass output.
- `make sample10-pack-runtime-test` passed for namespaced DataClass + DBAccess output.
- `make user-db-contract-test` passed for MySQL and SQLite generated runtime contracts.
- Existing sample15 config DB volume was migrated with `mtool/scripts/migrate_config_db.php`; the migration reached `035_project_php_namespace.sql` and reported no missing columns.
- `make sample15-pack-runtime-test` passed for metadata bundle export/import with `php_namespace`.
- `make sample26-pack-runtime-test` passed for capstone metadata bundle coverage with `php_namespace`.
- `make test` passed: 258 tests, 9023 assertions, 1 skipped.
