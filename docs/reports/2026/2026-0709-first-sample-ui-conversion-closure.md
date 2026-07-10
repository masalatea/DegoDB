# First Sample UI Conversion Closure

Status: `DONE`

Plan item: #550 first sample UI conversion closure

## Decision

`sample18-mini-task-board-demo` is credible enough to count as the first L1 existing sample UI no-code entry, with an explicit boundary: this is a metadata-first and preview-first entry, not a generated route replacement.

## Accepted Capability

- Stable golden fixture for the existing task board route and seed rows.
- Readonly `task_card` shared contract metadata for generated list/detail/form screens.
- Generated `NO-CODE-RUNTIME` artifact with `screen-definition.json`, `runtime-preview.json`, and `runtime-preview.html`.
- Runtime preview rows compared against the golden seed rows.
- Create, update, complete, reopen, and delete described as disabled dry-run no-code operations with route boundaries.
- Generated action buttons remain disabled through `generated_button_enabled=false`.
- Existing curated route `/samples/sample18-task-board` remains the only mutation owner.

## Remaining Gaps

- A generic fast HTML DOM contract harness is still needed; sample18 currently performs targeted string checks.
- Generated no-code list filter/sort state is not yet a first-class reusable contract.
- Generated form write semantics are not yet mapped to a safe action-input model.
- No generated UI mutation dispatcher exists, by design.
- No generated route replacement or shadow route should happen until the fast contract harness and a dedicated no-code test lab sample are in place.

## Plan Impact

#552 should become `ACTIVE_NEXT`: add the fast PHPUnit JSON and `DOMDocument` harness for generated no-code runtime artifacts. The dedicated no-code-only sample remains parked until that harness exists.

## Verification

This is a docs-only closure. The preceding #549 code slice was verified with:

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/scripts/lib/sample18_mini_task_board_demo_check.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`
