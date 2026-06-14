# Auth Architecture / 認証構成

English companion:
This document explains the current authentication architecture for the rewrite. It separates the local stub setup from future production-ready authentication, and it records the present session, principal, role, route-guard, and CSRF boundaries.

## 目的

- 新実装で認証をどう切り出すかを、旧実装との差分が見える形で残す。
- 現段階のローカル用スタブ認証と、将来の本番向け認証統合を混同しないようにする。

## 現在の方針

- いまは Docker ローカルで確実に起動確認できることを優先する。
- そのため、認証は `mtool/app/auth.php` に置いたスタブ認証を使う。
- 旧実装の共有ログイン基盤にはまだ接続しない。

## 現在の構成

### session

- `mtool/app/session.php` が session を開始する。
- `admin` と `lab` は別の session 名を持てる。
- cookie は `HttpOnly` と `SameSite=Lax` を付ける。

### principal

- ログイン後の principal は `$_SESSION['app_principal']` に保持する。
- 現在は以下の最小情報だけを持つ。
  - `id`
  - `display_name`
  - `roles`
  - `auth_source`
  - `site`

### role

- 現在は session principal の `roles` をそのまま使う。
- 最小 role は以下。
  - `admin`
  - `config`
  - `lab`
- 旧実装の `ProjectUser` の多数の read/write flag はまだ再現しない。

### credential source

- 認証情報は `.env` から注入する。
- `admin` と `lab` で別のユーザー名、パスワード、roles を持てる。
- `make env` は `.env.example` を元に local password をランダム生成する。
- repo 側の password fallback は最小限にし、起動時は `.env` を前提にする。

### route guard

- `mtool/app/router.php` が route 名を返す。
- `mtool/app/middleware.php` が route ごとの認証要否を判定する。
- 現時点では `/dashboard`、`/projects`、`/experiments` を保護ルートにしている。
- さらに page renderer 側で `admin` / `lab` のサイト境界を判定する。
- `projects`
  - `admin` または `config` role を要求
- `experiments`
  - `lab` または `admin` role を要求
- いまは一覧、追加、更新で同じ role 判定を使う
- read / write の分離はまだ行わない

### CSRF

- `mtool/app/csrf.php` が token を session に保持する。
- `POST /login`
- `POST /logout`

上記の POST は token 一致を前提にする。

## 旧実装との関係

旧実装は `original-codes/docs/auth-and-authorization.md` に整理した通り、次の要素を分離して持っていた。

- 共有ログイン基盤との連携
- login token cookie
- メール認証状態
- `ProjectUser` ベースのモジュール権限
- ページ単位セキュリティ
- 内部管理者オーバーライド

新実装ではこれらを一気に再現せず、まず以下の境界を先に固定する。

- identity provider との境界
- session snapshot の保持方法
- route / page policy の差し込み位置
- role / permission の評価位置

## 今後の拡張ポイント

### Phase 1

- スタブ認証から外部 identity provider 連携へ置換
- principal にメール認証状態や組織属性を追加

### Phase 2

- `Project` 単位の所属と role を評価する層を追加
- `admin` と `lab` で要求 role を変えられるようにする

### Phase 3

- 旧実装の `ProjectSecurityForEachPage` 相当を route policy として再設計する
- UI 単位ではなく controller / service 単位で認可を定義する

## 現段階でやらないこと

- 旧 cookie 名との互換吸収
- BASIC 認証フォールバック
- メール認証ワークフロー
- 自動ページ台帳登録

これらは旧実装の事情を引きずりやすいため、新実装では必要性を再評価してから取り込む。
