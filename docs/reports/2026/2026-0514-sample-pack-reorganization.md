# Sample Pack Reorganization

## Summary

- `Project 1 = MTOOL` と `Project 2+ = sample pack` を root layout でも明確に分離した
- `docker/mariadb/config-sample-seed/mtool-core/` を `mtool/docker/mariadb/config-seed/` へ移した
- `sample/output-baselines/` を廃止し、`sample/<pack>/` 単位の durable pack へ置き換えた
- old sample scenario compose (`02_single_proxy_sample`, `03_reference_projects`) を削除した
- `mtool-single-proxy` は `sample/` や `docker/compose-scenarios/` に置かず、`tests/scenarios/` へ移した
- root `published/` と同様に、sample 側でも current raw output を repo 外し、`work/` 集約を徹底した

## New Layout

- `mtool/docker/mariadb/config-seed/`
  - `Project 1 = MTOOL` の canonical bootstrap seed
- `tests/scenarios/mtool-single-proxy/`
  - `Project 1` の single-function proxy 検証 scenario
- `sample/sample1-sql-server/` から `sample/sample7-misc/`
  - `Project 2+` 相当の durable sample pack
- `sample/<pack>/compose.yaml`
  - pack 専用 compose override
- `sample/<pack>/run.sh`
  - `up` / `down` / `reset` / `ps` / `logs` / `apply-seed`
- `sample/<pack>/seed/*.sql`
  - pack 専用 seed
- `tests/scenarios/<scenario>/compose.yaml`
  - 検証 scenario 専用 compose override

## Runtime Policy

- generated output は常に `work/source-outputs/...` または `work/scenarios/<pack>/...` に出す
- `sample/` には durable input だけを置き、raw output は置かない
- pack 固有で repo に残す curated tree が必要な場合だけ `sample/<pack>/reference/` を使う

## Follow-up

- 各 sample pack へ project-specific metadata export を段階的に足す
- `sample1..7` には project row に加えて representative な legacy `project_source_outputs` を少量 seed 済みなので、次段は custom proxy / compare output などの補助 metadata を必要に応じて足す
- historical report 内の旧 path 表記は履歴として残し、current canonical docs だけを追従させる

## Correction

- 同日中の後続確認で、sample pack へ generic `RUNTIME-DBCLASSES` row を足して `mtool/reference/dbclasses` を bootstrap copy する案は不適切と判断した
- 理由は、sample 固有 metadata ではなく MTOOL 全体の runtime bundle が `sample/<pack>/output/` に出てしまい、「sample の seed 対象だけを確認する」という目的から外れるため
- あわせて sample pack の `db-config` initdb も見直し、`mtool/docker/mariadb/config-seed/` は流し込まず、共通 schema と pack 自身の seed だけで fresh initdb する方針に修正した
- 以後の current policy は、sample pack には representative metadata だけを seed し、buildable source output は pack 固有 metadata / curated source が揃ったものだけを個別定義する

## Verification

- `sample1..7` を fresh initdb で順に再起動し、各 pack が sample 自身の `projects` / `project_source_outputs` だけで立ち上がることを確認した
- 現在の sample seed は全て `metadata-only` なので、起動だけでは `sample/<pack>/output/` に source output artifact は出ない。この挙動も期待どおりである
