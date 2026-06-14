# 2026-05-18 Self-Generated Bootstrap Readiness

## 2026-05-19 update

- full self-loop は通過済み。artifact 入力と promoted default reference 入力の両方で green になった。
- `mtool/scripts/promote_runtime_reference.php` と `make promote-runtime-reference` を追加し、artifact `20260519-010720-0d539855` を `mtool/reference/dbclasses/` へ promote 済み。
- `mtool/app/config.php` は `APP_GENERATED_DBCLASSES_MODE` 未指定時に `self-generated-reference:canonical-dbaccess-partial-sql-regenerated` を自動判定する。
- 詳細は `docs/reports/2026/2026-0519-self-generated-runtime-reference-promotion.md` を参照。
- 以下の本文は 2026-05-18 時点の readiness memo を残したもので、`まだ終わっていないこと` などの節は当日時点の記録である。

## 今日やったこと

- `mtool/app/generated_catalog.php`
  - self-generated runtime bundle の `base/` / `_base/` / `_wrappers/` を解釈し、logical method/property/class catalog を返すようにした
  - dbaccess method excerpt も backing base file を使うようにした
- `mtool/app/project_output_runtime_generator.php`
  - `app_project_output_runtime_bootstrap_data_file_info()` を self-generated bundle 対応にした
  - `generated-wrapper-base` / `generated-layered-stub` の 2 layout を識別できるようにした
  - already-generated wrapper/base の non-plain data class は canonical side として pass-through 扱いできるようにした
- `mtool/app/project_output_service.php`
  - already-layered runtime entry を build-plan で再 layering せず passthrough するようにした
- `tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - no-DB fixture で self-generated runtime resolver を固定した

## 確認結果

- `make test`
  - pass
  - `6 tests, 89 assertions`
- latest generated bundle を `APP_REFERENCE_ROOT` に向けた no-DB focused check
  - `dbaccess method_candidate_count = 626`
  - `generated-wrapper-base = 84`
  - `generated-layered-stub = 17`
  - `plain_data_candidate_count = 64`
  - `non_plain_data_candidate_count = 37`

## まだ終わっていないこと

- full self-loop を `APP_REFERENCE_ROOT=<latest generated bundle>` で完走させる
  - 現在の shell では `db-config` 接続が無く、`live-schema` import が止まる
- self-generated bundle を durable reference へ promote する script / 手順を作る
- `mtool/reference/dbclasses/` の実置換はまだ未着手
- `mtool/app/config.php` の default switch もまだ未着手

## 次の一手

1. DB 付き環境で `APP_REFERENCE_ROOT=<latest generated bundle> php mtool/scripts/check_mtool_self_loop.php --requested-by=self-generated-bootstrap-check` を実行する
2. self-loop が通れば、generated bundle を `mtool/reference/dbclasses/` へ promote する script を追加する
3. promote 後に `mtool/app/config.php` の default reader source を self-generated 側へ切り替える

## 明日再開用メモ

- latest artifact root
  - `work/artifacts/source-outputs/MTOOL/20260518-071523-06754a82/bundle/mtool-source-output-runtime-dbclasses-20260518-071523-06754a82/mtool`
- focused check はこの bundle に対して通っている
- self-loop full run は parser ではなく DB 接続環境不足で止まっている

## 再開プロンプト

```text
MTOOL の self-generated bootstrap 置換作業を再開してください。

前回までに、self-generated runtime bundle の `base/` / `_base/` / `_wrappers/` を bootstrap reader 側で読めるようにしました。`generated_catalog.php`、`project_output_runtime_generator.php`、`project_output_service.php` を更新済みで、`make test` は 6 tests / 89 assertions で通っています。no-DB focused check では latest generated bundle を `APP_REFERENCE_ROOT` に向けて `dbaccess method_candidate_count=626`、`generated-wrapper-base=84`、`generated-layered-stub=17`、`plain_data_candidate_count=64`、`non_plain_data_candidate_count=37` を確認済みです。

今日は次を進めてください。
1. DB が見える環境で `APP_REFERENCE_ROOT=work/artifacts/source-outputs/MTOOL/20260518-071523-06754a82/bundle/mtool-source-output-runtime-dbclasses-20260518-071523-06754a82/mtool php mtool/scripts/check_mtool_self_loop.php --requested-by=self-generated-bootstrap-check` を実行し、full self-loop が通るか確認する。
2. 通ったら、generated bundle を durable reference へ promote する script を追加する。
3. その後に `mtool/reference/dbclasses/` の置換計画と `mtool/app/config.php` の default switch まで進める。

更新済みレポート:
- docs/reports/2026/2026-0518-self-generated-bootstrap-readiness.md
- docs/reports/2026/2026-0518-mtool-runtime-layout-audit.md
- docs/reports/2026/2026-0518-mtool-runtime-wrapper-base-migration-plan.md
```
