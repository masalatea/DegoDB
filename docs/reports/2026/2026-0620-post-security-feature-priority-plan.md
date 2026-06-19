# 2026-06-20 Post-Security Feature Priority Plan

## Status

- status: `CURRENT PRIORITY UPDATE`
- created: `2026-06-20 JST`
- purpose: security / auth / SSO first slice 後に、次へ進める候補の優先順を見直す

## Summary

直近の security / auth / SSO work は、以下を current scope で完了扱いにする。

- security foundation / audit foundation
- API auth v2 policy contract
- `static-bearer` generated runtime verification
- SSO / OIDC first slice
- Mtool admin/lab route authorization I/F baseline
- generated runtime security baseline

次の実装順は、Mtool admin/lab の broad authorization enforcement へ進むのではなく、生成物と metadata portability の主機能側を優先する。

Recommended order:

1. Custom proxy metadata bundle coverage. Done in first slice.
2. Generated API `oidc-jwt-bearer` runtime verification.
3. PostgreSQL output support for user DB / generated output.
4. Mtool namespace migration.
5. Mtool admin/lab route authorization hardening re-planning gate.

## Why This Order

### 1. Custom Proxy Metadata Bundle Coverage

Priority: `DONE_FIRST_SLICE`

これは新しい auth 方式を作る作業ではない。既存の custom proxy metadata を project metadata bundle の移行対象として扱えるようにする作業である。

Current reading:

- custom proxy 自体は `project_custom_proxies.auth_policy_version` / `auth_policy_json` を持てる。
- generated runtime security baseline の project-core bundle coverage は、DBAccess single-function proxy auth policy refs を主対象にしていた。
- 2026-06-20 first slice で、project-core bundle に `custom_proxies` section を追加し、custom proxy 本体、step、target source output binding、auth policy refs を export / preview / apply できるようにした。

Why first:

- custom proxy は既存の主機能であり、sample14 でも tutorial 化済み。
- auth policy を持てるのに bundle migration で落ちる可能性があると、project portability の説明が弱くなる。
- ただし、すでに push 済みの generated runtime security baseline commit へ amend するほど同一主題ではない。別コミットとして扱う。

First slice scope:

- project metadata bundle scope に custom proxy 本体を段階的に追加する。
- custom proxy step / target source output binding / auth policy refs を preview / apply の対象にする。
- populated secret / token / password values は引き続き bundle に含めない。
- preview で create / update / destructive risk を見えるようにする。
- sample14 or focused contract test で export -> preview -> apply -> generated custom proxy runtime の metadata continuity を確認する。

First slice result:

- `custom-proxies.json` is now part of project-core bundle output.
- custom proxy auth policy JSON uses the same generated runtime auth policy validator as DBAccess single-function proxy metadata.
- secret-like populated fields in custom proxy auth policy JSON fail bundle preview.
- sample15 and sample26 project metadata bundle references include the new empty custom proxy section.
- Contract coverage is in `ProjectMetadataBundleContractTest`.

Out of scope:

- custom proxy runtime auth の新方式追加。
- production-grade approval workflow。
- HTML / LanguageResource / broader security setting bundle expansion。

### 2. Generated API `oidc-jwt-bearer` Runtime Verification

Priority: `NEXT AFTER CUSTOM PROXY BUNDLE`

これは Mtool admin login の SSO ではなく、Mtool が生成した API / proxy runtime が OIDC provider の JWT bearer token を検証する作業である。

Current reading:

- `oidc-jwt-bearer` policy JSON shape は `I/F_DONE`。
- issuer / audience / discovery URL or JWKS URI / required claims の contract は用意済み。
- executable generated proxy resolver は、JWT verification 実装までは `oidc-jwt-bearer` を実行可能 auth として扱わない。

Why second:

- 先日の方針として、生成側の security は対応する。
- SSO first slice と同じ IdP 周辺の話だが、対象は generated API runtime であり Mtool admin/lab authorization とは別。
- `static-bearer` の次の generated runtime security expansion として自然。

First slice scope:

- generated proxy runtime で bearer JWT を fail-closed に検証する。
- issuer / audience / required claims を検証する。
- JWKS or discovery metadata 取得と key selection の最小実装を決める。
- missing / malformed / invalid signature / wrong issuer / wrong audience / missing required claim / valid token の contract test を追加する。
- OpenAPI security scheme と sample/reference output を必要最小限で更新する。

Out of scope:

- Mtool admin/lab route broad enforcement。
- IdP administration UI。
- SCIM / invitation / member lifecycle。
- OAuth2 client credentials。

### 3. PostgreSQL Output Support

Priority: `NEXT MAJOR FEATURE`

これは Mtool 自身の config store を PostgreSQL 対応にする話ではない。対象は user DB / generated output 側である。

Current reading:

- MySQL / MariaDB は user DB mainline。
- SQLite は first expansion として sample06 / sample08 / sample09 / sample10 の contract compare が完了済み。
- PostgreSQL / SQL Server は parked だったが、次の output capability expansion として PostgreSQL を先に再評価する価値がある。

Why after generated security:

- PostgreSQL support は大きい機能価値がある。
- ただし dialect, introspection, type normalization, generated SQL, runtime adapter, fixture, contract compare をまたぐため、custom proxy bundle coverage や generated API auth runtime より実装範囲が広い。

First slice scope:

- user DB contract framework に PostgreSQL lane を追加する。
- first sample は `sample10-dbaccess-mini-crud-flow` を候補にする。
- schema introspection / type normalization / SQL placeholder / insert update delete / select の最小差分を実装する。
- raw output の byte-for-byte parity ではなく normalized contract manifest で比較する。

Expansion order:

1. sample10 CRUD。
2. sample06 filter / sort / pagination。
3. sample08 join read model。
4. sample09 aggregate report。

Out of scope:

- Mtool config store PostgreSQL support。
- SQL Server support。
- vendor-specific advanced SQL, full-text search, JSON path, geospatial, stored procedures。

### 4. Mtool Namespace Migration

Priority: `LATER CLEANUP`

Namespace migration は価値があるが、主機能より後に切り出す。

Current reading:

- Composer usage alone does not require a repo-wide Mtool namespace migration.
- third-party classes are already namespaced where needed.
- repo-wide namespace migration is a broad cleanup and should not be mixed with PostgreSQL or generated runtime security behavior.

First slice, if chosen:

- namespace target boundary を決める。
- `mtool/app`, `mtool/shared`, `mtool/scripts` のどこから始めるかを決める。
- generated output compatibility と old include path compatibility を先に整理する。

### 5. Mtool Admin/Lab Route Authorization Hardening Re-Planning Gate

Priority: `PARKED / REPLAN_BEFORE_IMPLEMENTATION`

これは SSO first slice の続きではあるが、今すぐ broad enforcement へ進めない。

Current reading:

- SSO / OIDC first slice is done.
- external IdP group role mapping is done.
- `project_route_authorization.php` I/F baseline is done.
- broad Mtool admin/lab route enforcement is intentionally parked.

Why parked:

- これは generated API security ではなく Mtool admin/lab 自身の route authorization。
- member / group lifecycle, IdP administration, legacy membership compatibility と絡みやすい。
- 今は主機能側の portability / generated runtime security / DB output support を優先する。

Guardrail:

- namespace migration の次に、自動的に route authorization hardening へ進まない。
- 実装前に必ず scope / effort / risk / expected value を再見積もりする。
- その時点で PostgreSQL follow-up、generated runtime security follow-up、bundle scope expansion、sample/tutorial maintenance など、より優先すべき主機能があればそちらを先にする。
- route authorization hardening は「次に必ずやる 5 番目の実装」ではなく、「候補として再計画してから採否を決める gate」として扱う。

Resume condition:

- actual deployment need が出たとき。
- または source output publish/download, database source management, secret-backed config operation など、1 cluster 単位で enforcement したいとき。
- 再計画で、最小 cluster、必要な audit event、UI/CLI 影響、test/smoke 範囲、legacy membership fallback の扱いが明確になったとき。

## Commit Boundaries

- Custom proxy bundle coverage は、generated runtime security baseline への amend ではなく別コミットにする。
- Generated API `oidc-jwt-bearer` runtime verification は、Mtool admin/lab authorization hardening と混ぜない。
- PostgreSQL output support は user DB / generated output dialect work として独立させる。
- Namespace migration は behavior change と混ぜず、cleanup / structure commit として扱う。
- Do not proceed from namespace migration directly into Mtool admin/lab route authorization hardening without a fresh re-planning document or explicit priority decision.

## Source Of Truth Links

- Current plan inventory: `docs/reports/2026/2026-0619-plan-inventory.md`
- Generated runtime security: `docs/internal/generated-runtime-security-plan.md`
- Authorization hardening: `docs/internal/authorization-hardening-plan.md`
- User DB dialect roadmap: `docs/reports/2026/2026-0617-user-db-multidb-dialect-roadmap.md`
- Enterprise / personal feature plan: `docs/reports/2026/2026-0617-enterprise-personal-feature-plan.md`
