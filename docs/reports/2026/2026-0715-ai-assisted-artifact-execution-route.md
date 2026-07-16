# 2026-0715 AI-Assisted Artifact Execution Route

## Purpose / 目的

This history note records the decision that direct browser execution buttons are not the preferred next step for artifact generation. Instead, Mtool should document and later support an AI-assisted task-packet route.

この履歴は、artifact generation について browser の直接実行 button を次の優先手段にしない判断を記録する。代わりに、Mtool は AI-assisted task-packet route を文書化し、必要なら後で実装する。

## Decision / 判断

Direct Mtool UI execution remains read-only until CSRF, authentication, output directory, overwrite, audit, failure, validation, concurrency, and retry controls are explicit and tested.

Mtool UI からの直接実行は、CSRF、認証、出力先 directory、上書き、監査、失敗、検証、同時実行、retry control が明確に実装・検証されるまで read-only のままにする。

For practical execution convenience, prefer a task packet that Codex / Claude can read. The agent explains the command, output directory, overwrite policy, validation command, and forbidden actions, then asks the user before writing files.

実用上の実行導線としては、Codex / Claude が読める task packet を優先する。agent は command、出力先、上書き policy、validation command、禁止 action を説明し、file 書き込み前に利用者へ確認する。

## Documentation Update / 文書更新

- `docs/mobile-artifact-execution-ui-policy.md`
  - Added the AI-assisted execution route.
  - Clarified that the UI can remain read-only while AI executes the CLI after confirmation.
  - Added recommended task packet shape and confirmation wording.
- `docs/README.md`
  - Added the AI-assisted task-packet route to the mobile artifact execution policy description.
- `docs/current-plans.md`
  - Changed the candidate from direct writable execution UI controls to AI-assisted artifact execution packets.

## Boundary / 境界

This is documentation and route definition only. It does not implement a generator for artifact-generation task packets yet.

これは文書化と導線定義のみであり、artifact-generation task packet の generator 実装はまだ行わない。

Implementation would be a new scoped lane if selected after the current Post-RSS next product-scope decision.

実装する場合は、現在の RSS 後 product-scope selection で選ばれた後の新しい scoped lane として扱う。

## Status / 状態

`DOCUMENTED_ROUTE_ONLY`
