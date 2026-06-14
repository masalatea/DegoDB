# 2026-05-14 Project Security First Slice

この文書は first slice 時点の記録であり、同日後続の [2026-0514-project-security-second-slice.md](2026-0514-project-security-second-slice.md) により current 状態としては superseded されている。

## 目的

- `Project Security / Host Assignment` を broad scope の未着手 major area から外し、first slice を current route 化する。
- ただし schema purity より機能移植を優先し、今ある canonical schema で成立する範囲から進める。

## 今回入れたもの

- `admin:/projects/{project_key}/security`
  - security / host assignment の current hub。
- `admin:/projects/{project_key}/security/users`
  - `project_memberships` を current source of truth にした membership editor。
- `admin:/projects/{project_key}/security/pages`
  - 旧 page security の責務と blocked point を明示する partial route。
- `admin:/projects/{project_key}/host-assignments`
  - 旧 host assignment の責務と blocked point を明示する partial route。

## users slice の扱い

- 旧 `ProjectUser` の 16 個の read/write bit は、この slice では再現しない。
- current canonical scope は `owner / admin / member` の project membership のみ。
- 保存時は `project_memberships` を「1 login = 1 canonical row」へ正規化する。
- owner は `projects.owner_login_id` を source of truth とし、`security/users` では編集対象にしない。

## pages / host assignment を partial にした理由

- page security は旧実装でも `SERVER_NAME` / `SCRIPT_NAME` ベースの global policy で、project row に閉じない。
- host assignment は `ProjectHostSetting` だけで成立せず、`ApacheHostSetting` / `ApacheSetting` / `Server` など infra settings と一体。
- current `001_schema.sql` にはこれらの canonical table がまだ無い。

## 併せて直したこと

- `projects` 一覧と detail の `member_count` は `COUNT(DISTINCT pm.login_id)` に変更した。
  - `project_memberships` が将来複数 role row を持っても、人数表示が過大にならないようにした。
- project hub の `Security / Host Assignment` は `available-partial` に更新した。
- admin module table に `open` action を追加し、available route へ直接遷移できるようにした。

## 残る差分

- page security の canonical model は未実装。
- host assignment の canonical schema は未実装。
- 旧 `ProjectUser` の per-feature bit と current auth/route policy の接続は未実装。

## 次の候補

1. page security を route / service policy へどう落とすかを固定する。
2. host assignment を system / infra settings slice として切り出す。
3. 旧 `ProjectUser` bit を page policy / capability policy にどう吸収するかを決める。
