# Sample18 Generated Availability Expansion Preflight

Date: 2026-07-10

Status: `DONE`

## Summary

#695 defines the smallest safe boundary for expanding sample18 generated action availability after the fast metadata/DOM/payload/key contracts and the narrow browser smoke. It does not enable mutation, change generated defaults, or broaden action execution.

## Boundary

The first availability expansion candidate set is limited to:

- `create_task_card`
- `update_task_card`
- `complete_task_card`

`reopen_task_card` and `delete_task_card` remain disabled metadata-only candidates until DBAccess/custom adapter contracts exist.

## Required Conditions

Before generated UI may present an executable candidate state:

- mutation and executor enablement must both be explicit through app config or env flags;
- route `executor_config.status` must be `ready`;
- the generated action key, operation key, submit URL, CSRF handoff, required fields, key fields, and payload assembly must match the generated-submit route normalizer;
- keyed actions must have a selected row key source and fail closed when it is absent;
- UI result rendering must distinguish executed, blocked/duplicate, ordinary failure, and recovery-required failure;
- tests must cover disabled default, enabled candidate, missing key/input, config failure, duplicate replay, rollback failure, recovery-required failure, and successful execution.

## Next Work

Promote #696: `Sample18 generated availability-state fast contract first slice`.

The next slice should add fast PHPUnit/DOM assertions for disabled-default and enabled-candidate availability state before changing runtime defaults. Browser smoke can follow as an outer confirmation after the fast contract proves the generated metadata and DOM state.

## Verification

Docs-only preflight. No runtime tests were required for this planning commit.
