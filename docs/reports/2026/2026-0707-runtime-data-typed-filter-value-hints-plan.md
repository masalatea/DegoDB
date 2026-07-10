# Runtime-data typed filter value hints plan

Date: 2026-07-07

## Summary

#368 chooses runtime-data typed filter value hints as the next small product-facing slice after the date/time policy closure.

The runtime-data endpoint contract is now strict for numeric/date/time ordered comparisons, but the generated browser filter value controls are still generic text inputs. The next slice should expose accepted value formats in the generated controls without changing endpoint behavior.

## Selected First Slice

Add field-type-aware placeholder/title text to generated current/alias runtime-data filter value inputs:

- `integer`: integer value
- `number`: numeric value
- `date`: `YYYY-MM-DD`
- `datetime`: `YYYY-MM-DDTHH:MM:SS`
- `time`: `HH:MM:SS`
- other fields: text value

The hints should update when the selected filter field changes and should work for primary, secondary, and tertiary filter rows.

## Out Of Scope

- Endpoint contract changes.
- New accepted date/time formats.
- Timezone normalization.
- Nullable date/time ordering behavior.
- URL replay/history replay behavior changes.
- Artifact-key preview behavior changes.
- Mutation, retry, outbox processing, or status polling changes.

## Verification Plan

- PHP lint for `mtool/app/no_code_runtime.php`.
- Node syntax check for the browser smoke script if touched.
- `git diff --check`.
- At least `make sample31-no-code-public-runtime-browser-smoke`, because sample31 has explicit typed numeric/date fields.

## Push Status

No push was performed for #368.
