# Sample18 Public Runtime Status Filter DOM Preflight

Plan item: #560 sample18 public-runtime status filter DOM preflight

Status: DONE

## Summary

Added the narrow public-runtime preflight for `sample18-mini-task-board-demo` status filter controls before moving to safe action-input mapping.

## Changes

- Added `sample18` support to the no-code runtime browser smoke profile list.
- Added `--runtime-filter-dom-only` to stop after verifying public runtime-data binding and generated filter DOM controls.
- Added `make sample18-no-code-public-runtime-filter-dom-smoke`.
- Reused the existing public runtime revision flow through a sample18 wrapper, without enabling generated mutation buttons or joining the full sample28/29/31 public smoke matrix.
- Added a smoke-only public revision override that permits an otherwise ready readonly DOM preflight when the sole publish readiness blocker is an empty action surface.
- Updated the sample18 fast checklist so `generated_runtime_data_controls` points to the new Make target.

## Boundary

This slice only proves that the generated public runtime page renders the `status` filter control when runtime-data binding is present. It does not replace `/samples/sample18-task-board`, enable generated POST execution, or add action input mapping.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l mtool/scripts/create_no_code_public_runtime_smoke_revision.php`
- `bash -n mtool/scripts/check_sample18_no_code_public_runtime_filter_dom_smoke.sh mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh`
- `make sample18-no-code-public-runtime-filter-dom-smoke`
- `make test`
- `git diff --check`

## Next

#561 should define the minimal safe action-input mapping contract for sample18 generated actions while keeping mutation disabled and the curated route as the only mutation owner.
