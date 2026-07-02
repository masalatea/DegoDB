# Post-Operator Artifact Detail No-Code Product Goal Replan / operator artifact detail 後の product goal 再計画

Date: 2026-06-30

Status: `DONE`

## Summary / 概要

After the read-only Source Output artifact detail page, the next small no-code product-facing slice is `Sync error-state visibility first slice`.

read-only Source Output artifact detail page 後、次の小さな no-code product-facing slice として `Sync error-state visibility first slice` を選んだ。

## Decision / 判断

| Candidate / 候補 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- |
| Operator artifact detail follow-up | 0.5 - 2 days / 半日 - 2 日 | Deferred. The first detail page closes the immediate inspection gap without exposing a concrete missing field. |
| No-code runtime product polish | 0.5 - 2 days / 半日 - 2 日 | Deferred. Artifact detail did not reveal a generated runtime behavior gap. |
| Sync/error-state pressure | 1 - 3 days / 1 - 3 日 | Selected. Success paths are visible now; the next product-facing gap is showing failed sync/outbox state without adding transport or conflict resolution. |
| Mtool implementation namespace cleanup | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Selected First Slice / 選んだ first slice

`Sync error-state visibility first slice`

Scope:

- use existing outbox `failed` / `last_error` lifecycle fields;
- extend sample30 with one deterministic failed sync/outbox processing path;
- assert failed status, attempts, and last error without changing the existing success paths;
- avoid retry scheduler, remote transport, conflict resolution, broad operator dashboard, and generated runtime behavior changes.

## Rationale / 理由

The no-code success path, server-side sync path, handoff visibility, operator artifact health, and artifact detail are all visible. The next narrow product gap is failure visibility: an operator or tester should be able to see that a sync item failed and why, before adding more complex retry, transport, or conflict logic.

no-code success path、server-side sync path、handoff visibility、operator artifact health、artifact detail は見えるようになった。次の狭い product gap は failure visibility であり、retry / transport / conflict logic を増やす前に、sync item が failed になったことと理由を確認できる必要がある。

## Verification / 検証

Planning/report update only. Implementation verification belongs to the selected first slice.
