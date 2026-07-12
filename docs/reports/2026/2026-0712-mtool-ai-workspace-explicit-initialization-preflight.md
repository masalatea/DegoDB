# Mtool AI Workspace Explicit Initialization Preflight

Date: 2026-07-12

## Status

`DONE`

## Purpose

Define and implement the side-effect-free preflight for explicit workspace initialization before any real directory creation or manifest writing.

This is the #840 slice. It still does not create directories, write manifests, run scans, edit `.gitignore`, or mutate a user project.

## Implemented

- Added `app_ai_workspace_initialization_preflight()`.
- Added explicit initialization metadata:
  - mode: `dry-run` or `apply`;
  - approval flag;
  - accepted warnings flag;
  - `can_apply`;
  - side-effect-free/no-write/no-scan flags;
  - no-overwrite flag.
- Added directory planning:
  - create missing Mtool/workspace-owned directories;
  - reuse existing directories;
  - block paths that already exist as files;
  - skip external role directories;
  - skip disabled role directories.
- Added manifest planning:
  - planned manifests: `workspace`, `resolver`, `role_mapping`, `git_policy`, `user_settings`;
  - generated manifest content skeletons;
  - no-overwrite behavior for existing manifest paths.
- Added `.gitignore` suggestion text only:
  - no repository file edits;
  - project-local, mtool-work, and external profile wording.
- Added existing path map support for tests and future caller integration.

## Safety behavior

- `apply` mode requires explicit approval.
- Resolver/onboarding errors block initialization.
- Resolver warnings block initialization unless explicitly accepted.
- Existing directories are reused.
- Existing files at directory paths are blockers.
- Existing manifest files are skipped, not overwritten.
- External/disabled roles are not created by Mtool initialization.

## Verification

Static checks:

```text
php -l mtool/app/ai_workspace_contract.php
git diff --check
```

Direct side-effect-free dry run:

```text
php -r '... app_ai_workspace_initialization_preflight(...) checks ...'
```

Result:

```text
ok: valid apply preflight
ok: can apply with approval
ok: still no writes
ok: external/disabled dirs skipped
ok: existing manifest skipped
ok: apply requires approval
ok: warnings block apply
ok: accepted warnings allow apply preflight
ok: file collision blocks
ok: invalid onboarding rejected
```

PHPUnit target:

```text
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/AiWorkspaceContractTest.php
```

Result:

```text
blocked by local Docker daemon availability
failed to connect to the docker API at unix:///Users/matsue/.docker/run/docker.sock
```

This is the same local Docker daemon issue observed during #839. The PHP code path was verified with direct side-effect-free checks, but Docker-backed PHPUnit/full-suite verification needs Docker to be restarted.

## Next candidate

`Mtool AI workspace initialization apply first slice`

After Docker-backed verification is available again, implement the first explicit apply command/helper that creates directories and writes missing manifests only after approval, using this preflight result as the contract.
