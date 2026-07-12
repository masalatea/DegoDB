# Mtool Source Output Inspection Canonical Entry Point

## Status

`DONE`

## Summary

Added the first canonical-page affordance for the contained Mtool no-code inspection workflow.

The canonical Source Outputs page now exposes a link to:

```text
/projects/MTOOL/source-outputs/no-code-inspection
```

only when `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED` is enabled and the current project is `MTOOL`.

## Implementation

Added:

- `app_project_source_outputs_show_no_code_self_inspection_link($projectKey)`
- a stable link marker:
  - `data-mtool-no-code-inspection-entry-point="true"`
- user-facing copy:
  - `Open read-only no-code inspection`
  - `Generated inspection is read-only and does not replace canonical Source Outputs.`

The link lives inside the existing `No-Code Runtime Inspection` summary card.

## Preserved boundaries

This slice does not add:

- new route;
- mutation;
- Source Output create/edit/delete/reorder/build/publish replacement;
- review request persistence;
- public/lab/current/alias exposure;
- broad navigation redesign.

Rollback remains unsetting `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED`.

## Test coverage

`NoCodeMtoolSourceOutputInspectionTest` now asserts:

- default-off does not show the entry link;
- enabled `MTOOL` shows the entry link;
- enabled non-MTOOL does not show the entry link;
- canonical page source contains the stable marker, target, and read-only copy;
- no runtime execution or guarded submit binding is introduced by the page source.

## Verification

- `php -l mtool/app/project_source_outputs_page.php`
- `php -l tests/Integration/NoCodeMtoolSourceOutputInspectionTest.php`
- `git diff --check`
- `make test` before commit

## Next

#803 should close this entry-point slice and choose whether browser evidence is necessary for the visible link, or whether the next contained productization step can remain fast-test only.
