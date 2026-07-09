# Local No-Code Stack Cleanup Plan Before Availability

Date: 2026-07-09

Status: `PLAN_READY`

## Summary

#535 records the local no-code stack cleanup plan before moving toward availability enablement. The unpushed stack is 102 commits ahead before this planning commit, and a backup ref has been created.

## Backup

- Backup ref: `refs/backup/no-code-stack-before-cleanup-20260709`
- Backup target: `53aa0f38`
- Purpose: provide a local recovery point before any squash, rebase, reset, or other history rewrite.

## Proposed Squash Groups

| Group | Range / Representative commits | Resulting commit theme |
| --- | --- | --- |
| G1 | `184af06b` through `a19c0a5f` | Mtool no-code dogfooding metadata and custom slot presentation |
| G2 | `22bb0d42` through `f7390679` | Visible custom slot rendering and stack review |
| G3 | `9f87d61f` through `5b77db49` | Custom operation manifest metadata and adapter handoff |
| G4 | `1d911fbf` through `2ed1df96` | Review/publish route boundary metadata and route-boundary lane closure |
| G5 | `406fc0da` through `e35ca7e0` | Custom operation dispatch preflight, review artifact guard, and guard audit append |
| G6 | `1c86aea3` through `7d381d00` | Review workflow persistence repository, route helper, and availability replan |
| G7 | `0a7f8c56` through `d8dfb8c0` | No-push decision plus guard-first and audit hardening |
| G8 | `570cc32b` through `102058a9` | Review workflow repository validation and fetch filter coverage |
| G9 | `804168bd` through `ab2df821` | Repository identity filters and closed-status duplicate matrix |
| G10 | `08c277bb` through `ccc5cdb9` | Repository normalization and decoded payload fallback coverage |
| G11 | `348f72f2` through `09c3be5f` | Requested-by, identity required-field, and in-review duplicate reuse hardening |
| G12 | `53aa0f38` and this plan | No-push checkpoint and cleanup planning before availability |

## Execution Plan

1. Use the backup ref as the recovery point.
2. Rewrite only local commits on top of `origin/develop`.
3. Prefer squash groups over editing individual commits one by one.
4. Preserve code + tests + relevant docs in the same resulting commit where possible.
5. After cleanup, rerun focused no-code repository tests and `make test`.
6. Only after verification, promote availability enablement preflight.

## Boundary

- No squash, rebase, reset, push, or force-push is performed by this planning slice.
- Availability enablement remains parked.
- Generated button execution remains disabled.

## Verification

- `git rev-parse refs/backup/no-code-stack-before-cleanup-20260709`
- `git diff --check`
