# Mtool AI Workspace CLI Wrapper First Slice

Date: 2026-07-13

## Summary

Implemented the first CLI wrapper for explicit AI/Mtool workspace initialization:

```text
php mtool/scripts/init_ai_workspace.php [options]
```

The wrapper uses the existing side-effect-free CLI entry preflight contract. It returns usage or JSON/text preflight output by default, and calls `app_ai_workspace_initialization_apply()` only when the preflight confirms explicit apply approval.

## Supported boundary

The wrapper supports:

- `--help`
- `--json`
- `--project-root=PATH`
- `--mtool-home=PATH`
- `--project-key=KEY`
- `--profile=NAME`
- `--workspace-root=PATH`
- `--mode=dry-run|apply`
- `--approve`
- `--accept-warnings`
- `--role=ROLE=PATH`
- `--external-role=ROLE=PATH`
- `--disable-role=ROLE`
- `--existing-path=PATH:TYPE`

Safety remains unchanged:

- default mode is `dry-run`;
- `apply` requires `--mode=apply --approve`;
- resolver warnings still require `--accept-warnings`;
- preflight itself never writes;
- apply preserves no-overwrite behavior;
- scan, import, generation, validation, and copy/adaptation workflows remain out of scope.

## Tests

Syntax checks:

```text
php -l mtool/scripts/init_ai_workspace.php
php -l mtool/app/ai_workspace_contract.php
php -l tests/Integration/AiWorkspaceContractTest.php
```

Local dry-run smoke:

```text
php mtool/scripts/init_ai_workspace.php --project-root=/tmp/mtool-ai-cli-local-check --json
```

Targeted Docker-backed PHPUnit:

```text
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/AiWorkspaceContractTest.php
OK (32 tests, 215 assertions)
```

Full suite:

```text
make test
OK, but incomplete, skipped, or risky tests!
Tests: 514, Assertions: 14579, Skipped: 1.
```

## Next

Close the CLI wrapper lane and decide whether the next step should be:

1. AI-facing onboarding documentation that tells Codex/Claude exactly which command to run,
2. a short command guide for human users,
3. a higher-level prompt integration slice, or
4. hold until a concrete adoption workflow exists.

