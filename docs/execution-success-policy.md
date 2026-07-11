# Execution Success Policy / 実行成功ポリシー

English companion:
This document defines the common success/failure contract for mutation and execution routes. A user-facing success is returned only when every required operation step succeeds. If any required step fails, the route fails closed and exposes internal recovery metadata instead of claiming partial success.

この文書は、mutation / execution route の共通 success / failure contract を定義する恒久文書です。
user-facing success は required operation step がすべて成功した場合だけ返します。required step が 1 つでも失敗した場合、route は fail closed し、partial success を成功として扱わず、内部 recovery metadata を返します。

## Core Rule / 基本ルール

| Rule / ルール | English | 日本語 |
| --- | --- | --- |
| Success condition | Return success only when every required step succeeds. | required step が全成功した場合だけ success を返す。 |
| Failure condition | Any required-step failure returns failure. | required step が 1 つでも失敗したら failure を返す。 |
| Partial work | Partial physical work is internal recovery state, not user-facing success. | 物理的に一部処理が進んだ状態は内部 recovery state であり、user-facing success ではない。 |
| Cross-store gap | Missing cross-store atomicity must be surfaced as internal failure / recovery metadata. | cross-store atomicity が未完成な箇所は内部 failure / recovery metadata として出す。 |
| Retry | Retry must fail closed unless a dedicated replay or repair path is explicitly designed. | 専用 replay / repair path が設計されるまで retry は fail closed にする。 |

## Required Steps / required step

The exact required steps depend on the route. For mutation/execution routes, consider these steps required when the route claims the corresponding capability:

| Step / step | English | 日本語 |
| --- | --- | --- |
| Validation | Request shape and domain validation succeeded. | request shape と domain validation が成功した。 |
| Authorization | Auth, role, policy, and route boundary checks succeeded. | auth、role、policy、route boundary check が成功した。 |
| CSRF / admission | CSRF and admission gates succeeded when required. | 必要な CSRF / admission gate が成功した。 |
| Idempotency admission | The request is accepted as new or valid replay according to the route contract. | route contract に従い、新規または有効 replay として受理された。 |
| Request audit | Required request audit was recorded. | 必須 request audit が記録された。 |
| App mutation | The application data mutation succeeded. | application data mutation が成功した。 |
| Transaction finish | Required transaction commit succeeded. | 必須 transaction commit が成功した。 |
| Execution audit | Required execution audit was recorded. | 必須 execution audit が記録された。 |
| Idempotency outcome | Required idempotency execution outcome update succeeded. | 必須 idempotency execution outcome update が成功した。 |

## Cross-Store Atomicity / cross-store atomicity

Some routes write application data in one store and audit/idempotency state in another store. Until a true cross-store atomic mechanism exists, the UI/API contract still remains all-success-or-failure.

複数 store にまたがる route では、application data と audit / idempotency state が別 store に書かれることがあります。真の cross-store atomic mechanism ができるまでは、物理状態として recovery が必要になる可能性があります。それでも UI/API contract は all-success-or-failure のままです。

Required behavior:

- App DB commit success followed by audit/idempotency recording failure returns failure.
- The response must include internal failure / recovery metadata.
- The route must not claim user-facing success for partial recording.
- Duplicate retry must not blindly execute the mutation again.

## Response Metadata / response metadata

Failure responses should expose enough metadata to debug and recover without claiming success.

Recommended fields:

- `success`: boolean
- `status`: stable route status
- `failure_code`: stable failure code
- `required_steps`: per-step status where useful
- `transaction_status`
- `dbaccess_status`
- `recording_status`
- `rolled_back`
- `recovery_required`
- `recovery_reason`
- `dedupe_key`
- `request_audit_event_key`

## Current Application / 現在の適用

| Area / 領域 | Policy use / 方針 |
| --- | --- |
| sample18 generated submit execution | Must use this policy before route execution is enabled. |
| review workflow request persistence | Persistence acceptance should fail closed when required guard/persistence/audit steps fail. |
| source-output review/publish operations | Route execution plans should reference this policy before mutation is enabled. |
| future no-code generated actions | Generated action execution must use this policy by default. |

## Non-Goals / 非ゴール

- This policy does not claim physical cross-store atomicity already exists.
- This policy does not replace route-specific authorization, CSRF, idempotency, or audit design.
- This policy does not define a repair queue by itself; repair/outbox behavior needs a separate plan.
