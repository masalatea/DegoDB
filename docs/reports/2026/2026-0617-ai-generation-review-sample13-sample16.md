# 2026-06-17 AI Generation Review: sample13 / sample16

## Purpose

AI-assisted generation review contract を、実際の generated output に適用した first pass。

対象は HTTP / browser smoke 済みの representative outputs に絞る。

- `sample13-openapi-api-surface`
  - `OPENAPI-JSON`
  - `API-PROXY-SERVER`
- `sample16-authenticated-proxy`
  - `AUTH-PROXY-SERVER`

目的は、Mtool generator で出すべき範囲、generator option として持つべき範囲、継承先 class / custom runtime に逃がすべき範囲を、MySQL / MariaDB と SQLite の両方を見ながら棚卸しすることである。

## Review Summary

| Project | Candidate | Classification | Confidence | Recommended Implementation |
| --- | --- | --- | --- | --- |
| SAMPLE13 | `ApiTask.GetApiTask` DBAccess + proxy endpoint | `generated` | high | single-table `SELECTSINGLE`、scalar where、direct result は generator-owned。 |
| SAMPLE13 | `ApiTask.GetApiTaskList` DBAccess + proxy endpoint | `generated_with_options` | high | single-table `SELECTLIST` は generator-owned。limit parameter、sort order、example value は option / metadata validation として扱う。 |
| SAMPLE13 | `OPENAPI-JSON` artifact | `generated_with_options` | high | OpenAPI surface は generator-owned。proxy base URL、server URL、db source selector behavior は deploy/runtime option。 |
| SAMPLE13 | NoSecurity proxy runtime | `generated` | medium-high | tutorial / internal viewer 用の no-security endpoint は生成可。本番公開時は explicit policy review が必要。 |
| SAMPLE16 | `AuthTask.GetAuthTask` DBAccess + proxy endpoint | `generated` | high | single-table `SELECTSINGLE` と ProjectToken auth は generator-owned。 |
| SAMPLE16 | ProjectToken auth policy | `generated` | high | `MTOOL_PROXY_PROJECT_TOKEN` + payload `TOKEN` の fail-closed behavior は standard generated runtime scope。 |
| SAMPLE16 | custom authorization hook | `inherited_custom` | high | `authorizeByGetFunction()` / `authorizeByLoginCookieToken()` / `beforeHandle()` / `afterHandle()` は wrapper class に残す。 |
| SAMPLE13 / SAMPLE16 | Blob / file / vendor-specific behavior | `manual_runtime` or `inherited_custom` | medium | 現対象には含まれない。生成器で無理に一般化しない。 |

## SAMPLE13 Review

### Inputs Reviewed

- Tables:
  - `ApiTask(Id, Title, Status, OwnerName, DueDate, UpdatedAt)`
  - primary key: `Id`
  - index: `Status, DueDate`
- Functions / methods:
  - `ApiTask.GetApiTask($param_ApiTask_Id_where)`
  - `ApiTask.GetApiTaskList($param_ApiTask_Status_where, $limit)`
- Source outputs:
  - `OPENAPI-JSON`
  - `API-PROXY-SERVER`
- Security / auth:
  - `NoSecurity`
- Smoke gates:
  - `make sample13-http-runtime-smoke`
  - `make sample13-http-runtime-smoke-sqlite`
  - `make sample13-browser-try-it-out-smoke`
  - `make sample13-browser-try-it-out-smoke-sqlite`

### Generation Decision

`GetApiTask` is `generated`.

Reason:

- single table
- scalar primary-key equality
- fixed target fields
- direct response object
- no vendor-specific SQL
- same contract works in MySQL / MariaDB and SQLite through the generated runtime adapter

`GetApiTaskList` is `generated_with_options`.

Reason:

- single table list query is generator-owned.
- `Status = ?`, `DueDate asc, Id asc`, and `limit` are normal DBAccess metadata.
- But `limit` and OpenAPI example typing should be treated as explicit generator/runtime options, not implicit behavior.

`OPENAPI-JSON` is `generated_with_options`.

Reason:

- path, request body, response shape, `x-mtool`, and schemas can be generated from target metadata.
- server / proxy base URL changes between local ports and deployment.
- `db_source_key` is viewer/runtime request state, not canonical OpenAPI metadata.

### Dialect Notes

| Dialect | Status | Notes |
| --- | --- | --- |
| MySQL / MariaDB | supported | current default lane; HTTP and browser smoke green. |
| SQLite | supported for config store lane and runtime adapter first slice | generated proxy path works through common runtime adapter; HTTP and browser smoke green. |
| PostgreSQL | future | would need source introspection and SQL quoting / limit behavior review. |
| SQL Server | future | would need TOP/OFFSET and identifier quoting review. |

### Generator Options

| Option | Value | Reason |
| --- | --- | --- |
| `proxy_base_url` | deploy/runtime supplied | OpenAPI server URL differs by environment. |
| `db_source_key` | viewer/runtime query option | Should not mutate canonical `openapi.json`. |
| `limit` | function argument | Needs validation and example generation. |
| request examples | metadata-derived with possible overrides | Current generic `"string"` examples are acceptable for smoke but should improve later. |

### Inherited / Custom Implementation Notes

No custom implementation is required for current sample13 behavior.

Potential custom cases:

- row-level authorization
- tenant scoping beyond a simple scalar parameter
- vendor-specific date filtering
- non-standard response envelope

These should be wrapper/custom proxy responsibilities, not hidden generator behavior.

## SAMPLE16 Review

### Inputs Reviewed

- Tables:
  - `AuthTask(Id, Title, Status, OwnerName, UpdatedAt)`
  - primary key: `Id`
- Functions / methods:
  - `AuthTask.GetAuthTask($param_AuthTask_Id_where)`
- Source output:
  - `AUTH-PROXY-SERVER`
- Security / auth:
  - `ProjectToken`
  - env: `MTOOL_PROXY_PROJECT_TOKEN`
  - payload: `TOKEN`
- Generated extension point:
  - `_wrappers/handlers/AuthTaskGetAuthTaskProxyHandler.php`
- Smoke gates:
  - `make sample16-http-runtime-smoke`
  - `make sample16-http-runtime-smoke-sqlite`

### Generation Decision

`AuthTask.GetAuthTask` is `generated`.

Reason:

- single table
- scalar primary-key equality
- direct response object
- no transaction / lock / vendor-specific SQL
- same generated DBAccess adapter surface is used in MySQL / MariaDB and SQLite lanes

ProjectToken auth is `generated`.

Reason:

- token source is a standard env var.
- request field name is predictable: `TOKEN`.
- fail-closed behavior is deterministic:
  - missing token fails
  - empty token fails
  - missing env token fails
  - wrong token fails
  - matching token passes
- current HTTP smoke verifies the contract in both config store profiles.

Custom authorization hook is `inherited_custom`.

Reason:

- `authorizeByGetFunction()` and `authorizeByLoginCookieToken()` depend on project-specific identity, session, tenant, and policy data.
- keeping hooks in the wrapper class preserves generator simplicity.
- generated base can be regenerated safely without overwriting custom policy.

### Dialect Notes

| Dialect | Status | Notes |
| --- | --- | --- |
| MySQL / MariaDB | supported | default lane smoke verifies ProjectToken route. |
| SQLite | supported for config store lane and runtime adapter first slice | same generated adapter path and payload contract. |
| PostgreSQL | future | token policy is portable; DBAccess dialect still needs future source/runtime support. |
| SQL Server | future | token policy is portable; DBAccess dialect still needs future source/runtime support. |

### Generator Options

| Option | Value | Reason |
| --- | --- | --- |
| `single_proxy_auth_type` | `ProjectToken` | Standard generated auth strategy. |
| `MTOOL_PROXY_PROJECT_TOKEN` | env var | Secret should not be stored in generated artifact. |
| auth field | `TOKEN` | Stable payload contract for clients / OpenAPI. |
| custom handler wrapper | enabled | Project-specific policy stays outside regenerated base file. |

### Inherited / Custom Implementation Notes

Use wrapper class for:

- tenant-aware authorization
- lookup-based authorization
- login-cookie or OIDC bridge checks
- request/response auditing hooks
- policy that depends on external identity provider or project membership

Do not push these into the base generator until the policy becomes a stable cross-project contract.

## Cross-Sample Findings

### What The Generator Can Own Now

- simple `SELECTSINGLE`
- simple `SELECTLIST`
- scalar where parameters
- target field mapping
- basic sort order
- direct result response envelope
- OpenAPI path / request / response surface from single-function proxy metadata
- NoSecurity for explicitly internal/tutorial use
- ProjectToken fail-closed auth
- generated base + custom wrapper split
- MySQL / MariaDB and SQLite runtime adapter surface for the current generated DBAccess subset

### What Should Stay Outside Generator For Now

- blob / file upload binding
- vendor-specific SQL functions
- stored procedures
- complex transaction boundary
- row-level or tenant-level auth
- identity-provider specific authorization
- custom response envelope with business-specific semantics
- query behavior that needs unclear indexes / relation / uniqueness assumptions

### Policy Confirmation

This first review supports the current policy:

- MySQL / MariaDB and SQLite should stay aligned on generated-output usage.
- SQLite should not get a separate one-off generated layer that MySQL / MariaDB does not use.
- Complex behavior can remain in inherited/custom classes.
- AI can make the first generation/custom/manual classification, but complex or security-sensitive cases should remain advisory until reviewed.

## Test / Smoke Expectations

Representative gates that should stay green:

```bash
make sample13-http-runtime-smoke
make sample13-http-runtime-smoke-sqlite
make sample13-browser-try-it-out-smoke
make sample13-browser-try-it-out-smoke-sqlite
make sample16-http-runtime-smoke
make sample16-http-runtime-smoke-sqlite
make test
```

Latest local verification:

- `make sample13-browser-try-it-out-smoke`: OK
- `make sample13-browser-try-it-out-smoke-sqlite`: OK
- `make test`: OK, `174 tests, 7119 assertions`

## Follow-Up Slice: Typed Scalar Examples

Done:

- OpenAPI scalar request parameters now infer conservative numeric examples from clear parameter names.
- `limit` is emitted as `integer` with example `0`.
- `...Id...` / `...PID...` / `...Count...` / `...Number...` / `...No...` suffixes are emitted as `integer` with example `0`.
- Other scalar request parameters remain `string`.
- sample13 and sample17 OpenAPI reference artifacts were updated.
- Proxy build items now carry DBAccess where / limit metadata into OpenAPI build-plan payloads.
- OpenAPI scalar request schema generation now prefers DBAccess parameter metadata, then target column name, then conservative parameter-name inference.
- sample16 authenticated proxy build-plan now carries stable DBAccess select where metadata, with `AuthTask.Id` marked as `int`.

This keeps the generated OpenAPI request examples aligned with the DBAccess definition where metadata exists, while preserving the conservative name-based fallback for older or custom build items.

## Follow-Up Decisions

1. Add an AI review artifact to future user DB samples when behavior is not obviously simple CRUD/read.
2. Broaden metadata-backed OpenAPI scalar typing beyond the current DBAccess where / limit first slice if richer parameter metadata is added.
3. Decide whether auth-required OpenAPI operation should get a browser Try It Out smoke after sample16 is represented in OpenAPI form.
4. Keep custom authorization and tenant policy in wrapper/custom runtime until there is a stable cross-project policy contract.
