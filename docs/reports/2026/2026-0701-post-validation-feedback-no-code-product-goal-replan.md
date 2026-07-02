# 2026-0701 Post-Validation-Feedback No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Schema-form validation parity check** as the next implementation slice.

Generated runtime and React bridge now fail closed on blank required inputs and expose display-ready validation messages. The next smallest adapter confidence gap is the schema-form comparison artifact: JSON Schema `required` catches missing fields, but it must also carry the Mtool blank-required policy for string fields.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Schema-form validation parity check | 0.5 - 1.5 days | Selected. It keeps JSON Forms / rjsf comparison artifacts aligned with runtime and React bridge validation behavior. |
| Larger product surface | Replan first; likely 2 - 5 days | Deferred. Current validation behavior is stronger, but adapter parity is the smaller continuation. |
| Runtime capability continuation | Replan first; likely 1 - 3 days after a narrow gap is chosen | Deferred. No narrower runtime gap is currently more concrete than schema-form parity. |
| Commit cleanup | 0.25 day | Deferred. The validation feedback slice was already committed locally and the worktree was clean. |

## Boundary

In scope:

- Schema-form generated JSON Schema required string handling.
- Schema-form contract metadata and consumer notes for blank-required parity.
- rjsf smoke assertion for blank required validation.
- Focused report/current-plan updates.

Out of scope:

- Adopting JSON Forms or rjsf as the product runtime.
- Full validation DSL.
- Cross-field validation.
- Server-side validation behavior.
- Publishing workflow.

## Verification

Planning/report update only.
