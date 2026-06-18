# 2026-06-19 Instruction Driven Demo Sample Plan

## Status

- status: `DONE`
- target lane: `sample/tutorials/sample18+`
- first candidate pack: `sample18-mini-task-board-demo` (`DONE`)
- purpose: AI-generated virtual user prompts から、実際に動く small demo sample を作る

## Summary

`sample01` から `sample17` までは、DegoDB の機能を simple-to-complex に確認する tutorial lane として整っている。

2026-06-19 に first candidate の `sample18-mini-task-board-demo` を追加し、MySQL / MariaDB config store lane と SQLite config store lane の両方で runtime test を通した。

次の段階では、機能別教材だけでなく、「ユーザーがこう指示したら、DegoDB でどこまで動くものを作れるか」をそのまま sample にする lane を追加する。

この plan は、実ユーザーの元アイデアとは別に、AI 側で仮想的な依頼 prompt を作り、その prompt から runtime pack を実装し、途中で出た問題を generator / runtime / seed / docs へフィードバックするための作業計画である。

## Goal

- 実際の利用シーンに近い prompt を sample の出発点として残す。
- user の生の試行錯誤ログではなく、AI が sample として読みやすい依頼 prompt に再構成する。
- その prompt から作った schema / metadata / source outputs / runtime checks を runtime pack として固定する。
- 生成物は `reference/` に actual output として置き、imitation output は置かない。
- 問題が出た場合は sample 側でごまかさず、必要な runtime / generator / test / docs の改善候補として記録する。

## Non-Goals

- `original-codes/` を runtime input に使わない。
- sample を marketing demo にしない。
- 大きな業務アプリを最初から作らない。
- `sample17` までの tutorial lane を置き換えない。
- ユーザーの本来の別アイデアをこの plan で確定扱いしない。

## Proposed Lane Shape

instruction-driven demo sample は、既存の tutorial lane の続きとして `sample18+` に置く。

各 pack は通常の runtime pack contract に従う。

- `README.md`
- `compose.yaml`
- `run.sh`
- `seed/`
- `reference/`
- `tests/Integration/SampleNN...Test.php`
- `mtool/scripts/check_sampleNN_..._outputs.php`
- `make sampleNN-pack-runtime-test`

通常の tutorial と違い、README には最初に仮想ユーザー prompt を残す。

## Demo Prompt Contract

各 demo sample の README には、少なくとも次を記録する。

- `Original Prompt`
  - AI が仮想的に作った、または実ユーザーの意図を sample 向けに整理したユーザー依頼。
- `Interpreted Scope`
  - DegoDB の sample として採用する範囲。
- `Out of Scope`
  - この demo では作らない機能。
- `Generated / Curated Boundary`
  - DegoDB が publish した actual output と、手で seed した metadata / module source の境界。
- `Feedback Notes`
  - 実装中に見つかった不足、違和感、後続改善候補。

## Curation Policy

sample に残す prompt は、実際の会話ログや試行錯誤をそのまま転記しない。

AI 側で次のように整理する。

- 依頼文は、初見の読者が目的を理解できる短い prompt にまとめる。
- 途中の迷い、言い直し、実装調整は `Interpreted Scope` / `Out of Scope` / `Feedback Notes` に分ける。
- sample の README は成功した最短経路を主に説明し、失敗や発見は後続改善メモとして残す。
- runtime / generator の問題は sample の仕様に見せかけず、修正対象または known gap として扱う。
- AI が補った前提は、できるだけ明示し、ユーザーが最初からそう言ったようには書かない。

## First Candidate: sample18-mini-task-board-demo

### Virtual Prompt

```text
小さなチーム用のタスクボードを作りたいです。

タスクにはタイトル、説明、状態、担当者、期限、優先度があり、
一覧では未完了タスクを期限順に見たいです。

できれば、タスクの詳細、作成、更新、完了もできるようにしてください。
HTML の簡単な画面と、外部から使える API 仕様も欲しいです。
まずは Docker でそのまま動く小さいデモにしてください。
```

### Interpreted Scope

- project key: `SAMPLE18`
- main table: `TaskCard`
- optional lookup tables:
  - `TaskStatus`
  - `TaskPriority`
- DBAccess functions:
  - `TaskCard.GetTaskCardList`
  - `TaskCard.GetTaskCard`
  - `TaskCard.InsertTaskCard`
  - `TaskCard.UpdateTaskCard`
  - `TaskCard.CompleteTaskCard`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`
- web-lab demo page:
  - `/samples/sample18-task-board`

### Acceptance Criteria

- `make sample18-pack-runtime-test` が fresh runtime で通る。
- `DATACLASS-PHP` と `DBACCESS-PHP` は actual generated reference と一致する。
- `HTML-PAGE` は sample18 用 HTML module source から publish され、reference と一致する。
- `OPENAPI-JSON` は list / detail / write 系の API surface を含む。
- `web-lab` で起動後に触れる simple task board page を持つ。
- HTTP smoke で login、page 表示、task 作成、task 編集が通る。
- README に virtual prompt、解釈した scope、manual flow、生成物の置き場を書く。

### Likely Gaps To Watch

- write 系 DBAccess を OpenAPI output にどこまで自然に出せるか。
- HTML output が CRUD demo として十分に見えるか。
- task status / priority を lookup table にした時の generated DataClass の読みやすさ。
- list filter / sort / page metadata が demo らしい実用性を持つか。

## Follow-Up Candidates

### sample19-inventory-request-demo

仮想 prompt:

```text
社内の備品申請を管理したいです。
申請者、品目、数量、理由、承認状態、承認者、申請日を持たせて、
未承認一覧、承認済み一覧、品目別の申請数が見たいです。
```

狙い:

- approval workflow 風の small business app
- filter / aggregate / status transition
- report-oriented DBAccess の demo

### sample20-content-publishing-demo

仮想 prompt:

```text
小さな記事公開管理を作りたいです。
記事、カテゴリ、公開状態、公開日時を管理して、
公開済み記事の一覧ページと API 仕様を出したいです。
```

狙い:

- content / category / publish state
- HTML output が見えやすい
- OpenAPI と HTML の multi-output demo

## Implementation Phases

### Phase 1. Plan Only

- この計画書を追加する。
- 既存 `docs/sample-tutorial-roadmap.md` はまだ変更しない。
- `sample18` の scope を実装可能な最小幅へ寄せる。

### Phase 2. sample18 First Slice

- `[DONE]` pack skeleton を作る。
- `[DONE]` seed で project / table / DBAccess / source outputs を定義する。
- `[DONE]` runtime publish を通す。
- `[DONE]` actual output を `reference/` に固定する。
- `[DONE]` checker と PHPUnit integration test を追加する。

### Phase 3. Feedback Fixes

- sample18 実装中に出た runtime / generator / seed / docs の問題を修正する。
- 修正範囲が広い場合は sample18 から分け、別 report にする。
- sample18 README に feedback notes を残す。

### Phase 4. Roadmap Promotion

- `[DONE]` sample18 が通ったら `docs/sample-tutorial-roadmap.md` と `sample/tutorials/README.md` に追加する。
- 必要に応じて `docs/study/` に demo guide を追加する。
- sample19 / sample20 は sample18 の結果を見て選ぶ。

## Implementation Result

- added pack: `sample/tutorials/sample18-mini-task-board-demo`
- canonical targets:
  - `make sample18-pack-runtime-test`
  - `make sample18-pack-runtime-test-sqlite`
  - `make sample18-http-runtime-smoke`
- verification:
  - `make sample18-pack-runtime-test` -> `OK (1 test, 7 assertions)`
  - `make sample18-pack-runtime-test-sqlite` -> `OK (1 test, 7 assertions)`
  - `make sample18-http-runtime-smoke` -> login / page render / task create / task update `ok=true`
- web-lab page:
  - `http://127.0.0.1:18272/samples/sample18-task-board`
- Swagger page:
  - `http://127.0.0.1:18272/runs/swagger/SAMPLE18?source_output_key=OPENAPI-JSON`
- OpenAPI paths:
  - `/proxyserver-TaskCard-GetTaskCardList.php`
  - `/proxyserver-TaskCard-GetTaskCard.php`
  - `/proxyserver-TaskCard-InsertTaskCard.php`
  - `/proxyserver-TaskCard-UpdateTaskCard.php`
  - `/proxyserver-TaskCard-CompleteTaskCard.php`

## Done Definition

- `sample18-mini-task-board-demo` が runtime pack として再現できる。
- canonical target `make sample18-pack-runtime-test` が通る。
- virtual prompt から artifact までの trace が README / report に残る。
- actual generated output だけが reference として保存される。
- sample18 の実装で見えた課題が未対応のまま埋もれていない。
