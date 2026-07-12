# Mtool AI Workspace Contract Dry-Run Hardening

Date: 2026-07-12

## Status

`DONE`

## Purpose

Run additional dry-run scenarios against the side-effect-free AI workspace contract/resolver and fix findings until no new actionable issues were found.

No workspace directories were created and no scans were executed.

## Dry-run rounds

### Round 1: root paths, role aliases, and relative write guards

Findings:

- `project_root="/"` was trimmed to an empty path.
- Role mapping input using standard directory names such as `design-briefs` was silently ignored because the internal role key is `design_briefs`.
- Workspace-relative write requests such as `mtool-project/metadata/schema.json` bypassed the Mtool-owned read-only guard.

Fixes:

- Preserve root paths during path trimming/joining.
- Accept role mapping aliases based on internal role keys and standard directory names.
- Normalize workspace-relative write targets before evaluating the read-only guard.
- Warn on unknown role mappings instead of silently ignoring them.

### Round 2: parent traversal in role mappings and write targets

Findings:

- Relative mapped paths such as `../docs/proposals` could escape the workspace.
- Relative write targets with traversal could bypass guard matching before normalization.
- Invalid mapped paths still appeared in candidate directory output even when the resolver returned `ok=false`.

Fixes:

- Reject parent traversal in relative mapped paths.
- Mark invalid role mappings disabled so no unsafe candidate directory is emitted.
- Lexically normalize write targets before comparing against `mtool-project/`.

### Round 3: relative root warnings

Finding:

- Relative `project_root`, `mtool_home`, or explicit workspace roots were accepted without warning.

Fix:

- Keep the dry-run result valid, but add warnings that relative roots must be resolved before filesystem writes.

### Round 4: explicit external profile without workspace root

Finding:

- `profile=external` without `workspace_root_cli` or `workspace_root_env` fell back to `project-local`, changing the user's explicit intent.

Fix:

- Treat explicit `external` profile without an explicit workspace root as invalid.

### Round 5: missing required roots for explicit profiles

Finding:

- `profile=mtool-work` without `mtool_home`, and `profile=project-local` without `project_root`, produced relative placeholder workspace paths.

Fix:

- Do not invent workspace roots when required profile inputs are missing.

## Final dry-run result

The final dry-run covered:

- normal project-local workspace;
- explicit `mtool-work`;
- CLI external workspace precedence over env/profile;
- external profile missing workspace root;
- missing required roots for explicit profiles;
- role alias mapping;
- unknown role mapping warning;
- parent traversal rejection;
- relative root warnings;
- root project path;
- absolute, relative, and traversal write-guard cases;
- editable Mtool config exceptions;
- sibling-prefix non-match;
- copy-plan slugging.

No new actionable findings remained after the fixes.

## Verification

```text
php -l mtool/app/ai_workspace_contract.php
git diff --check
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/AiWorkspaceContractTest.php
```

Targeted result:

```text
OK (14 tests, 71 assertions)
```

Full test:

```text
make test
```

Result:

```text
OK, but incomplete, skipped, or risky tests!
Tests: 496, Assertions: 14435, Skipped: 1.
```

## Next candidate

Continue to #839: generate reviewable onboarding prompt text and machine-readable prompt metadata from the now-hardened resolver result, still without filesystem writes.
