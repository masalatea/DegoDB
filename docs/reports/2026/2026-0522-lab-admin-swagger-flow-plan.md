# 2026-05-22 Lab/Admin Swagger Flow Plan

## Status

- first slice: `DONE`
- status updated at: `2026-05-27`
- completion basis:
  - `lab-live-schema` import source
  - `OPENAPI-JSON` source output
  - lab Swagger viewer / Try It Out
  - external named DB source
  - docs / smoke / contract coverage
- note:
  - この plan の縦切り主線は完了した。
  - OpenAPI public alias key / raw delivery は後続 hardening policy に切り出し済みで、この plan の DONE 判定には含めない。

## 結論

- 次の主線は `Lab DB schema を変更 -> Admin で import -> canonical metadata を更新 -> code output -> Lab で runtime 実験 -> Swagger で API 実験` で進めるのがよい。
- `設定` と `canonical metadata` の責務は引き続き `admin` に寄せる。
- `lab` は `runtime 実験 / compare / review / Swagger UI` の場として整理する。
- 最初の実装 slice は、`lab-live-schema` を import source にした current lane をベースに、`single-function proxy` から `OpenAPI` を自動生成する縦切りにする。

## 現状 (計画立案時点)

この section は plan 立案時点の gap 記録であり、現在の repo 状態では mainline 実装済みである。

### すでにできていること

- `db-lab` をブラウザで触るための `lab-db-ui` は追加済み
- admin 側の table import source として `lab-live-schema` が使える
- canonical metadata の保存先は引き続き `db-config`
- `Data Class`、`DB Access`、proxy 系の source output 生成は current lane がある
- `lab` 側には endpoint test / compare output / build の実験系 route がある

### まだ無いもの

- `OpenAPI / Swagger` の generator
- `lab` 側で generated spec をそのまま閲覧する Swagger UI
- `lab_db` 以外の任意 host DB を named source として扱う一般化

## 責務分離の方針

### Admin

- DB import source の定義
- `dbtable` / `dbtablecolumns` の canonical metadata 管理
- `Data Class` / `DB Access` / `Source Output` の設計と生成
- どの proxy を公開対象にするかの canonical 判定
- 将来の external DB source 登録

### Lab

- `db-lab` 上での schema 実験
- 生成済み runtime / proxy の動作確認
- endpoint test / compare output / review
- generated `openapi.json` を読む Swagger UI

## 来週の実装目標

来週中に、少なくとも次の 1 本を green path にする。

1. `lab-db-ui` で `db-lab` の schema を変更する
2. admin 側で `lab-live-schema` を import する
3. `Data Class` / `DB Access` を sync する
4. proxy source output を生成する
5. 同じ canonical metadata から `openapi.json` を生成する
6. `lab` で Swagger UI を開き、生成済み endpoint をそのまま叩ける

## 実装順

### [DONE] Phase 1. OpenAPI を source output の 1 種類として追加する

- `ProjectSourceOutput` の class / strategy に `OpenAPI` 用の選択肢を追加する
- first slice は `single-function proxy` だけを対象にする
- 出力はまず `openapi.json` のみでよい
- `custom proxy` は後続に回す

### [DONE] Phase 2. OpenAPI generator を最小実装する

- canonical metadata から path / method / operationId / requestBody / response schema を組み立てる
- request / response schema は、まず `Data Class` と proxy metadata から読める範囲で最小限に出す
- auth や error response は first slice では簡易表現でよい
- 出力先は既存の source output artifact flow に乗せる

### [DONE] Phase 3. Lab に Swagger viewer を追加する

- `lab` 側に Swagger UI 用 route を追加する
- 生成物の最新 `openapi.json` を指定して表示する
- assets は CDN 依存を避け、repo 管理か container 同梱で完結させる
- `lab` 側の説明文は「設定変更の場」ではなく「生成済み API の実験場」として揃える

### [DONE] Phase 4. import source を named DB source へ一般化する

- 現在の `db` / `lab_db` の hardcoded source を、named source model の first step として整理する
- 次段で `external named source` を足せる構造へ寄せる
- one-off DSN 引数ではなく、admin で管理できる named source を正面導線にする

### [DONE] Phase 5. external DB source を追加する

- 接続先 host / port / dbname / user / password / driver を named source として保持する
- read-only 前提の preflight check を入れる
- import 実行時は named source を選ぶだけにする
- first slice では MySQL 系だけに絞ってよい

## 日別の進め方

### 月曜

- OpenAPI source output の metadata model を追加する
- generator の空実装と artifact wiring を入れる
- `lab` 側に Swagger page の route と空 page を用意する

### 火曜

- `single-function proxy` から `openapi.json` を生成する
- sample か `MTOOL` の 1 project で spec を出せるところまで通す
- PHPUnit contract test を追加する

### 水曜

- `lab` の Swagger UI から generated spec を読ませる
- 実 endpoint に対して Try it out できるところまで通す
- docs の task flow を更新する

### 木曜

- named DB source model の整理に着手する
- `lab_db` を hardcoded 例から `named source` 例へ寄せる
- external source を 1 種類足すための設計を固める

### 金曜

- external source の first slice を実装する
- import preflight / error message / docs を仕上げる
- `make test` と必要な smoke を通して締める

## 受け入れ条件

- `make up` 後に `lab-db-ui` で schema 変更を試せる
- admin 側で `lab-live-schema` を import source として選べる
- generated proxy と同じ metadata から `openapi.json` が出る
- `lab` 側の Swagger UI で generated spec を開ける
- Swagger UI から lab runtime endpoint を実際に叩ける
- docs に「Lab DB を変える -> Admin import -> output -> Lab Swagger」の最短手順がある

## 非目標

- `lab` 側で canonical metadata を直接編集すること
- first slice で `custom proxy` まで完全対応すること
- first slice で全 DB 製品に一般対応すること
- generated `openapi.json` を手編集前提にすること

## 注意点

- `lab` に見えている設定系 UI は増やさず、設定責務は `admin` に寄せる
- Swagger は `lab` の補助実験 UI として置き、canonical source of truth はあくまで `admin` metadata にする
- external import は `lab_db` の特例を増やすのではなく、named source model へ一般化してから足す

## 最初の着手順

来週の最初の 1 本としては、次の順がよい。

1. `OpenAPI source output` の metadata と generator 追加
2. `lab` の Swagger viewer 追加
3. `lab-live-schema` current lane で end-to-end 実証
4. その後に external named DB source へ拡張

この順なら、今すでにある `lab-live-schema` の導線を使って、小さい縦切りで価値を出しつつ、後段の外部 DB 取り込みにもそのままつながる。
