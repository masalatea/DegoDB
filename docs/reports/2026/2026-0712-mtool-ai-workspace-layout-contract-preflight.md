# Mtool AI Workspace Layout Contract Preflight

Date: 2026-07-12

## Status

`DONE`

## Purpose

Turn the AI/Mtool workspace layout rules from documentation into a side-effect-free Mtool contract helper and resolver.

This is the first code slice for the workspace plan. It intentionally does not create directories, scan projects, write manifests, or mutate user projects.

## Implemented

- Added `mtool/app/ai_workspace_contract.php`.
- Added a side-effect-free workspace contract:
  - supported profiles: `project-local`, `mtool-work`, `external`;
  - fixed Mtool-owned `mtool-project/` directory names;
  - standard workspace roles;
  - manifest names including `role-mapping.json`;
  - editable config file allowlist;
  - copy-plan artifact naming;
  - read-only guard wording.
- Added a side-effect-free resolver:
  - CLI workspace root wins over env and profile;
  - env workspace root wins over profile;
  - explicit `mtool-work` and `project-local` profiles are supported;
  - project-local default is `project_root/mtool-workspace`;
  - development fallback is `mtool_home/work/<project-key>`;
  - returns diagnostics without filesystem writes.
- Added role mapping normalization:
  - standard path;
  - mapped path;
  - disabled roles;
  - owner;
  - git policy;
  - external mapped paths.
- Added Mtool-owned direct-write evaluation:
  - direct AI writes under `mtool-project/` are refused by default;
  - explicitly editable config files are allowed.
- Added stable copy-plan artifact path helper.

## Tests

Added `tests/Integration/AiWorkspaceContractTest.php`.

Coverage:

- project-local resolution;
- CLI/env/profile precedence;
- explicit `mtool-work` profile;
- existing notes/Obsidian-style external role mapping;
- disabled role mapping;
- Mtool-owned read-only guard;
- editable config exception;
- copy-plan slug/path stability.

## Verification

Targeted test:

```text
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/AiWorkspaceContractTest.php
```

Result:

```text
OK (6 tests, 34 assertions)
```

Full test:

```text
make test
```

Result:

```text
OK, but incomplete, skipped, or risky tests!
Tests: 488, Assertions: 14398, Skipped: 1.
```

The skipped test is pre-existing in the full integration suite path.

## Not implemented

- No workspace initialization command.
- No directory creation.
- No scan execution.
- No manifest writing.
- No user-project copy or promotion workflow.
- No AI provider integration.

## Next candidate

`Mtool AI workspace onboarding prompt artifact`

Generate a reviewable prompt/plan from the resolver result, still without filesystem writes. This should make the first AI/user confirmation step concrete before adding explicit workspace initialization.
