# Post-Operator/Admin No-Code Product Goal Replan / operator/admin no-code 後の product goal 再計画

Date: 2026-06-30

Status: `DONE`

## Summary / 概要

After the first inspection-only operator/admin surface, the next small no-code product-facing slice is `Operator preview health/detail links first slice`.

operator/admin 向けの最初の inspection-only surface 後、次の小さな no-code product-facing slice として `Operator preview health/detail links first slice` を選んだ。

## Decision / 判断

| Candidate / 候補 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- |
| Operator inspection follow-up | 0.5 - 2 days / 半日 - 2 日 | Selected. The first surface shows `NO-CODE-RUNTIME` counts and paths, but operators still need a compact health signal and direct affordances into generated artifacts. |
| No-code runtime product polish | 0.5 - 2 days / 半日 - 2 日 | Deferred. The operator inspection did not reveal a confirmed generated runtime behavior gap yet. |
| Sync/error-state pressure | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but the operator/admin workflow should first expose current artifact health clearly. |
| Mtool implementation namespace cleanup | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Selected First Slice / 選んだ first slice

`Operator preview health/detail links first slice`

Scope:

- derive the smallest health states from existing `NO-CODE-RUNTIME` definition, latest artifact, generated preview JSON, and generated preview HTML;
- surface direct detail/download/path affordances in the existing Source Outputs admin page;
- stay read-only and avoid visual builder, metadata editing, publish approval workflow, remote transport, conflict resolution, and new generated runtime behavior.

## Rationale / 理由

The first operator/admin surface made generated no-code runtime artifacts visible, but it is still mostly a count/path summary. The next useful product step is not a broader builder or sync feature. It is a compact operator answer to: "is this generated no-code preview healthy, and where can I inspect the concrete artifact?"

最初の operator/admin surface で generated no-code runtime artifact は見えるようになったが、まだ count/path summary に近い。次に有用なのは広い builder や sync 機能ではなく、「この generated no-code preview は健全か、具体 artifact はどこで確認できるか」に答える小さな operator affordance。

## Verification / 検証

Planning/report update only. Implementation verification belongs to the selected first slice.
