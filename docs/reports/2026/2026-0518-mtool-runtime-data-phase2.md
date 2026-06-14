# 2026-05-18 Mtool Runtime Data Phase2

## 変更内容

- `MTOOL / RUNTIME-DBCLASSES` の final artifact について、canonical plain DTO と判定できる `data-*` を `root wrapper + base/data-*Base.php` へ移行した。
- runtime 固有の custom layer は維持し、plain DTO の root wrapper も `_runtime_loader.php` 経由で `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-*.php` override を解決する。
- non-plain bootstrap data は引き続き `_base/` / `_wrappers/` / `_runtime_loader.php` の transition-state layout に残す。

## 実装ポイント

- `mtool/app/project_output_service.php`
  - canonical plain DTO file を検出した場合は、runtime bundle publish 時に root `data-*.php` wrapper と `base/data-*Base.php` を出力するようにした。
  - non-plain data は従来どおり root stub + `_base/` + `_wrappers/` を使う。
  - generated custom layer README も mixed layout を説明する文面へ更新した。
- `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/README.md`
  - extension layer 利用者向けに、plain DTO は `base/data-*Base.php`、non-plain data は transition-state layout、という current contract を明記した。
- `mtool/reference/mtool-self-loop-expected-output.json`
  - representative runtime file digest baseline を更新し、plain DTO の wrapper/base pair と、transition-state data の代表 `Project` 3点セットを監視対象にした。

## 検証

- `make test`
  - sample1 の template-file migration 後も reference digest と PHPUnit integration test が通ることを確認した。
- `php -l mtool/app/project_output_service.php`
- `make mtool-self-loop-check`
  - latest self-loop artifact `20260518-043002-a0caef83`
  - `generation_summary` は既存値を維持
  - representative runtime file digest baseline も更新後に一致

## 現在の layout

```text
mtool/dbclasses/
  data-CompareOutputSearchCache.php
  data-Project.php
  base/data-CompareOutputSearchCacheBase.php
  _base/data-Project.php
  _wrappers/data-Project.php
  _runtime_loader.php
```

## 次

- remaining `37` non-plain data class を、sample pack で旧構造を actual tool output として再現できるものから分離する。
- `A`: `Base` 化しやすい bootstrap-heavy class を sample で contract 固定後に runtime へ持ち込む。
- `B`: multi-class / top-level helper / default property を含む class は skip reason を manifest で追いながら据え置く。
