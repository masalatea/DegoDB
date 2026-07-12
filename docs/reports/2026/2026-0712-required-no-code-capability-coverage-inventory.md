# Required No-Code Capability Coverage Inventory

## 目的

全sampleの完全変換ではなく、MtoolがNo Codeとしてsupportすべきcapabilityを代表sample evidenceで網羅できているか判定する。各項目は`COVERED`、`GAP`、`NOT_REQUIRED_WITH_REASON`のいずれかとする。

## Capability Matrix

| Capability | Decision | Current evidence | Remaining boundary |
| --- | --- | --- | --- |
| canonical schemaからlist/detail/form生成 | `COVERED` | Sample28、29、31、32のshared contract・runtime artifact・fast/browser test | domain追加だけでは新sliceを作らない |
| live read、row selection、search/filter/sort/pagination | `COVERED` | generic runtime contract、current/alias live runtime-data、filter/sort/page fail-closed test | 新しいquery形状が出た場合だけ拡張 |
| required/readonly/type validationとerror表示 | `COVERED` | Sample28 runtime draft・schema-form probe、server validation fail-closed contract | domain固有validationはcustomまたは明示extension |
| createの直接guarded execution | `COVERED` | Sample18 `create_task_card`、auth/CSRF/allowlist/audit/idempotency/Transaction Full、HTTP commit/rollback smoke | default-off・explicit authorityを維持 |
| updateのmanaged outbox execution | `COVERED` | Sample30・31 sync outbox、generated server DBAccess、App-local SQLite、failure visibility | async/retry policyを維持 |
| authentication、authority、CSRF、default-off | `COVERED` | Sample18・29・31のcurrent/alias authority、denied/CSRF/browser evidence | broad default enablementは対象外 |
| audit、idempotency、failure/recovery visibility | `COVERED` | Sample18 direct path、Sample30 outbox failure、operator outbox detail/retry contracts | cross-store atomicityはclaimしない |
| same-DB composite Transaction Full | `COVERED` | generated PDO/mysqli wrapper、Sample14 proxy、Sample18 guarded HTTP proof | concrete caller内で必要時に採用 |
| multiple related entities、parent/child、lookup input | `COVERED` | Sample22 book/published-chapter contracts、required belongs-to parent、lookup options、read-only list/detail/form、fast JSON/DOM evidence | mutationやeditor置換はこのcapabilityに不要 |
| non-CRUD lifecycle action | `COVERED` | Sample18 `complete_task_card` lifecycle metadata、guarded generated-submit route、Transaction Full commit evidence | reopen/deleteは別policyのためinitial supported matrixから除外 |
| generated/no-codeとcustom UIの同一workflow共存 | `COVERED` | Sample28 React bridge `hybrid_ownership_contract`、generated/custom/shared/fallback/test ownership、React bridge build/browser smoke、JSON Forms/rjsf fallback smoke | full custom app置換ではなくcontract-based handoffを維持 |
| hard deleteの汎用自動化 | `NOT_REQUIRED_WITH_REASON` | metadata-only delete候補はある | destructive policy・domain retention差が大きいためinitial supported matrixから除外し、custom/明示extensionとする |
| file upload、rich editor、domain固有widget | `NOT_REQUIRED_WITH_REASON` | generic field contract外 | custom componentの正規拡張点として扱う |
| 全sample・全screenの完全変換 | `NOT_REQUIRED_WITH_REASON` | product思想で否定 | capability evidenceを増やさない変換は行わない |

## 最小Gap解消順

### 1. Related-entity read/form slice

- Candidate: `sample22-ebook-chapter-workflow-demo`
- 理由: book/chapter parent-child、順序、editor create/update/publishという既存metadataがあり、relation・parent binding・lookup/lifecycleを1 domainで観察できる。
- First slice: read-only book-bound chapter list/detailとbook lookup/input metadata。既存API・custom editor flowは置換しない。
- Exit: relation identity、parent key handoff、lookup option source、fail-closed missing parent、fast JSON/DOM contractが揃う。
- Estimate: 1.5 - 2.5 days。

### 2. Generated lifecycle action slice

- Candidate: `sample18-mini-task-board-demo`
- Operation: `complete_task_card`
- 理由:既存metadata・DBAccess・Transaction Full・authority foundationを再利用し、新しいdomainやhard deleteを持ち込まずstate transitionを実証できる。
- Exit: explicit default-off authority、selected key、validation、audit/idempotency、Transaction Full、success/failure browser/HTTP evidenceが揃う。
- Estimate: 1 - 2 days。

### 3. Hybrid generated/custom ownership slice

- Candidate: `sample28-no-code-data-app-mvp` React bridge。
- 理由: runtime preview、React bridge、JSON Forms/rjsf probeが既に同一screen definitionから生成され、custom UIがgenerated contractを読む境界を最小追加で固定できる。
- Exit: `bridge-contract.json`の`hybrid_ownership_contract`でgenerated領域、custom領域、shared state/action handoff、authority ownership、test ownership、fallbackを明示。
- Status: `COVERED`。

## L1 Exit Condition

残るgapが`COVERED`または具体的理由付き`NOT_REQUIRED_WITH_REASON`になり、既存covered項目の回帰testが維持されれば、sampleを使ったcapability確認は完了とする。全sample・全screen・hard delete・domain固有widgetの完全自動化は要求しない。その後、Mtoolのcontained hybrid workflow選定へ進める。

Status: `CLOSED_NO_REMAINING_GAP`
