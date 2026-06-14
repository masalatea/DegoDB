# 2026-05-21 Runtime Reference Promote Operation Split

This report preserves the pre-promote point-in-time on 2026-05-21 when runtime reference status was still `stale-reference`.
Later the same day, the verification artifact `20260521-012440-66c2a545` was promoted and current status returned to `up-to-date`.
See `2026-0521-post-archive-verification-and-promotion.md` for the post-promote state.

## 結論

- `self-loop verification run` と `promote candidate run` は別物として扱う。
- `php mtool/scripts/show_runtime_reference_status.php --require-current` の `stale-reference` は、`latest artifact != promoted runtime reference` を示す運用状態であり、単独では runtime breakage や self-loop failure を意味しない。
- promote は `green verification` に加えて、`その artifact を default authoritative runtime reference へ採用する意思決定` が揃った時だけ実行する。

## 背景

- 2026-05-20 の post-sample22 baseline では、promoted runtime reference は artifact `20260520-073256-1bc9b18f` で `up-to-date` だった。
- 2026-05-21 に `make test` と `make mtool-self-loop-check` を回した結果、latest artifact `20260520-234638-15018a34` ができた。
- ただしこの run は docs / planning refresh 後の verification であり、default runtime reference を更新する採用 run ではなかった。
- そのため current status は `stale-reference`、`needs_promote=true`、`durable_recovery_ready=true` になっている。

## run の分け方

### 1. verification run

- 目的
  - current code / docs / generator / sample gate / self-loop が壊れていないか確認する。
- 典型コマンド
  - `make test`
  - `make mtool-self-loop-check`
  - `php mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php`
- 読み方
  - new artifact ができても、自動では promote しない。
  - `stale-reference` は「未採用の newer artifact がある」という意味であり、この run 自体の green / red 判定とは別に読む。

### 2. promote candidate run

- 目的
  - verified artifact を `mtool/reference/dbclasses/` の default authoritative baseline へ昇格する。
- 前提
  1. 必要な test / sample gate / self-loop が green
  2. promote 対象の `artifact_key` が明確
  3. この artifact を default runtime reference に進める判断が済んでいる
- 実行
  - `php mtool/scripts/promote_runtime_reference.php --artifact-key=...`
- 完了条件
  - `php mtool/scripts/show_runtime_reference_status.php --require-current` が `up-to-date`

## 判断基準

- docs / prompt / roadmap の整理後に verification だけ回した run
  - promote しない
- transient な debug / inspection / rerun で latest artifact を更新した run
  - promote しない
- runtime bundle / rollout baseline / default reference を前進させたい run
  - promote する
- promoted reference の repair / rollback
  - `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` を使う
  - `bootstrap_dbclasses.sh` で stale-reference を解消しない

## 運用上の読み替え

- `stale-reference`
  - `latest artifact` がまだ採用されていない
  - Phase 2 失敗とは限らない
- `up-to-date`
  - latest artifact が promoted reference と一致している
- `durable_recovery_ready=true`
  - 現 promoted reference は snapshot-backed restore が可能

## 次

1. current artifact `20260520-234638-15018a34` は、採用判断が入るまでは verification artifact として扱う
2. session close 時には、`promote する` か `verification-only stale-reference を残す` かを handoff に明記する
3. Phase 2 の残りは、artifact を増やすことではなく、どの run が promote candidate なのかをぶらさず運用できるようにすること
