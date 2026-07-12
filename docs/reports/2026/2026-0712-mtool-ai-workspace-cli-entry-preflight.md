# Mtool AI Workspace CLI Entry Preflight

Date: 2026-07-12

## Summary

Implemented the side-effect-free contract for the future workspace initialization CLI entry.

The new helper, `app_ai_workspace_initialization_cli_entry_preflight()`, parses argv/env input and composes the existing resolver, onboarding prompt artifact, and initialization preflight without creating the actual CLI wrapper and without touching the filesystem.

## Command contract

Selected future command:

```text
php mtool/scripts/init_ai_workspace.php [options]
```

Supported preflight options:

- `--project-root=PATH`
- `--mtool-home=PATH`
- `--project-key=KEY`
- `--profile=project-local|mtool-work|external`
- `--workspace-root=PATH`
- `--mode=dry-run|apply`
- `--approve`
- `--accept-warnings`
- `--role=ROLE=PATH`
- `--external-role=ROLE=PATH`
- `--disable-role=ROLE`
- `--existing-path=PATH:TYPE`
- `--json`
- `--help`

## Precedence and safety

- Default mode is `dry-run`.
- `--workspace-root` wins over `MTOOL_AI_WORKSPACE_ROOT`.
- `apply` mode requires `--approve`.
- Resolver warnings require `--accept-warnings`.
- The preflight helper itself never writes to the filesystem.
- The preflight helper never starts scan, import, generation, validation, or copy/adaptation workflows.

## Tests

Syntax checks:

```text
php -l mtool/app/ai_workspace_contract.php
php -l tests/Integration/AiWorkspaceContractTest.php
```

Targeted Docker-backed PHPUnit:

```text
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/AiWorkspaceContractTest.php
OK (30 tests, 196 assertions)
```

Full suite:

```text
make test
OK, but incomplete, skipped, or risky tests!
Tests: 512, Assertions: 14560, Skipped: 1.
```

## Next

Promote the first CLI wrapper slice:

`mtool/scripts/init_ai_workspace.php`

The first wrapper should use the preflight contract, print usage/JSON output, and call `app_ai_workspace_initialization_apply()` only when the preflight confirms explicit approval.

