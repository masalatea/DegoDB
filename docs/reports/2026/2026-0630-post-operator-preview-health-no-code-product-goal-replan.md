# Post-Operator Preview Health No-Code Product Goal Replan / operator preview health 後の product goal 再計画

Date: 2026-06-30

Status: `DONE`

## Summary / 概要

After operator-visible artifact health and detail/download/path affordances, the next small no-code product-facing slice is `Operator source-output artifact detail first slice`.

operator-visible な artifact health と detail / download / path affordance 後、次の小さな no-code product-facing slice として `Operator source-output artifact detail first slice` を選んだ。

## Decision / 判断

| Candidate / 候補 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- |
| Operator artifact detail follow-up | 0.5 - 2 days / 半日 - 2 日 | Selected. Health/detail links now surface artifact identity, but there is no read-only artifact detail page between list summary and archive download. |
| No-code runtime product polish | 0.5 - 2 days / 半日 - 2 日 | Deferred. The health surface did not confirm a generated runtime behavior gap. |
| Sync/error-state pressure | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but less directly tied to the current operator artifact inspection gap. |
| Mtool implementation namespace cleanup | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Selected First Slice / 選んだ first slice

`Operator source-output artifact detail first slice`

Scope:

- add a read-only artifact detail route/page;
- reuse existing source-output artifact manifest data and permission boundaries;
- show manifest, archive, bundle, runtime source, source output identity, file counts, and download affordance;
- link artifact identities from existing Source Outputs surfaces to the detail page.

## Rationale / 理由

Operator health is now visible, but the operator still has to jump directly from a summary to downloading an archive. A small artifact detail page closes that inspection gap without adding publish workflow, editing, generated runtime behavior, visual builder, or sync transport changes.

operator health は見えるようになったが、operator は summary から archive download へ直接飛ぶしかない。小さな artifact detail page を追加すると、publish workflow、編集、generated runtime behavior、visual builder、sync transport を増やさずに inspection gap を埋められる。

## Verification / 検証

Planning/report update only. Implementation verification belongs to the selected first slice.
