# Mobile artifact execution UI policy / mobile artifact execution UI policy

Date: 2026-07-14

## Summary / summary

MW-13 policy decision:

Keep the current mobile wrapper artifact UI read-only. Do not add UI-triggered artifact generation yet.

Stable policy:

- `docs/mobile-artifact-execution-ui-policy.md`

## Decision / 判断

Mtool can show artifact guidance in the UI, but generation should continue through controlled CLI/source-output workflows until a concrete operator need exists.

Execution UI is not rejected forever. It is deferred until the safety controls are explicit and tested.

## Required controls / 必須control

Before adding execution UI, define and test:

- CSRF protection;
- authentication and authorization;
- output directory allow-list;
- overwrite policy;
- dry-run/preview mode;
- audit log;
- failure/partial-output handling;
- validation after generation;
- race/concurrent execution behavior;
- cleanup/retry behavior.

## Current consequence / 現在の結果

The existing route remains correct:

```text
GET /projects/{project_key}/mobile-wrapper-artifacts
```

It is read-only guidance. It should not execute generation or create app/native files.

## Reopen condition / 再開条件

Reopen implementation only when a concrete operator workflow requires UI execution and the required controls can be implemented in the same slice.
