# Canonical Table Constraint Bundle Integration

Status: `DONE`

This report completes plan #862C.

## Implemented bundle boundary

Project metadata bundles now optionally include:

- `table-keys.json`;
- `table-foreign-keys.json`.

The portable representation uses table and column physical names rather than config-DB PIDs. Import validates names against the bundled canonical table section, then resolves them to the target project's newly created table/column PIDs before persistence.

## Preserve behavior

Project-core apply recreates canonical table rows, which changes PIDs and cascades normalized constraint rows. Therefore absent-section preservation cannot be implemented by simply skipping repository writes.

The apply path now:

1. captures existing target constraints as portable physical-name metadata;
2. recreates canonical table metadata;
3. resolves the portable snapshot to new PIDs;
4. restores the target constraints inside the same import transaction.

When constraint sections are present, imported constraints replace the target snapshot. Preview reports `create`, `replace`, or `preserve` only when relevant. Projects with neither source nor target constraints retain the legacy bundle summary shape.

## Validation

- Key and FK sections must appear together.
- Referenced tables/columns must exist in the bundled table section.
- Empty key/FK columns fail validation.
- Resolved PID ownership is revalidated by the canonical constraint normalizer.
- Any failure rolls back the project-core import transaction.

## Evidence

- Focused metadata bundle class: `6 tests`, `346 assertions`.
- Composite unique order round-trips through portable names.
- FK referenced-column mapping round-trips.
- Applying an older bundle preserves existing key and FK metadata after table PID recreation.
- `make test`: `534 tests`, `14809 assertions`, `1 skipped`; exit code 0.
- PHP lint and `git diff --check` passed.

## Next boundary

Plan #862D should add the first live-schema constraint reader and preview/apply integration. MySQL/MariaDB is the natural first driver because the current primary live import already reads its `information_schema.COLUMNS`. PostgreSQL and SQLite remain explicit follow-up drivers until individually qualified.
