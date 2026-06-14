# 2026-05-21 Phase2 Core Done Checklist

## 判定

- current baseline では `Phase 2 core done = yes` と読んでよい。
- ここでの `done` は `historical contract の literal 100% replacement` ではなく、current supported runtime scope を self-host / authoritative runtime として閉じる `bounded full replacement` を指す。

## Checklist

- [x] `RUNTIME-DBCLASSES` を current canonical metadata から再生成できる
- [x] app 自身が promoted self-generated runtime reference を authoritative source として読める
- [x] self-generated artifact 入力と promoted default reference 入力の full self-loop が通る
- [x] runtime dbclasses 本体に bootstrap copy / dbaccess fallback / broad legacy delegate が残っていない
- [x] current manifest 上の non-plain `data-*` はすべて sample gate へ分類済みで、`unclassified_non_plain_items=0`
- [x] latest verified artifact は promote 済みで、runtime reference status は `up-to-date`
- [x] durable snapshot から promoted runtime reference を restore できる
- [x] `bootstrap_dbclasses.sh` は current mainline helper から外れ、archive 済み
- [x] `original-codes/` は host-side reference only のままで、Docker runtime / artifact bundle / current runtime input に戻っていない

## 現在の根拠

- promoted artifact
  - `20260521-012440-66c2a545`
- runtime reference status
  - `up-to-date`
  - `needs_promote=false`
  - `durable_recovery_ready=true`
- verification
  - `make test`
    - `54 tests / 1156 assertions`
  - `make mtool-self-loop-check`
    - pass
- rollout inventory
  - `direct-replacement=63`
  - `sample-test=36`
  - `unclassified_non_plain_items=0`
- runtime dbclasses counters
  - `bootstrap_data_class_count=0`
  - `fallback_dbaccess_count=0`
  - `legacy_delegate_function_count=0`

## この checklist に含めないもの

- `ApacheHostSetting` / `ApacheHostSettingTemplate`
  - explicit exclusion
- `file/blob` contract
  - optional unsupported track
- `source_dump_path` / `bootstrap-reference` rename
  - provenance metadata migration
- `mtool` 実処理コードの historical copy
  - current zero-copy goal の対象外
- `tests/fixtures/legacy-dbclasses/`
  - migration gate 用 input fixture として別枠

## 残り

- 残りは core blocker ではなく、tail cleanup / wording / future conditional work として扱う。
- 新しい complex form や real blob requirement が出た時だけ、sample gate か別 lane を追加する。
