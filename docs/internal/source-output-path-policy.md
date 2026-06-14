# Source Output Path Policy / source output path 方針

English companion:
This policy explains where durable source trees, generated outputs, staging areas, and disposable work files belong. Read it when you need to decide whether a file should live under `mtool/`, `sample/`, `tests/`, `work/`, or the runtime reference tree.

## 目的

- durable な source tree と runtime output の責務を root レベルで明確に分ける
- `Project 1 = MTOOL` の core と `Project 2+` 相当の sample pack を混在させない
- disposable runtime file は sample pack を含めて `work/` へ集約し、`make clean` でまとめて消せるようにする

## Root の責務

### `mtool/`

- current runtime code の正本
- `mtool/admin/`、`mtool/lab/`、`mtool/app/`、`mtool/extensions/`、`mtool/reference/`、`mtool/scripts/` を置く
- runtime に編入するコードは、`mtool/` 配下の適切な PSR-4 指向パスへ直接置く

### `docker/`

- repo 全体で共有する base Docker asset root
- `docker/php-apache/`、`docker/mariadb/config-initdb/`、`docker/mariadb/lab-initdb/` だけを置く
- project / scenario 固有の compose override や seed は置かない
- `make clean` では削除しない

### `mtool/reference/`

- durable な runtime reference root
- promoted runtime reference、runtime-reference snapshot、canonical html module、legacy snapshot / placeholder など再利用前提の source を置く
- legacy catalog JSON に残る `source_dump_path` は provenance 用 metadata として保持してよく、current runtime input path としては扱わない
- `make clean` では削除しない

### `mtool/extensions/`

- durable な companion / custom layer root
- `mtool/extensions/{project_key}/{source_output_key}/` を規約 path とする
- helper、wrapper、collaborator、補足コードを置く
- raw generated output は混ぜない
- `make clean` では削除しない

### `mtool/resources/`

- durable な file-based resource catalog root for core / `MTOOL`
- `mtool/resources/` を `MTOOL` catalog の規約 path とする
- `LanguageResource` の file source-of-truth 候補 JSON tree を置く
- `manifest.json` の `origin.source_dump_path` も provenance 表示用 metadata としてのみ残し、runtime がその path を直接開く前提にはしない
- `manifest.json`、`groups/<group_key>/group.json`、`groups/<group_key>/resources/<resource_key>.json` を基本形とする
- generated artifact や runtime scratch は混ぜない
- `make clean` では削除しない

### `sample/`

- sample pack の durable input root
- `sample/<category>/<pack>/README.md`、`compose.yaml`、`run.sh`、`seed/`、必要なら `reference/` を置く
- sample 固有の language resource file catalog は `sample/<category>/<pack>/resources/` に置く
- `sample1-*` 以降が `Project 2+` 相当の pack
- disposable runtime state と generated output はここへ出さない
- `make clean` では削除しない

### `mtool/docker/compose/`

- `Project 1 = MTOOL` の core compose override root
- `01_mtool.compose.yaml` を置く
- current 起動系に必要な compose override だけを置く
- `make clean` では削除しない

### `tests/`

- 検証資産の root
- Docker を使う検証 scenario は `tests/scenarios/` に置く
- `make clean` では削除しない

### `work/`

- root runtime と共通 disposable file の root
- current raw output、runtime source staging、artifact 履歴、job history、compare workspace、scenario runtime state、sample pack の disposable runtime state、scratch / log を置く
- historical に `bootstrap_dbclasses.sh` が作っていた host-side staged legacy recovery copy (`work/legacy-recovery/dbclasses/`) は archive 済み helper 導線だけが使う path とし、current mainline path policy には含めない
- ad hoc な scratch、確認用 log、単発の作業中間物も `work/tmp/` 配下へ置く
- 消されてもアプリが再起動・再生成で復旧できる前提で使う
- `make clean` で丸ごと削除する

## Source Output の既定ルール

- `project_source_outputs.source_output_dir` の default は全 project 共通で `work/source-outputs/{project_key}/{source_output_key}` とする
- `project_source_outputs.source_temp_output_dir` の default は共通で `work/staging/source-outputs/{project_key}/{source_output_key}` とする
- sample pack に repo へ残す curated reference tree が必要な場合だけ `sample/<category>/<pack>/reference/<source_output_key>/` を使う
- `sample/<category>/<pack>/reference/` に置く file は、実ツールが出した actual output か、出所を確認できる legacy curated source に限定し、AI / 手書きの imitation output は置かない
- ただし generic な `RUNTIME-DBCLASSES` を sample pack へ seed して `mtool/reference/dbclasses` の runtime reference tree をそのまま publish しない
- runtime に編入する場合は、その時点で `mtool/` 配下の適切な PSR-4 指向パスへ直接移す
- 既存 DB の `project_source_outputs` を現行 path policy に揃えるときは `mtool/docker/mariadb/config-seed/032_refresh_source_output_directory_policy.sql` を適用する

## 運用ルール

- repo 内の disposable file は sample pack 実行分を含めて `work/` を使う
- durable reference は `mtool/reference/` に固定し、一時出力や履歴を混ぜない
- current raw output は全 project で `work/source-outputs/` を使う
- sample pack は durable input だけを持ち、再生成できる file は `work/` 側へ出す
- custom 修正は `mtool/extensions/` だけに置く

## merge / promote 時の見方

- day-to-day の current raw output は `work/source-outputs/` から見る
- 人手または Codex の追加実装は `mtool/extensions/{project_key}/{source_output_key}/` から重ねる
- sample pack 固有の input は `sample/<category>/<pack>/seed/` と `sample/<category>/<pack>/reference/` から見る
- sample pack 実行で生じた disposable file は `work/sample-packs/<pack>/` と `work/source-outputs/` から見る
- repo に残す curated reference tree がある場合だけ `sample/<category>/<pack>/reference/` を参照する
- `work/artifacts/source-outputs/{project_key}/{artifact_key}/` は監査用 snapshot であり、日常の merge 先としては扱わない
- `MTOOL / RUNTIME-DBCLASSES` を durable reference へ反映するときは、`work/artifacts/.../bundle/.../mtool/dbclasses` を `make promote-runtime-reference` または `php mtool/scripts/promote_runtime_reference.php --artifact-key=...` で `mtool/reference/dbclasses/` へ昇格する
- promote 時には同じ tree を `mtool/reference/runtime-reference-snapshots/{project_key}/{source_output_key}/{artifact_key}/` にも保存し、`make clean` 後は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` で recover する
- `work/artifacts/...` 自体は disposable だが、runtime reference manifest が指している promoted artifact は上記 snapshot により `work/` 外から recover できる
- `make bootstrap-dbclasses` は archived alias として fail fast し、current supported recovery を `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` へ寄せる
- authoritative runtime reference の repair / rollback は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` だけを使い、legacy copy の direct overwrite は retire した
- 旧 `make bootstrap-dbclasses-runtime-reference` と archive 済み `bootstrap_dbclasses` helper の runtime-reference overwrite 導線は retired guidance として失敗させる
- `bootstrap_dbclasses.sh` 自体は archived historical helper であり、current supported recovery flow には含めない。もし host-side quarantined full-tree legacy copy が再び必要になった場合だけ、archive から明示的に復帰させて使う
- `work/source-outputs/MTOOL/RUNTIME-DBCLASSES` は publish 済み disposable root であり、durable reference の source of truth にはしない
- runtime に編入するコードは、その時点の namespace / package 設計に合わせて `mtool/` 配下へ直接移す

## Code 上の基準点

- path 組み立ての共通 helper は `mtool/app/runtime_storage_paths.php` に集約する
- source output の default path policy は `mtool/app/project_output_service.php` に集約する
- durable input は `reference` helper、disposable output は `work` helper を使い分ける
- service / page 側で path 規約を使うときは、helper または helper を呼ぶ wrapper 関数を使い、文字列の直書きを増やさない
- `Makefile` の `clean` target は `work/` を完全削除し、旧 `generated/` 残骸も除去する。`mtool/reference/`、`mtool/extensions/`、`sample/` は保持する

## 関連ディレクトリ

### `mtool/docker/mariadb/config-seed/`

- `Project 1 = MTOOL` の canonical bootstrap seed
- `mtool/docker/compose/01_mtool.compose.yaml` が fresh initdb 時に staged copy する
- `LanguageResource` 旧 overlay seed は current path に置かず、必要なら archive から明示的に取り出す

### `mtool/archive/` にある `LanguageResource` legacy archive

- `LanguageResource` 旧 overlay compose / seed pack の archive 置き場
- current runtime / generator / test の通常導線からは参照しない
- migration/debug でどうしても必要な場合だけ、明示的に展開して使う

### `mtool/archive/` にある `bootstrap_dbclasses` archive

- `bootstrap_dbclasses.sh` の archive 置き場
- current runtime / generator / test / Makefile の通常導線からは参照しない
- host-side quarantined full-tree legacy copy が本当に必要になった時だけ、明示的に復帰させて使う

### `tests/scenarios/mtool-single-proxy/`

- `Project 1` 補助の single-function proxy 検証 scenario
- `README.md`、`compose.yaml`、`run.sh`、`seed/` を持つ

### `sample/<category>/<pack>/seed/`

- sample pack ごとの optional seed
- sample pack の fresh initdb では、共通 schema (`docker/mariadb/config-initdb/`) に対してこの seed だけを staged copy する
- `Project 1 = MTOOL` の `mtool/docker/mariadb/config-seed/` は sample pack initdb へ混ぜない
- 既存環境へ後から足す場合だけ、pack compose または seed apply script 経由で適用する

### `sample/<category>/<pack>/compose.yaml`

- root `compose.yaml` に重ねる pack 専用 override
- `APP_WORK_ROOT=/var/www/work/sample-packs/<pack>` を切り替え、共通 schema と pack seed だけを initdb へ staged copy する
- writable mount は base compose の `/var/www/work` を使う

### `work/sample-packs/<pack>/`

- sample pack 専用の disposable runtime root
- `runtime-sources/`、`artifacts/`、`compare-output/`、`job-history/` など pack 実行で生じる file をここへ出す
- `make clean` で消える前提の scratch / snapshot / staging だけを置く

### `sample/<category>/<pack>/reference/`

- pack 専用の curated reference tree
- actual output または provenance を確認できる legacy curated source だけを置く
- current raw output ではない

### `work/runtime-sources/`

- html / proxy / legacy-directory-mirror generator が作る disposable runtime source staging root
- `runtime_source_relative_path` の logical key を実体 path へ解決した結果をここへ materialize する

### `work/source-outputs/`

- current raw output root
- build / write-output action はまずここを更新する

### `work/artifacts/source-outputs/`

- Source Output artifact 履歴の保管場所
- `manifest.json` と `tar.gz` bundle を保持する

### `work/staging/project-output-runtime/`

- runtime-dbclasses を artifact 化する直前の staging root

### `work/staging/source-outputs/`

- source output ごとの一時出力 path metadata の既定先

### `work/compare-output/`

- compare output definition が参照する compare root / output storage
- compare definition の `storage_base_path` や additional path の base として使う

### `work/compare-output-assets/`

- Compare Output template / ignore rule の project override
- file-based asset override であり、空なら built-in default を使う

### `work/tmp/`

- ad hoc な scratch、確認用 log、単発の作業中間物の保存先
- root `tmp/` は使わず、必要な一時 file はここへ寄せる

### `work/job-history/compare-output/`

- Compare Output job manifest と snapshot の保存先

### `work/job-history/build/`

- build job manifest の保存先

### `work/job-history/endpoint-test/`

- endpoint test job manifest と snapshot の保存先

### `work/scenarios/<scenario>/`

- root runtime / test scenario 側で使う scenario 専用 disposable runtime state
- sample pack はここではなく `work/sample-packs/<pack>/` を使う
- artifact 履歴、staging、compare workspace、compare-output-assets、job history などを scenario-local に切り分ける

## Version Control での扱い

- `mtool/`、`mtool/extensions/`、`sample/` は repo で持つ durable tree として扱い、`.gitignore` では除外しない
- `work/` は disposable root として `.gitignore` に入れる
- `make clean` は `work/` を完全削除する
