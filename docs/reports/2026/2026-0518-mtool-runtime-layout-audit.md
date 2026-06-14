# 2026-05-18 Mtool Runtime Layout Audit

## 2026-05-19 update

- full self-loop は翌 2026-05-19 に self-generated artifact 入力で通過し、その後 `mtool/reference/dbclasses/` も promoted self-generated tree へ置き換えた。
- current default mode は `self-generated-reference:canonical-dbaccess-partial-sql-regenerated` であり、promoted default reference 入力でも `make mtool-self-loop-check` が通る。
- 以下の本文は 2026-05-18 時点の audit snapshot を残したもので、`未完了` 欄は当日時点の保留事項である。

## 結論

- `MTOOL / RUNTIME-DBCLASSES` の final runtime bundle は、2026-05-18 時点で hybrid layout である。
- `dbaccess-*` は `root wrapper + base/dbaccess-*Base.php` へ移行済みで、data は `generated-wrapper-base` と `generated-layered-stub` の 2 系統が共存している。
- self-generated bundle を再び bootstrap 入力として読むための reader/parser 側は今回の修正で layer-aware になった。
- ただし full self-loop を `APP_REFERENCE_ROOT=<latest generated bundle>` で end-to-end 完走させる最終確認は、この shell では `db-config` 接続が無いため未実施である。

## current layout

- `dbaccess-*`
  - root entry: `mtool/dbclasses/dbaccess-*.php`
  - generated base: `mtool/dbclasses/base/dbaccess-*Base.php`
  - copied legacy support: `mtool/dbclasses/_support/legacy-dbaccess/*.php`
- `data-*`
  - `generated-wrapper-base` 84 class
    - root entry: `mtool/dbclasses/data-*.php`
    - generated base: `mtool/dbclasses/base/data-*Base.php`
  - `generated-layered-stub` 17 class
    - root stub: `mtool/dbclasses/data-*.php`
    - backing base: `mtool/dbclasses/_base/data-*.php`
    - backing wrapper: `mtool/dbclasses/_wrappers/data-*.php`
- shared helper
  - `mtool/dbclasses/_runtime_loader.php`

## 実施内容

- `mtool/app/generated_catalog.php`
  - top-level `data-*.php` / `dbaccess-*.php` から `base/` / `_base/` / `_wrappers/` を辿り、logical class/property/method catalog を返すようにした。
  - dbaccess method excerpt も backing base file を読む。
- `mtool/app/project_output_runtime_generator.php`
  - `app_project_output_runtime_bootstrap_data_file_info()` が self-generated runtime bundle を解析し、`generated-wrapper-base` / `generated-layered-stub` を識別できるようにした。
  - already-generated wrapper/base の non-plain data class は canonical side としてカウントしつつ pass-through できるようにした。
- `mtool/app/project_output_service.php`
  - `app_project_output_runtime_build_plan()` が already-layered runtime entry を再 layering せず passthrough するようにした。

## focused check

`latest generated bundle -> APP_REFERENCE_ROOT` で DB 非依存の focused check を実施し、次を確認した。

- `dbaccess method_candidate_count = 626`
- `data layout`
  - `generated-wrapper-base = 84`
  - `generated-layered-stub = 17`
- `plain_data_candidate_count = 64`
- `non_plain_data_candidate_count = 37`

この値は current self-loop baseline の aggregate count と整合する。

## テスト

- `make test`
  - `6 tests, 89 assertions`
- 追加
  - `tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - self-generated runtime bundle の resolver / build-plan を no-DB fixture で固定した。

## 未完了

- `APP_REFERENCE_ROOT=<latest generated bundle>` で `mtool/scripts/check_mtool_self_loop.php` を full run すること
  - 現在の shell では `db-config` 接続が無く、`live-schema` import が `db-config` 名前解決失敗で止まる
- self-generated bundle を durable reference へ promote する CLI / 手順の追加
- `mtool/app/config.php` の default `APP_GENERATED_DBCLASSES_MODE` / loader source を、reader 側の確認後に切り替えること
