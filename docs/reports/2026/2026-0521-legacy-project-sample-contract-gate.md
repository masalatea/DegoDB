# 2026-05-21 Legacy Project Sample Contract Gate

## 概要

- `sample/legacy-projects/` を representative runtime pack の棚として使い続けるため、`sample2-8` 向けの静的 contract test を追加した。
- 新しい gate は pack README、project seed、source output seed、resource manifest の整合を current catalog API と突き合わせる。
- これにより、`sample/` category split 後も legacy-project pack 側の project-shaped metadata contract を test で固定できるようになった。

## 変更内容

- `mtool/app/sample_pack_catalog.php`
  - runtime pack 名から canonical `project_key` を引く helper を追加した。
- `tests/Integration/LegacyProjectSampleCatalogTest.php`
  - `sample2-8` の pack 順序と canonical project key を固定
  - `900_010_*_project_seed.sql` の `projects` row を検証
  - `900_020_*_source_output_seed.sql` の `source_output_key`、`source_output_list_order`、output path、binding strategy を検証
  - `sample3` / `sample5` / `sample7` の `resources/manifest.json` を file catalog loader 経由で検証
  - `sample2` / `sample4` / `sample6` / `sample8` に `resources/` が生えないことを検証
- `tests/README.md`
  - 新しい legacy-project sample contract gate を test inventory に追加した。
- `tests/Integration/README.md`
  - integration lane 一覧へ `LegacyProjectSampleCatalogTest` を追加した。
- `sample/legacy-projects/README.md`
  - category README から、この棚の静的 gate を辿れるようにした。

## 背景

- `SamplePackCatalogTest` は category、fixture、guide README、`Sample9-22` coverage を押さえるには十分だったが、`sample2-8` の project-shaped metadata までは固定していなかった。
- `legacy-projects` 側は runtime pack として seed / resources を持つため、file-based migration sample とは別の contract が必要だった。
- まずは Docker を追加で回す重い gate ではなく、現在の pack 構造を壊さないための static metadata contract を先に入れるのが妥当だった。

## 検証

- `php -l mtool/app/sample_pack_catalog.php`
  - pass
- `php -l tests/Integration/LegacyProjectSampleCatalogTest.php`
  - pass
- `make test`
  - `63 tests / 1767 assertions`
- `make mtool-self-loop-check`
  - pass

## 補足

- 今回の slice は sample/test/documentation 側の contract 追加であり、runtime generator の挙動変更は含まない。
- self-loop verification は通ったが、verification run と promote は分けて扱う運用のため、この slice では runtime reference promote は実施していない。
