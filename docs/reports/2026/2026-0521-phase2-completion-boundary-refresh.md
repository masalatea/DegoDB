# 2026-05-21 Phase2 Completion Boundary Refresh

## 結論

- この時点で追うべきなのは、`historical contract を 100% 置き換えること` ではなく、`current supported runtime scope を self-host / authoritative runtime として閉じること` である。
- したがって `Phase 2. self-host / runtime 置換完了` は、`bounded full replacement` を完了条件に置く。
- current baseline では、runtime dbclasses 本体については `bootstrap_data_class_count=0`、`fallback_dbaccess_count=0`、`legacy_delegate_function_count=0`、`plain=63` / `non-plain=36`、`unclassified_non_plain_items=0` まで到達しているため、Step 2 の残りは「複雑形を無限に追うこと」ではなく「完了境界と運用を締めること」が中心である。

## いま `full replacement` と呼ぶ範囲

次を満たした状態を、current planning では `Phase 2 core done` と読む。

1. `RUNTIME-DBCLASSES` が current canonical metadata から再生成できる
2. app 自身が promoted self-generated runtime reference を authoritative source として読める
3. self-generated artifact 入力と promoted default reference 入力の full self-loop が通る
4. runtime dbclasses 本体に bootstrap copy / dbaccess fallback / broad legacy delegate が残っていない
5. current manifest 上の non-plain `data-*` がすべて sample gate へ分類済みで、未分類 complex form が無い

## `full replacement` に含めないもの

### 1. explicit exclusion として残してよいもの

- `ApacheHostSetting` / `ApacheHostSettingTemplate`
  - Apache config template / host assignment infra 用であり、runtime bundle scope の外に置く。
- `file/blob` contract
  - current canonical metadata に live row が無い。
  - `prepare()` + `bind_param("b")` + `send_long_data()` を伴う別 contract なので、current Phase 2 completion を止めず、必要が出た時だけ別 lane で扱う。

### 2. tail cleanup として後ろへ送ってよいもの

- `bootstrap_dbclasses.sh` の archive 可否
  - `last-resort staging` helper として残すかどうかの整理。
- `source_dump_path` / `bootstrap-reference` rename
  - provenance metadata migration の問題であり、runtime self-replacement completion と直結しない。
- PSR-4 / directory cosmetic cleanup
  - 以前から Phase 2 blocker ではない。

### 3. conditional work として扱うもの

- 新しく見つかる complex/new form
  - current inventory では `unclassified_non_plain_items=0` なので blocker ではない。
  - 今後 `manual-classification` が出た時だけ sample gate を足す。

## current inventory の読み替え

- simple lane
  - `direct-replacement=63`
  - current manifest / self-loop 上は未適用残件なし
- complex lane
  - `sample-test=36`
  - `Sample9-22` + `LegacyTopLevelDeclarationMigrationTest.php`
  - current inventory では representative gate が一巡しており、未分類 `0`

つまり current 状態は「complex lane が大量に残っている」ではなく、「current complex lane は sample-gated で置換済み、将来の新形だけを条件付きで追加する」段階である。

## refresh した近接タスク

1. `Phase 2 core done` の完了境界を roadmap / strategy に反映する
2. self-loop 後の `stale-reference` をどう扱うか、`latest artifact を baseline に promote する場面` と `単なる verification run` を運用上切り分ける
3. `bootstrap_dbclasses.sh` を本当に archive 候補として詰めるか、`last-resort staging` として明示残置するか判断する
4. file/blob lane は `optional unsupported contract` として別 track 化し、current Phase 2 blocker から外す

## 判断

- 継続して `literal 100% replacement` を目指す必要はない。
- ただし `Phase 2 core done` までは詰める価値がある。
- いまやるべき棚卸しは「まだ何件残っているか」よりも、「何を done と言えばよいか」を固定することだった。

## 検証に使った根拠

- `docs/internal/generated-code-strategy.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0520-runtime-replacement-two-stage-rollout.md`
- `docs/reports/2026/2026-0520-runtime-replacement-rollout-inventory.md`
- `docs/reports/2026/2026-0519-file-blob-runtime-delegate-decision.md`
- `docs/reports/2026/2026-0519-apache-host-setting-runtime-exclusion.md`
- `docs/reports/2026/2026-0521-host-only-helper-lane-classification.md`

## 次

1. roadmap 上の `Step 2 / Phase 2` を `bounded full replacement` として読み替える
2. `Phase 2 core done` と `tail cleanup` を分けて、完了判定を詰める
3. runtime replacement の今後の追加作業は、current inventory の残件掘りではなく、new complex form / real blob requirement が出た時だけ行う
