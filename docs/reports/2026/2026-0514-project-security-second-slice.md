# 2026-05-14 Project Security Second Slice

## 目的

- `Project Security / Host Assignment` を「route はあるが blocked 理由だけ見える」状態から進める。
- Phase 1 の機能移植を優先し、page security と host assignment に current canonical landing zone を与える。
- 最終的な route policy / infra catalog への split は後段に回し、まず current route で設定できる状態を作る。

## 今回入れたもの

- `project_page_security_policies`
  - `SERVER_NAME + SCRIPT_NAME` を保持する current policy row。
- `project_page_security_policy_capabilities`
  - 旧 `SecurityType` を normalized capability list として保持する child row。
- `project_host_assignments`
  - 旧画面の visible 4 列
    - `apache_setting_name`
    - `server_local_name`
    - `virtual_host_name`
    - `template_name`
  - を denormalized landing zone として保持する current row。
- `admin:/projects/{project_key}/security/pages`
  - page security policy の一覧、追加、更新、削除。
- `admin:/projects/{project_key}/host-assignments`
  - host assignment の一覧、追加、更新、削除。

## 設計判断

- page security
  - 旧 `ProjectSecurityForEachPage` / `ProjectSecurityForEachPageDetails` を、そのまま 16 列へ戻さず、policy row + capability row へ正規化した。
  - route policy への最終吸収は後段だが、Phase 1 の source of truth としてはこの table 群を使う。
- host assignment
  - 本来は `ProjectHostSetting` だけで完結せず、`ApacheSetting` / `ApacheHostSetting` / `Server` などの infra domain と結びつく。
  - ただし機能移植優先のため、まずは visible 4 列を current canonical row として持つ。
  - infra catalog への split は後段で行う。

## 旧実装との差分

- page security は「global policy を project 配下から触る convenience route」として置いている。
- host assignment は infra catalog をまだ持たず、Phase 1 では denormalized row を編集する。
- つまり、どちらも final form ではないが、「旧画面に戻らないと変更できない」状態は外せた。

## 確認

- `php -l`
  - `mtool/app/project_page_security_repository.php`
  - `mtool/app/project_page_security_repository_pdo.php`
  - `mtool/app/project_host_assignment_repository.php`
  - `mtool/app/project_host_assignment_repository_pdo.php`
  - `mtool/app/project_security_pages_page.php`
  - `mtool/app/project_host_assignments_page.php`
  - `mtool/app/project_security_route_common.php`
  - `mtool/app/project_security_page.php`
  - `mtool/app/project_detail_page.php`
  - すべて syntax error なし。
- running `db-config`
  - `docker/mariadb/config-initdb/001_schema.sql` を再適用し、
    - `project_page_security_policies`
    - `project_page_security_policy_capabilities`
    - `project_host_assignments`
    の作成を確認した。

## まだ残ること

1. page security の current table から、実際の current route / service policy へどう投影するかを固定する。
2. host assignment の denormalized row を、`Server` / `ApacheSetting` / `ApacheHostSettingTemplate` 等の infra catalog へどう split するかを固定する。
3. 旧 `ProjectUser` の per-feature bit と current auth/capability policy の接続を決める。
