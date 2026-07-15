# 2026-0715 Multi-Pass Cleanup Plan

## Purpose / 目的

This history note records the decision to add a multi-pass cleanup phase after the shared-state sync bundle/checklist lane and the external consumer integration lane reach a clean stopping point.

この履歴は、shared-state sync bundle/checklist lane と外部 consumer 連携 lane が区切りまで進んだ後、複数周回の全体整理 phase を追加する判断を記録する。

## Why / 理由

Many files have accumulated across docs, history files, samples, Mtool scripts, tests, fixtures, and validation evidence. A single cleanup pass is likely to miss stale links, inconsistent naming, duplicated guidance, or unclear responsibility boundaries.

docs、履歴ファイル、sample、Mtool script、test、fixture、validation evidence に多くのファイルが蓄積している。1回だけの整理では、古い link、不整合な命名、重複した説明、責務境界の曖昧さを見落としやすい。

Therefore the cleanup should be a deliberate multi-pass review.

そのため、整理は意図的に複数周回で行う。

## Planned Passes / 計画するpass

| Pass | Area / 対象 | Goal / 目的 |
| --- | --- | --- |
| 1 | Docs and navigation / docs・導線 | Check permanent docs, README/index files, current plans, history links, stale wording, duplicated guidance, and Japanese/English balance. |
| 2 | Samples and artifacts / sample・artifact | Check samples, fixtures, generated artifact examples, validation scripts, stale references, inconsistent naming, and orphaned demo files. |
| 3 | Mtool code and script surface / mtool code・script surface | Check apps, scripts, shared helpers, CLI names, artifact emitters, duplicate entry points, and abandoned spike code. |
| 4 | Tests and validation evidence / test・validation evidence | Check tests, Make targets, smoke commands, validation reports, and proof matrix links. |
| 5 | Final consistency and history archive / 最終整合・履歴archive | Re-run consistency checks, archive accumulated DONE items, review branch/commit state, and prepare final cleanup checkpoint. |

## Cleanup Rules / 整理ルール

- Do not delete history. Move completed plan/detail history to dated history files.
- Do not remove files just because they are old; first classify them as active, historical, reference, experimental, or obsolete.
- Preserve representative evidence and validation commands.
- Avoid behavior changes during cleanup unless the issue is clearly mechanical and low risk.
- Keep `docs/current-plans.md` small.
- Record ambiguous items instead of guessing.

## Current Plan Update / current plan 更新

`docs/current-plans.md` now adds:

- #945 Whole-repo cleanup pass plan;
- #946 Cleanup pass 1: docs and navigation;
- #947 Cleanup pass 2: samples and artifacts;
- #948 Cleanup pass 3: mtool code and script surface;
- #949 Cleanup pass 4: tests and validation evidence;
- #950 Cleanup pass 5: final consistency and history archive.

## Status / 状態

`PLANNED_AFTER_EXTERNAL_CONSUMER_MILESTONE`
