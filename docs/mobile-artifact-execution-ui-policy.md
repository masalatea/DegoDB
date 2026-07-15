# Mobile artifact execution UI policy / mobile artifact execution UI policy

English companion: This document defines when Mtool may expose UI-triggered mobile artifact generation and which safety controls must exist first.

This document defines the policy for UI-triggered mobile artifact generation in Mtool.

この文書は、Mtool UI から mobile artifact generation を実行する場合の policy を定義する。

## Current decision / 現在の判断

Keep the current Mtool UI read-only for mobile wrapper artifacts.

Do not add UI-triggered artifact generation buttons until a concrete operator need exists and the execution controls below are implemented.

When users want execution convenience before those UI controls exist, prefer an AI-assisted task-packet route: Mtool exposes the intended command, inputs, output directory, overwrite policy, validation command, and forbidden actions as a readable packet. Codex / Claude can read that packet, ask the user for explicit confirmation, run the CLI, validate the result, and report the outcome.

現在の Mtool UI は mobile wrapper artifact について read-only のままにする。

具体的な operator need があり、下記の execution control が実装されるまでは、UI から直接実行する artifact generation button は追加しない。

ただし、その前段階の実行導線としては、AI に安全に依頼する task packet route を優先する。Mtool は、実行予定 command、入力、出力先、上書き policy、validation command、禁止 action を人間と AI が読める packet として出す。Codex / Claude はその packet を読み、利用者に明示確認してから CLI を実行し、検証して結果を報告する。

## Why / 理由

Artifact generation can create or overwrite files. Even when Mtool only emits handoff/input artifacts, execution from a web UI needs explicit safety controls.

artifact generation は file 作成・上書きを伴う可能性がある。Mtool が handoff/input artifact だけを出す場合でも、web UI から実行するには明示的な safety control が必要。

An AI-assisted task packet keeps the browser UI read-only while still giving the user a practical execution path. The confirmation happens in the agent conversation, not through a click-prone browser button.

AI-assisted task packet にすると、browser UI は read-only のままにしつつ、利用者には実用的な実行経路を提供できる。確認は誤クリックしやすい browser button ではなく、agent conversation 内で行う。

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

## AI-assisted execution route / AI支援の実行導線

This is the preferred route before direct execution buttons are added to Mtool UI.

Mtool may expose a read-only execution guide or task packet that contains:

1. artifact type to generate;
2. source project / source-output / packet references;
3. exact CLI command;
4. intended output directory;
5. overwrite policy;
6. validation command;
7. expected generated files;
8. forbidden actions;
9. failure handling notes;
10. confirmation wording for the AI agent.

Mtool itself should not run the command from the UI in this route. The user gives the task packet to Codex / Claude, and the agent must ask before running any command that writes files.

これは、Mtool UI に直接実行 button を追加する前の優先導線である。

Mtool は read-only の実行 guide または task packet として、次の情報を出してよい。

1. 生成する artifact 種別;
2. 参照する project / source-output / packet;
3. 正確な CLI command;
4. 想定出力先 directory;
5. 上書き policy;
6. validation command;
7. 生成予定 file;
8. 禁止 action;
9. 失敗時の扱い;
10. AI agent が利用者に確認する文言。

この route では、Mtool UI 自身は command を実行しない。利用者が task packet を Codex / Claude に渡し、agent は file を書く command の実行前に必ず確認する。

### Recommended packet shape / 推奨packet形状

```text
artifact-generation-task/
  task.json
  TASK.md
  input/
    declared-source-artifacts.json
  output/
    generated-files.json          created after execution
    validation-result.json        created after validation
```

`task.json` should include:

- `status`: `pending_user_confirmation`;
- `write_scope`: allowed output directory;
- `overwrite`: default `false`;
- `commands.generate`: exact CLI command;
- `commands.validate`: exact validation command;
- `forbidden_actions`: dependency install, project init, native build, signing, store submission, unlisted file overwrite, DB mutation, network calls unless explicitly declared;
- `success_criteria`: files and validation results that must exist before claiming success.

`task.json` には少なくとも次を含める。

- `status`: `pending_user_confirmation`;
- `write_scope`: 許可された出力先 directory;
- `overwrite`: default `false`;
- `commands.generate`: 正確な CLI command;
- `commands.validate`: 正確な validation command;
- `forbidden_actions`: dependency install、project init、native build、signing、store submission、未宣言 file overwrite、DB mutation、明示されていない network call;
- `success_criteria`: 成功とみなすために必要な file と validation 結果。

### Agent confirmation / agent確認

The agent should summarize:

- what will be generated;
- where it will be written;
- whether existing files will be overwritten;
- which command will be run;
- which validation will be run after generation.

Then it asks for explicit approval.

Agent は次を要約する。

- 何を生成するか;
- どこに書くか;
- 既存 file を上書きするか;
- どの command を実行するか;
- 生成後にどの validation を実行するか。

そのうえで明示承認を求める。

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
It may also show a link to the AI-assisted task-packet route when such a packet exists.

Recommended warning:

```text
This page describes mobile wrapper artifacts. Artifact generation is currently performed through controlled CLI/source-output workflows, not from this UI.
```

Recommended AI route wording:

```text
If you want AI-assisted generation, give the generated task packet to Codex or Claude. The agent must explain the command, output directory, overwrite policy, and validation command, then ask for confirmation before writing files.
```

現在の read-only page は CLI guidance と artifact description を表示してよいが、generation button は出さない。
対応する packet が存在する場合は、AI-assisted task-packet route への link を表示してよい。

推奨文言:

```text
この画面は mobile wrapper artifact の説明画面です。artifact generation はこの UI からではなく、管理された CLI / source-output workflow で実行します。
```

AI 導線の推奨文言:

```text
AI支援で生成したい場合は、生成された task packet を Codex または Claude に渡してください。agent は command、出力先、上書き policy、validation command を説明し、file 書き込み前に確認します。
```

## Reopen condition / 再開条件

Reopen execution UI implementation only when:

- a concrete operator workflow needs UI execution;
- output mode configuration exists;
- CSRF/auth/output-dir/overwrite/audit/failure controls are implemented and tested;
- read-only guidance is no longer enough.

AI-assisted execution packets do not by themselves reopen direct UI execution. They are a separate safe handoff route.

AI-assisted execution packet は、それだけでは direct UI execution の再開条件にならない。これは別の安全な handoff route として扱う。
