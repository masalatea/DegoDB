# LanguageResource Separation / LanguageResource 分離方針

English companion:
This document records the plan for treating `LanguageResource` as an optional module rather than a permanent core dependency. It describes the containment steps, the future file-based source of truth, and the compatibility rules during incremental cutover.

## 目的

- `LanguageResource` を新 `MTOOL` core の恒久必須機能とはみなさず、optional module として扱う。
- source of truth を DB row ではなく repo 配下の JSON file tree に固定する。
- current admin は inspector-only とし、編集は AI / 人による file 直接更新へ寄せる。

## 現在地

- 2026-05-18 時点で source of truth は `MTOOL -> mtool/resources/`、sample project -> `sample/<category>/<pack>/resources/` の UTF-8 JSON file tree に確定している。
- `MTOOL` は file tree export 済みで、bulk export / validate が通る。
- `MTOOL` の `LanguageResource` group source output binding は archive 済み overlay seed に依存せず、repo 内の stable legacy PID registry から current `source_output_key` を解決する。
- runtime / generator / generated wrapper は `project_language_resource_catalog_loader.php` から `file-canonical -> reference -> empty` で catalog を解決し、DB canonical table は live read source に使わない。
- current admin route は `/projects/{project_key}/language-resources` / `/groups` / `/{resource_key}` の inspector だけを残し、create / update / delete / move / additional-group / auto-translate route は持たない。
- generated `lang_res_auto_translate_ajax.php` は current endpoint へ handoff せず、legacy JSON 互換の `NG` response で file workflow を案内する read-only bridge とする。
- local `MTOOL` では `project_language_resource_*` table / data の retirement と table drop まで完了している。DB bridge は `mtool/scripts/debug/language_resource/` 配下の migration / debug path に限定する。
- 旧 `LanguageResource` overlay compose / seed pack は current path から外し、必要なら archive から明示的に取り出す扱いにする。

## 基本方針

- default core initdb には `LanguageResource` 実データを含めない。
- overlay は legacy artifact parity や migration/debug のために明示的に取り出して使う場合だけ許す。default path には置かない。
- 正本 format は JSON に固定する。YAML や PHP array へは広げない。
- new runtime / new generator は `original-codes/` を直接参照しない。
- DB 中心の旧 editor や auto-translate AJAX UX は再実装しない。
- DB canonical write path を新規に増やさない。

## 想定する最終形

- `LanguageResource` は `MTOOL` core から分離された optional module とする。
- source of truth は `MTOOL` では `mtool/resources/`、sample project では各 `sample/<category>/<pack>/resources/` 配下の Git 管理しやすい UTF-8 JSON file tree とする。
- project root は `manifest.json`、group は `groups/<group_key>/group.json`、resource は `groups/<group_key>/resources/<resource_key>.json` に分割する。
- validator / export を通常運用の主系コマンドとする。
- current admin に残すのは browse / search / detail / groups の read-only inspector だけにする。
- auto translate が必要なら file workflow の中で AI / 人が直接更新する。
- 旧 consumer がまだ必要な間は、新 source of truth から compatibility artifact を生成する。
- DB bridge は migration / debug に限定し、通常運用では使わない。

## Phase 1: Containment

- 完了。
- `LanguageResource` を default core の必須 slice としないことを固定した。
- 旧 `lang_res*.php` 群と `ProjectSourceOutput(ClassType=LanguageResource)` の依存関係は wrapper / optional overlay へ分離した。
- canonical DB 再実装を進める前提は停止した。

## Phase 2: Optional Module 化

- 概ね完了。
- default 起動では `LanguageResource` data が無い状態を正式動作にした。
- current admin / project hub では module state を `file canonical available` / `reference fallback` / `optional off` / `blocked` として明示する。
- `ProjectSourceOutput(ClassType=LanguageResource)` bootstrap と `LanguageResource*` DBAccess metadata は core から外し、overlay でだけ戻せるようにした。

## Phase 3: AI-Friendly Source Of Truth 設計

- 完了。
- resource key / group / locale / normalization rule を JSON file tree に固定した。
- AI が編集しやすい粒度で `group.json` / `resource.json` に分割した。
- human review と merge conflict を意識して巨大 1 file を避けた。
- 自動翻訳は old AJAX UX を再現せず、file workflow 側の draft / review 前提に寄せた。

## Phase 4: Incremental Cutover

- `MTOOL` と sample pack を対象に file tree export / validate と generated wrapper bridge の cutover を進めた。
- `lang_res*.php` wrapper は browse/detail/groups inspector へ橋渡しし、write path は file workflow 案内へ寄せた。
- `lang_res_auto_translate_ajax.php` も read-only bridge として固定した。

## Phase 5: Legacy DB Editor 廃止

- `LanguageResource*` table / editor 群は core roadmap の主系から外した。
- import/export と comparison は migration/debug 用 script に限定する。
- new `MTOOL` では DB 編集 UI を持たず、resource file 編集と validation を主導線にする。

## Compatibility Wrapper Policy

- migration/debug CLI の正本 path は `mtool/scripts/debug/language_resource/*.php` とする。
- 暫定で残す top-level wrapper は `sync_project_language_resource_from_file_tree.php`、`inspect_language_resource_db_residual_rows.php`、`retire_project_language_resource_db_rows.php`、`drop_project_language_resource_db_tables.php` の 4 本に限定する。
- これらは historical handoff / local shell history 互換のための thin `require_once` wrapper であり、新しい stable doc / runbook / automation から参照を増やさない。
- `mtool/scripts/check_language_resource_db_retirement_readiness.php` は readiness 確認の current entrypoint なので top-level に残すが、compatibility wrapper 扱いはしない。
- wrapper の削除条件は、stable doc / Make target / automation 参照の解消、canonical debug path への handoff 切り替え、wrapper なしでも readiness/smoke が green であること、migration/debug 期間の終了である。

## 直近の deliverables

- `mtool/scripts/debug/language_resource/` に寄せた debug bridge を、必要なら最終的に wrapper ごと削除する。
- next pilot project に同じ file-only / tableless pattern を広げるか判断する。
- formatter / key order / validation strictness の運用ルールを必要に応じて追加する。

## 完了条件

- `MTOOL` core は `LanguageResource` なしで正式起動できる。
- optional module を読み込めば migration 期間の legacy artifact を引き続き生成できる。
- `MTOOL` が file-based source of truth へ移行し、sample catalog は sample pack 配下に置かない。
- `original-codes/` 直接参照は残らない。

## 関連文書

- [2026-0515 progress snapshot](../reports/2026/2026-0515-progress-snapshot.md)
- [2026-0515 language resource file source-of-truth plan](../reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md)
- [2026-0515 language resource debug bridge hardening](../reports/2026/2026-0515-language-resource-debug-bridge-hardening.md)
- [Language resource file catalog](../../mtool/resources/README.md)
