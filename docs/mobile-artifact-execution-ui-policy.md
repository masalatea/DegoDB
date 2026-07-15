# Mobile artifact execution UI policy / mobile artifact execution UI policy

English companion: This document defines when Mtool may expose UI-triggered mobile artifact generation and which safety controls must exist first.

This document defines the policy for UI-triggered mobile artifact generation in Mtool.

この文書は、Mtool UI から mobile artifact generation を実行する場合の policy を定義する。

## Current decision / 現在の判断

Keep the current Mtool UI read-only for mobile wrapper artifacts.

Do not add UI-triggered artifact generation until a concrete operator need exists and the execution controls below are implemented.

現在の Mtool UI は mobile wrapper artifact について read-only のままにする。

具体的な operator need があり、下記の execution control が実装されるまでは、UI-triggered artifact generation を追加しない。

## Why / 理由

Artifact generation can create or overwrite files. Even when Mtool only emits handoff/input artifacts, execution from a web UI needs explicit safety controls.

artifact generation は file 作成・上書きを伴う可能性がある。Mtool が handoff/input artifact だけを出す場合でも、web UI から実行するには明示的な safety control が必要。

## Evaluation basis / 評価根拠

This policy follows the mobile external feasibility result: Mtool should own handoff/input artifacts and validation boundaries, while native/app project generation and potentially destructive execution require explicit controls.

この policy は mobile external feasibility result に基づく。Mtool は handoff / input artifact と validation boundary を所有し、native / app project generation や破壊的になり得る実行は明示 control を必要とする。

Related references:

- [Mobile External Feasibility Study / mobile external FS](mobile-external-feasibility-study.md)
- [Mobile Ownership Boundaries / mobile ownership boundary](mobile-ownership-boundaries.md)
- [Mobile Output Modes / mobile output modes](mobile-output-modes.md)
- [2026-0714 Mobile Artifact Execution UI Policy](reports/2026/2026-0714-mobile-artifact-execution-ui-policy.md)

## Required controls before execution UI / execution UI前の必須control

Before adding any execution button, define and test:

1. CSRF protection;
2. authentication and authorization;
3. output directory allow-list;
4. overwrite policy;
5. dry-run/preview mode;
6. audit log;
7. failure/partial-output handling;
8. validation after generation;
9. race/concurrent execution behavior;
10. cleanup/retry behavior.

## Output directory policy / output dir policy

Allowed default output roots:

- Mtool repository development:
  - `work/source-outputs/`
  - `work/feasibility-studies/`
- User workspace:
  - `{project_root}/mtool-workspace/mtool-project/`
  - `{project_root}/mtool-workspace/review-artifacts/`
  - `{project_root}/mtool-workspace/validation/`

Execution UI must not write outside configured roots.

## Overwrite policy / overwrite policy

Default:

- do not overwrite existing artifact directories;
- require a new output key or explicit overwrite confirmation;
- record overwritten paths in audit log if overwrite is allowed.

## Dry-run policy / dry-run policy

Execution UI should support preview/dry-run before writing files:

- selected project/source-output;
- selected mode;
- selected targets;
- planned output directory;
- planned files;
- validation warnings;
- non-goals.

## Audit policy / audit policy

Each execution should record:

- user/operator identity;
- timestamp;
- project key;
- source-output key;
- mode;
- selected targets;
- output directory;
- generated files;
- validation result;
- warnings/errors.

## Failure policy / failure policy

If generation fails:

- report partial files;
- do not claim success;
- do not silently retry;
- preserve logs;
- require operator action before overwrite/retry.

## Current UI wording / 現在のUI文言

The current read-only page may show CLI guidance and artifact descriptions, but should not expose generation buttons.

Recommended warning:

```text
This page describes mobile wrapper artifacts. Artifact generation is currently performed through controlled CLI/source-output workflows, not from this UI.
```

## Reopen condition / 再開条件

Reopen execution UI implementation only when:

- a concrete operator workflow needs UI execution;
- output mode configuration exists;
- CSRF/auth/output-dir/overwrite/audit/failure controls are implemented and tested;
- read-only guidance is no longer enough.
