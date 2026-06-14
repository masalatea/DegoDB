# 2026-05-20 Runtime Replacement Rollout Inventory

## 結論

- runtime replacement の 2 段 rollout を、current manifest に対して機械的に棚卸しできる状態にした。
- `mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only` で、non-plain `data-*` 36 件を lane / sample gate 単位で出せる。
- 2026-05-20 時点の current inventory では `unclassified_non_plain_items=0` であり、sample gate 未割り当ての複雑形は無い。

## 追加したもの

- `mtool/app/project_output_runtime_generator.php`
  - `source_name -> rollout lane / sample gate` の helper を追加した。
  - plain DTO は `direct-replacement`、non-plain は `sample-test` を基本とし、未分類 source は `manual-classification` で検出できるようにした。
- `mtool/scripts/show_runtime_replacement_rollout.php`
  - current `runtime-generation-manifest.json` を読み、`data_generation_items` に lane / gate 情報を重ねて JSON で出す CLI を追加した。
- `tests/Integration/RuntimeReplacementRolloutLaneTest.php`
  - representative source の lane 判定と、current manifest 上の non-plain item がすべて sample gate に分類済みであることを固定した。

## current inventory

- total `data-*`: `99`
- non-plain: `36`
- unclassified non-plain: `0`

lane counts:

- `companion-declarations`: `13`
- `default-property`: `2`
- `method-and-enum`: `12`
- `method-only`: `5`
- `top-level-declaration`: `3`
- `wrapper-property-method`: `1`

## 使い方

```bash
php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
```

必要なら `--manifest=...` で別 artifact の manifest にも同じ分類を当てられる。

## 確認結果

- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
  - pass
  - `unclassified_non_plain_items=0`
- `make test`
  - `37 tests / 656 assertions`
- `make mtool-self-loop-check`
  - pass

## 含意

- simple lane は引き続き direct replacement でよい。
- complex/new form は、今後新しい source が出たときだけ `manual-classification` として検出される。
- その時点で sample を追加し、lane を明示 mapping に加える流れで広げられる。
