# Enterprise And Personal Feature Plan

Date: 2026-06-17

## Purpose

DegoDB を企業向け・個人向けの両方で使いやすくするために、今後作っておくべき機能を優先度つきで整理する。

この計画は、現在の主線である `DB structure -> import -> Data Class -> DB Access -> Source Output` と、設計データの正本を `config_db` に置く方針を前提にする。

## Priority Definitions

| Priority | Meaning |
| --- | --- |
| P0 | 早期に土台として必要。後回しにすると導入・運用・復旧の障害になる |
| P1 | 主線が安定した後に拡張価値が高い。継続利用やチーム利用の品質を上げる |
| P2 | 中長期で有効。最初の導入より、運用成熟や市場拡張に効く |

## Product Direction

DegoDB の価値は、生成された source file そのものよりも、再生成可能な設計データを `config_db` に canonical metadata として残せることにある。

この計画では、セキュリティを最優先の product constraint として扱う。

機能追加は、便利さより先に次を満たす必要がある。

- 設計データ、DB 接続情報、generated artifact の公開範囲を明確にする。
- secret を bundle、log、generated output、public route に混ぜない。
- import / sync / publish / bundle apply の主要操作を actor 付きで追跡できる。
- unsafe な default を避け、公開・破壊・secret 依存の操作は fail closed にする。

そのため、機能拡張の優先順位は次の順で考える。

1. secret と private artifact を漏らさない。
2. 誰が何を変えたか追える。
3. 設計データを失わない。
4. import / sync / publish を安全に繰り返せる。
5. 個人ユーザーが迷わず始められる。
6. 企業ユーザーが権限・監査・秘密情報分離を前提に使える。

## Security First Policy

Security は enterprise-only の追加機能ではなく、個人利用を含む全 mode の前提にする。

## Current Status As Of 2026-06-19

SQLite persistence prerequisite is treated as closed for the current Mtool config-store scope. The next security foundation slice should not start with SSO / OIDC or a broad permission UI. Start with the smallest foundation that protects later work:

1. security regression checks for secret leakage and public exposure;
2. public route / artifact visibility policy freeze;
3. audit log schema boundary for MySQL / MariaDB and SQLite config store;
4. minimal project role model only after the audit event shape is clear.

This makes audit log foundation the first implementation-bearing slice, with security regression checks as the entry gate. Minimal project permissions follow it. API auth v2 remains a parallel policy-contract preparation, not the first security foundation implementation.

| Area | Policy |
| --- | --- |
| Secrets | password / token / populated secret file は canonical metadata、bundle、log、generated output に含めない |
| Public exposure | raw OpenAPI / artifact / generated proxy の public route は default で増やさない |
| Access control | project metadata、database source、artifact download、publish は権限境界を持つ |
| Auditability | import apply、sync、publish、bundle export/import、source registration は audit log 対象にする |
| Fail closed | secret 不足、許可外 source、visibility disabled、権限不足は silent fallback せず拒否する |
| Review before apply | schema import、bundle apply、publish などの影響が大きい操作は preview / review を先に置く |
| Least privilege | runtime read 可能な database source は capability flag で明示する |

## Minimum Required Set

最初に作るべき最低限の機能は、便利機能ではなく「安全に保存し、漏らさず、追跡できる」ための土台に限定する。

| Required | Feature | Why it is required | Minimum shape |
| --- | --- | --- | --- |
| Yes | Secret leak prevention | DB password / token が bundle、log、generated output に混ざると利用開始できない | secret export 禁止、log masking、secret sidecar、regression test |
| Yes | Public exposure control | OpenAPI、artifact、generated proxy が意図せず外へ出ると危険 | default は authenticated viewer / admin download のみ。raw public route は作らない |
| Yes | Audit log foundation | import / sync / publish / bundle apply の操作責任を追えないと企業利用が難しい | actor、project、operation、target、result、timestamp を保存 |
| Yes | Minimal project permissions | project や DB source を誰でも編集できる状態は避ける | viewer / editor / publisher / admin 程度の role |
| Yes | Config DB backup path | 設計データの正本を失うと再生成できない | MySQL / MariaDB dump と SQLite file-store backup の最低限 CLI |
| Yes | Import / apply preview boundary | DB schema 変更を無確認で canonical metadata に入れるのは危険 | preview と apply を明確に分け、destructive change を表示 |
| Yes | Safe local persistence | 個人利用でも設計データを失いやすい状態は避ける | `APP_CONFIG_STORE_DIR` SQLite 導線、reset warning、backup案内 |

この minimum set に入れないもの:

- SSO / OIDC
- approval workflow
- full rollback
- team dashboard
- local app packaging
- project templates
- AI-assisted schema review
- JSON to DB guided feature

これらは有用だが、上の minimum set が先にないと、導入後の安全性や復旧性の説明が弱くなる。

## Enterprise Features

| Priority | Feature | Reason | Notes |
| --- | --- | --- | --- |
| P0 | User / role / permission management | 企業利用では project 閲覧、編集、publish、source 登録、secret 操作を分ける必要がある | まずは project 単位の role から始める |
| P0 | Audit log | 誰が import / sync / publish / bundle import を実行したか追跡できる必要がある | CLI と UI の両方を記録対象にする |
| P0 | Secret management hardening | DB password を bundle、画面、log に混ぜないため | env reference、secret sidecar、fail-closed rule を維持する |
| P0 | Public exposure hardening | OpenAPI、artifact、generated proxy を意図せず public に出さないため | default は authenticated viewer / admin download に限定する |
| P0 | Config DB backup / restore UI and CLI | 設計データの正本が `config_db` なので、復旧導線は最重要 | MySQL / MariaDB と SQLite file store の両方を対象にする |
| P0 | Security regression checks | secret leak、public route、permission bypass を継続的に防ぐ | test / scan / smoke の最小セットを用意する |
| P1 | Import preview diff review | 既存 DB 更新時に table / column / type / stale の差分を承認しやすくする | preview と apply の境界をより明確にする |
| P1 | Project metadata bundle scope expansion | 現状 scope 外の custom proxy、HTML、language resource、security 設定も持ち運びたい | `project-core` から段階的に拡張する |
| P1 | Approval workflow | publish、bundle apply、schema import apply をレビュー制にできる | audit log と role 管理の後に作る |
| P1 | Environment separation | dev / staging / production の source output、DB source、config DB を明確に分ける | source key と publish target の混線を防ぐ |
| P1 | CI integration | metadata bundle 検証、OpenAPI 生成、proxy smoke test を CI で回せる | headless smoke の成果を再利用する |
| P2 | SSO / OIDC | 社内利用で必要になりやすい | 最初は local user / role の方が優先 |
| P2 | Change history / rollback | import や設計変更を過去版へ戻せる | audit log と snapshot が揃ってから |
| P2 | Team dashboard | project 一覧、最終 publish、失敗 job、warning を確認できる | 運用状況を見える化する |

## Personal Features

| Priority | Feature | Reason | Notes |
| --- | --- | --- | --- |
| P0 | Safe local defaults | 個人利用でも token、DB password、artifact を漏らさないため | local でも public route と secret 保存を厳しくする |
| P0 | Lightweight local persistence completion | 個人ユーザーは外部 DB なしで継続利用したい | `APP_CONFIG_STORE_DIR=work/config-store` の SQLite 導線を完成させる |
| P0 | First setup wizard | Trial / Personal Durable / External config DB を選びやすくする | 保存先と消失リスクを明示する |
| P0 | One-command existing DB import flow | source 登録、preview、apply、sync、publish までの迷いを減らす | UI lane と CLI lane の両方を用意する |
| P1 | JSON / API response to DB design draft | 個人ユーザーは既存 DB より JSON だけ持っていることが多い | `json-to-db-entrance` を実機能化する候補 |
| P1 | Generated output preview | Data Class、DB Access、OpenAPI、proxy を画面で比較確認したい | publish 前の確認にも使える |
| P1 | SQLite backup command | 個人利用では「壊した・消した」を防ぐ機能が価値になる | SQLite-safe backup / `VACUUM INTO` を検討する |
| P1 | Sample project creation | 空の状態から始めるより、小さい CRUD / API 例がある方が早い | tutorial sample を UI から作れる形にする |
| P2 | AI-assisted schema / generation review | column 名、型、relation、index 候補と、Mtool 生成可能 / 継承先個別実装 / manual runtime の切り分けを提案できる | automatic apply ではなく review 補助に留める |
| P2 | Local app packaging | Docker に慣れていない個人ユーザー向け | まずは current Docker lane を固める |
| P2 | Project templates | Blog、CRM、在庫、予約、問い合わせ管理など | sample / tutorial と重複しない形にする |

## DB Support Scope

DegoDB / Mtool 自身の設計データ保存先は、当面は 2 種類に絞る。

- DB 保存: MySQL / MariaDB。team / enterprise / shared use の標準。
- SQLite file 保存: `APP_CONFIG_STORE_DIR/config.sqlite`。personal / lightweight / local-first の標準。

ツール自身の config store では、対応 DB の種類を増やすことよりも、backup / restore、schema preflight、migration、permission / audit の保存が安定することを優先する。

一方で、ユーザー DB 側は対応範囲を広げる価値が高い。既存 DB import、schema introspection、runtime SQL generation、DB access class output、proxy / OpenAPI runtime bundle が対象になるため、MySQL / MariaDB を基準に SQLite first expansion までは代表 contract gate を整える。ただし PostgreSQL / SQL Server はこの計画では実装対象にしない。must-have feature が揃った後に、必要性と費用対効果を再評価する。

この切り分けにより、Personal Durable は SQLite file store completion を優先し、Enterprise Features は server DB 保存と security / audit を優先する。ユーザー DB の multi-dialect 対応は output capability expansion として別に進める。

## Recommended Implementation Order

1. Complete SQLite persistence prerequisite.
   - `APP_CONFIG_STORE_DIR` の SQLite config store を quickstart から backup まで通す。
   - SQLite bootstrap / migration / runtime adapter の未完了部分を閉じる。
   - file store 用 backup command を追加する。
   - local Docker volume だけで長期利用しない注意を UI / docs に出す。
   - この step は security / audit / permission の保存先を MySQL / MariaDB と SQLite の両方で設計するための前提にする。
   - 現行 admin mainline では MySQL / MariaDB と SQLite の両方を PDO repository layer で揃える。将来 self-generated DBAccess へ寄せる場合も、片方だけ先に恒久移行しない。

2. Add security foundation.
   - secret を bundle、log、generated output に出さない regression checks を追加する。
   - raw OpenAPI / artifact / generated proxy の public route policy を固定する。
   - database source の runtime read capability と fail-closed behavior を再確認する。

3. Add audit log foundation.
   - import apply、sync、publish、bundle export/import、source registration を記録する。
   - `requested_by` がある CLI から優先して記録する。
   - MySQL / MariaDB と SQLite config store の両方で使える schema にする。
   - 後続の approval workflow と rollback の土台にする。

4. Add minimal permission model.
   - project viewer / editor / publisher / admin 程度の粗い role から始める。
   - database source と secret 操作は project edit より強い権限にする。
   - CLI 実行者の扱いも audit log と合わせて整理する。

5. Strengthen import preview and apply review.
   - table / column / type / nullable / stale の差分を読みやすくする。
   - destructive に見える変更は明示確認にする。
   - preview は保存しない、apply は canonical metadata を更新する、という境界を保つ。

6. Expand project metadata bundle.
   - current `project-core` scope の後に、custom proxy、HTML、language resource、security 設定を段階的に追加する。
   - secrets は引き続き bundle 本体に含めない。
   - preview -> apply の安全な導線を維持する。

7. Turn JSON to DB entrance into a guided feature.
   - JSON file / JSON API cache / JSON config から DB design draft を作る。
   - draft は canonical import ではなく、人がレビューする pre-design artifact として扱う。
   - DB-first の主線を変えない。

8. Standardize CI and smoke output.
   - bundle validation、OpenAPI generation、proxy runtime smoke、Swagger Try It Out smoke を CI で使える形にする。
   - output は `work/` の disposable artifact として扱い、正本にはしない。

## Near-Term Milestones

### Milestone 1: SQLite Persistence Prerequisite

Goal: security / audit / permission の実装前に、設計データ保存先として SQLite file store を実用ラインまで閉じる。

Scope:

- SQLite config store quickstart
- SQLite bootstrap / migration
- SQLite runtime adapter
- SQLite backup command
- reset 前の warning

Success markers:

- `APP_CONFIG_STORE_DIR=work/config-store` だけで設計データが残る。
- MySQL / MariaDB なしでも sample project を 1 本 publish まで通せる。
- backup / restore の最低限 CLI がある。

### Milestone 2: Security Foundation

Goal: 便利な機能を増やす前に、secret、公開範囲、操作追跡の最低限を固定する。

Scope:

- secret leak regression checks
- public route / artifact visibility policy
- audit log table and repository
- import / sync / publish / bundle operation logging
- minimal project role model

Success markers:

- secret が bundle / log / generated output に混ざらないことを test で確認できる。
- raw OpenAPI / artifact は default で public 配信されない。
- 主要操作の actor、project、operation、target、result が残る。
- project ごとに閲覧者と編集者を分けられる。

### Milestone 3: Personal Durable

Goal: 個人ユーザーが外部 DB なしで DegoDB を継続利用できる。

Scope:

- SQLite config store quickstart
- SQLite backup command
- reset 前の warning
- sample project creation or one-command sample import

Success markers:

- `APP_CONFIG_STORE_DIR=work/config-store` だけで設計データが残る。
- backup / restore 手順を docs なしでも辿れる。
- sample project を 1 本 publish まで通せる。

### Milestone 4: Enterprise Safety Expansion

Goal: 企業利用に必要な安全性を approval / review まで広げる。

Scope:

- approval workflow
- import preview diff UI
- bundle import preview UI or structured CLI report
- secret handling review
- destructive change warning

Success markers:

- apply 前に変更内容を説明できる。
- stale / dropped / type changed column を見落としにくい。
- apply 後に audit log と artifact / bundle を辿れる。

## Non-Goals

- generated output を設計データの正本にすること。
- populated secrets file を Git 管理すること。
- JSON を DegoDB の primary input に変更すること。
- enterprise SSO を role / audit より先に作ること。
- security check なしで public route や raw artifact 配信を増やすこと。
- Docker volume だけの長期運用を推奨すること。

## Related Documents

- [Design Data Persistence Report](2026-0616-design-data-persistence-report.md)
- [Config Store Folder Profile First Slice](2026-0617-config-store-folder-profile-first-slice.md)
- [Lightweight SQLite Persistence Plan](2026-0617-lightweight-sqlite-persistence-plan.md)
- [Config DB Backup Rotation](2026-0617-config-db-backup-rotation.md)
- [JSON To DB Optional Entrance Roadmap](2026-0617-json-to-db-optional-entrance-roadmap.md)
- [Project Metadata Bundle](../../project-metadata-bundle.md)
- [Existing DB To Output](../../existing-db-to-output.md)
