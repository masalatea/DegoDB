# Mtool AI Workspace Initialization Apply First Slice

Date: 2026-07-12

## Summary

Implemented the first filesystem-writing slice for the AI/Mtool workspace contract.

`app_ai_workspace_initialization_apply()` now applies only a successful, explicitly approved `apply`-mode preflight. It creates missing directories and writes missing manifest files, while preserving the no-overwrite contract and keeping scan execution out of scope.

## Boundary

This slice intentionally stays small:

- Requires `can_apply=true` from `app_ai_workspace_initialization_preflight()`.
- Requires `mode=apply`.
- Requires `no_overwrite=true`.
- Creates only directories listed by the preflight `create_directories`.
- Writes only manifests listed by the preflight `write_manifests`.
- Rechecks the filesystem at apply time.
- Skips any manifest that already exists at apply time.
- Does not create external role directories.
- Does not create disabled role directories because disabled roles are omitted from the resolved directory list.
- Does not start scan, import, generation, validation, or copy/adaptation workflows.

## Tests

Targeted Docker-backed PHPUnit:

```text
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/AiWorkspaceContractTest.php
OK (25 tests, 164 assertions)
```

Full suite:

```text
make test
OK, but incomplete, skipped, or risky tests!
Tests: 507, Assertions: 14528, Skipped: 1.
```

## Next

Close the apply-helper lane and decide whether the next product slice should be:

1. a CLI/admin entry point that invokes preflight + apply after explicit approval,
2. prompt/onboarding integration that shows the exact apply boundary to AI users, or
3. no further implementation until there is a concrete adoption workflow.

