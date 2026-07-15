# 2026-0715 Cleanup Pass 2: Samples And Artifacts

## 目的

947 として、今回の計画に関係する sample / artifact の初回整理 pass を実施した。

## 確認対象

- `sample35-capacitor-artifact-import`
- `sample36-shared-state-sync-server-input`
- `sample37-shared-state-sync-client-input`

## 確認内容

- README の scope / non-goal / validation 導線
- fixture schema version
- static validation script
- dependency install / native build / SDK generation を暗黙に行わない境界

## 実行した validation

```bash
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
```

## 結果

すべて pass。

- sample35: `native_project_files_checked_in=false`
- sample36: `production_runtime_generated=false`
- sample37: `source_generation=false`, `sdk_generation=false`

## 判断

今回の代表 sample / artifact には、すぐ直すべき stale reference や orphaned generated artifact は見つからなかった。

次は 948 として mtool code / script surface の整理 pass に進む。

## 状態

`DONE_REPRESENTATIVE_SAMPLE_ARTIFACT_PASS`
