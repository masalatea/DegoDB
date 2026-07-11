# 2026-0711 No-Code Readiness Status

Status: `CURRENT_STATUS`

## Summary

現時点の No Code 化は、AI が UI を直接生成する段階から一段戻し、Mtool が UI 判断に必要な操作可否 metadata を JSON contract として安定して出せるようにする段階にある。

これまでの作業で、Sample18 に対する readiness metadata の shape contract は固定済み。つまり、操作ごとに「押せる / 押せない」「押せない理由」「対応する route boundary」「executor config 由来の enablement / dependency 情報」「failure reason」を表現する JSON の型を定義し、fixture・docs・focused PHPUnit assertion で確認できる状態にした。

## Current Interpretation

要点は以下。

- 以前は、AI が UI を直接それらしく生成する方向に寄っていた。
- 今は、UI 生成の前提として、Mtool が操作可否を説明できる JSON metadata を作る方向に寄せている。
- #703 では、その JSON metadata の仕様、つまり readiness metadata contract を固定した。
- 次の #704 では、Sample18 の既存 executor config と route contract から、副作用なしで readiness snapshot を組み立てる helper を追加する。
- その後、screen-definition / runtime-preview / HTML marker に metadata を carry-through し、JSON/DOM contract test で正しさを検証する。
- そこまで固めてから、No Code 的なコード生成や UI 有効化ロジックを検討する。

## Current Plan Position

Current active position:

- #703 `Sample18 readiness metadata shape contract`: `DONE`
- #704 `Sample18 readiness snapshot helper first slice`: `ACTIVE_NEXT`

Short-term sequence:

1. Side-effect-free helper で readiness snapshot を生成する。
2. 生成した readiness metadata を screen-definition metadata に載せる。
3. runtime-preview.json と HTML `data-*` marker にも載せる。
4. PHPUnit の JSON/DOM contract test で、disabled / enabled candidate / route missing / failure reason を検証する。
5. 必要最小限の browser smoke で、実ブラウザ表示だけ確認する。
6. Readiness lane を閉じた後、commit stack checkpoint を入れて意味単位で整理する。
7. real execution や generated default enablement の前に、transaction complete gate を入れる。
8. Transaction complete gate 後にもう一度 commit stack checkpoint を入れ、execution safety boundary を review しやすくする。
9. その後、No Code UI 生成や server-generated availability overlay の次段階を検討する。

## Why This Matters

この順序により、No Code UI は「見た目を先に作る」のではなく、「UI が信頼できる判断材料を持つ」状態から積み上げられる。

特に、操作失敗は「全部成功か全部失敗か」に寄せる方針で扱う。現時点で transaction 対応が完全でなくても、UI / route / metadata の設計としては、一つでも失敗したら即座に失敗として扱う fail-closed 方針を前提にする。後続で transaction 対応を強化する。

Transaction complete gate は、read-only readiness metadata が UI / JSON / HTML marker へ carry-through された後、server-generated overlay や real guarded execution smoke の前に置く。ここでは、rollback on any failed step、commit-unknown recovery、idempotency / audit consistency、DB-backed Sample18 coverage を確認し、mutating action が all-success-or-all-failure の response contract を持つことを gate にする。

また、readiness lane closure 後と transaction complete gate 後の両方で commit stack checkpoint を入れる。前者は readiness metadata / test / docs の意味単位整理、後者は execution safety boundary の reviewability を高めるための整理とする。

## Long-Term Direction

長期方針は以下。

1. Sample UI を No Code 化する。
2. Sample UI の No Code 化を通じて、No Code に不足している capability と test boundary を洗い出す。
3. 十分に育ったら Mtool 自身の UI flow を No Code 化する。
4. その後、AI が資料や data を構造正規化し、関連性や ontology 的解析へ進む。
5. 最終的には、資料だけを渡すと AI が構造を解析・正規化し、No Code UI を即時生成し、ユーザーが構造的に閲覧・質問できる状態を目指す。

## Verification Status

Latest known verification for #703:

- `jq empty sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: OK
- `make test`: OK, with existing incomplete/skipped/risky notice
- `git diff --check`: OK

Push has not been performed.
