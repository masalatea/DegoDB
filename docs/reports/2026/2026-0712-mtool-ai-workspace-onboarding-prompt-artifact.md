# Mtool AI Workspace Onboarding Prompt Artifact

Date: 2026-07-12

## Status

`DONE`

## Purpose

Generate a reviewable AI/user onboarding prompt and machine-readable metadata from the side-effect-free workspace resolver result.

This is the #839 slice. It still does not create directories, write manifests, run scans, or mutate a user project.

## Implemented

- Added `app_ai_workspace_onboarding_prompt_artifact()`.
- Added a human-readable prompt text generator.
- Added machine-readable prompt metadata:
  - artifact version;
  - contract version;
  - selected profile;
  - workspace root;
  - Mtool-owned project root;
  - decision source;
  - ignored sources;
  - warnings and errors;
  - role mappings;
  - directory candidates;
  - manifest candidates;
  - approval and initialization flags.
- Added explicit prompt behavior for resolver errors.
- Added explicit prompt behavior for warnings that must be resolved before filesystem writes.
- Kept the artifact side-effect-free:
  - no directories created;
  - no manifests written;
  - no scan started.

## Prompt behavior

For a valid resolver result without warnings, the prompt asks whether the user wants to approve the workspace plan and continue to explicit initialization.

For a valid resolver result with warnings, the prompt keeps the plan reviewable but sets `can_initialize=false` and asks the user to resolve or explicitly accept the warnings before initialization.

For resolver errors, the prompt explains the missing or invalid input and asks the user to adjust the workspace input before retrying.

## Tests

Added coverage to `tests/Integration/AiWorkspaceContractTest.php`:

- valid onboarding prompt and metadata;
- external/disabled role mapping shown in prompt text;
- no-write/no-scan metadata;
- warning handling before filesystem writes;
- resolver error prompt handling.

## Verification

Targeted test:

```text
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/AiWorkspaceContractTest.php
```

Result:

```text
OK (17 tests, 110 assertions)
```

Full test:

```text
make test
```

Result:

```text
blocked by local Docker daemon availability
```

The first attempt failed during Docker build status streaming:

```text
target web-admin: failed to receive status: rpc error: code = Unavailable desc = error reading from server: EOF
```

The retry failed before test execution because the Docker socket was unavailable:

```text
failed to connect to the docker API at unix:///Users/matsue/.docker/run/docker.sock
```

`docker info` confirmed that the Docker client is available but the server/daemon is not reachable.

## Next candidate

`Mtool AI workspace explicit initialization preflight`

Define the explicit initialization command boundary before any real directory creation:

- required approval input;
- no overwrite behavior;
- manifest write set;
- `.gitignore` suggestion behavior;
- dry-run versus apply modes;
- tests for existing files and disabled/external roles.
