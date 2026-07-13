# AI Task-Packet Workflow / AI task packet ワークフロー

English companion:
Use this workflow when Codex or Claude should turn bounded JSON material into a review-only schema proposal. Mtool prepares a task packet, the agent asks once before writing, and one shared validator creates a separate Mtool-derived review artifact. Ollama is an explicit optional local fallback, not the primary path.

Codex や Claude に JSON 資料を読ませ、review 専用 schema proposal を作る場合の恒久導線です。長い prompt を手作業で渡す代わりに、Mtool が source、比較 context、出力 contract、禁止事項、validation command をまとめた task packet を作ります。

## 最短の流れ

1. Sample19 task packet を作ります。

```bash
php mtool/scripts/create_sample19_schema_proposal_task.php
```

2. 表示された task root で Codex / Claude に一言伝えます。

> Mtoolの未処理タスクを進めて。

3. AI は `TASK.md` と `task.json` を読み、対象 source、出力先、validation、禁止操作を説明して一度だけ確認します。
4. 利用者が具体的 task に同意すると、AI は `output/candidate.json` だけを書き、packet に宣言された validation command を実行します。
5. 成功時は `output/review-artifact.json` が作られます。これは candidate そのものではなく、Mtool が canonical diff を独立導出した別 artifact です。

Agent が task packet、scan、fallback candidate、formal output の権限差を作業中に確認する場合は、[AI Schema Proposal Handoff Guide](ai-schema-proposal-handoff-guide.md) を併読します。

Generic な過去の「継続」は、provider 実行や別 task の承認にはなりません。確認は生成された具体的 task interaction 内で行います。

## Task packet の内容

```text
work/ai-tasks/<task-id>/
  task.json
  TASK.md
  input/source.json
  input/canonical-snapshot.json
  input/output-shape.json
  input/scan.json
  input/fallback-candidate.json    optional
  input/fallback-validation.json optional
  output/candidate.json
  output/validation.json
  output/review-artifact.json
```

情報の優先順位は固定です。

1. `source.json`: source of truth
2. `canonical-snapshot.json`: 現在の Mtool metadata との比較 context
3. `output-shape.json`: candidate JSON contract
4. `scan.json`: deterministic な pointer/type 索引。推論なし
5. fallback candidate: advisory な叩き台

scan や fallback の内容が source と矛盾する場合は source を優先します。

## 共通 Validation

Codex、Claude、Ollama のどの経路でも正本入口は同じです。

```bash
php mtool/scripts/validate_schema_proposal_task.php \
  --task=work/ai-tasks/<task-id>/task.json \
  --candidate=work/ai-tasks/<task-id>/output/candidate.json
```

公開 PHP facade は次です。

```php
app_schema_proposal_task_validate(
    $taskPacket,
    $candidateJson,
    $sourceBytes,
    $canonicalSnapshotBytes,
);
```

処理順は `task_validation`、`input_integrity`、`candidate_decode`、`candidate_validation`、`canonical_diff_derivation`、`review_artifact_validation`、`review_artifact_ready` です。

Candidate は `canonical_diff=[]` を返します。AI が canonical comparison を確定するのではなく、Mtool が candidate と canonical snapshot から diff を導出します。成功結果には candidate と review artifact の別 SHA-256、および `mutation_performed=false` が含まれます。

## Review

Task review route は default-off、認証必須、GET-only です。

```text
/projects/SAMPLE19/schema-proposal-tasks/<task-id>/review
```

Sample19 stack で明示的に確認する場合だけ有効化します。

```bash
MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED=1 \
  ./sample/tutorials/sample19-json-first-content-model-demo/run.sh up
```

確認後は通常起動へ戻します。

```bash
./sample/tutorials/sample19-json-first-content-model-demo/run.sh up
```

Review page は task/source/canonical/shape/scan/validation/candidate/review/derivation/diff hash を再検証します。form、button、script、POST、approve、apply、import、SQL、build、publish はありません。

## Optional Ollama fallback

Codex / Claude が使える場合、Ollama は不要です。課金なしの local candidate が必要な場合だけ、生成済み task を明示指定します。

```bash
php mtool/scripts/run_sample19_local_ai_proposal.php \
  --task=work/ai-tasks/<task-id>/task.json \
  --execute-local-fallback
```

この command は明示 flag がなければ実行されません。結果は agent-owned `output/candidate.json` ではなく、advisory な `input/fallback-candidate.json` と `input/fallback-validation.json` に出ます。利用者または Codex / Claude が source と照合した後、必要なら正式 candidate を作ります。

## 安全境界

- task packet は `work/` 配下の disposable local artifact で、Git 管理対象ではありません。
- generator は既存 task root を上書きしません。
- task は DB/config metadata、SQL、import、apply、build、publish、network を禁止します。
- paid provider API や credential は primary workflow に不要です。
- validation success は review artifact ready を意味し、schema 適用や metadata 更新の成功を意味しません。
- production data や個人情報を使う場合は、[Security And Data Handling](security-and-data-handling.md) の明示承認・redaction方針を先に確認します。

## 関連文書

- [JSON To DB Entrance](json-to-db-entrance.md)
- [AI Schema Proposal Handoff Guide](ai-schema-proposal-handoff-guide.md)
- [Security And Data Handling](security-and-data-handling.md)
- [Current Plans](current-plans.md)
- [Sample19 tutorial](../sample/tutorials/sample19-json-first-content-model-demo/README.md)
