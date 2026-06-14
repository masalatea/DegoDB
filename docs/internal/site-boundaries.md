# Site Boundaries / サイト境界

English companion:
This document explains the responsibility split between `mtool/admin/`, `mtool/lab/`, `mtool/app/`, and `mtool/reference/`. Use it to keep configuration work, experimentation, shared runtime code, and reference material from bleeding into one another.

## 目的

- `設定変更用サイト` と `実験用サイト` の責務を混在させないため、境界を明文化する。
- 今後の実装で `mtool/admin/`、`mtool/lab/`、`mtool/app/`、`mtool/reference/` に何を置くべきかの判断基準を残す。

## サイト構成

### `mtool/admin/`

- 設定変更用サイト
- control plane として扱う
- 設定、設計、権限、公開操作の UI を持つ

### `mtool/lab/`

- 実験用サイト
- runtime / lab として扱う
- 実行確認、試験、比較、検証の UI を持つ

### `mtool/app/`

- 両サイトで共有するコードを置く
- ドメインモデル
- 共通サービス
- 共通 UI 部品
- 共通設定の読み取り処理

### `mtool/reference/`

- 両サイトから読む durable reference asset を置く
- copied catalog
- canonical HTML module root
- snapshot / placeholder

## 役割分担

### `mtool/admin/` に置くもの

- 設定管理画面
- 設計編集画面
- 権限管理画面
- 公開対象の定義
- publish 実行機能
- 旧 `dev web/db/` のうち canonical な設定を更新する画面
- `Project` 基本設定
- DB Table import / metadata 管理
- Data Class 管理
- DB Access / query 設計
- Proxy / HTML / Language Resource / Source Output 設定
- ProjectUser / page security / compare setting

### `mtool/lab/` に置くもの

- 実験用の画面
- 比較・確認用の画面
- 動作確認用の API
- 試験的なワークフロー
- リセットしやすい一時機能
- Build 実行と進捗確認
- Compare Output 実行とレビュー
- Endpoint Test のような実行系 API
- canonical 設定を読み込んで検証する実験レコード

### `mtool/app/` に置くもの

- `Project` などの共通ドメイン
- 共通バリデーション
- DB アクセス共通化層
- ログ、設定、例外処理の共通基盤

### `mtool/reference/` に置くもの

- legacy import の copied reference JSON
- HTML module の canonical source tree
- placeholder や snapshot の durable asset

## データ境界

### 設定系 DB

- `db-config` を設定の正本とする
- `mtool/admin/` から更新する
- 設計、設定、権限、公開対象の保存先にする
- `mtool/lab/` からは compare output などの実行系画面で read-only 参照する

### 実験系 DB

- `db-lab` を試験・実験用の保存先とする
- `mtool/lab/` から使う
- 破棄や再作成がしやすいことを優先する

## データ更新ルール

- `mtool/lab/` から設定の正本を直接更新しない
- `mtool/admin/` が設定を変更し、必要に応じて `mtool/lab/` へ反映する
- 反映方式は今後の設計で以下から選ぶ
  - publish
  - snapshot export/import
  - read-only 参照 + lab 専用オーバーライド
- compare output 実行では `lab` が `db-config` の canonical definition を read-only 参照し、生成ファイルは `work/` 配下へ書き出す

## 実装上の注意

- 2サイトに分けても、コードを二重化しない
- 共通処理は `mtool/app/` に寄せる
- durable reference asset は `mtool/reference/` に寄せる
- `mtool/admin/` と `mtool/lab/` は UI とアプリケーションサービスの責務で分ける
- 同じ概念を別名で重複実装しない
- 旧 `ProjectPID` 前提の画面遷移はそのまま移植せず、`project_key` もしくは新しい stable key を route に使う

## 当面の運用方針

- 初期ブートストラップでは、`mtool/admin/` と `mtool/lab/` を別ポートで起動する
- 将来的に必要であれば reverse proxy を追加する
- まずは責務分離を優先し、URL の見た目は後から整える
