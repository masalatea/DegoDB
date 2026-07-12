# MySQL/MariaDB Live Constraint Import

## Outcome

Plan #862D qualifies the first live constraint import path for MySQL/MariaDB. The existing table import now reads ordered primary and unique keys plus foreign keys from `information_schema`, exposes source constraint support and counts in preview, and replaces canonical constraint metadata in the same transaction as table and column metadata apply.

## Safety boundaries

- Only constraints whose tables are in the project's managed import table set are included.
- A foreign key is included only when both its source and referenced table are managed by the project.
- A focused single-table import does not replace project-wide constraints; preview reports that constraint apply is unavailable for that focused operation.
- Sources that do not explicitly report constraint support preserve existing canonical constraints.
- Portable physical names are resolved to the current canonical table and column PIDs after table/column changes, before transaction commit.

## Evidence

- Composite primary/unique key ordering and composite foreign-key column pairing are normalized without canonical PIDs.
- Portable live snapshots resolve to canonical PIDs inside the caller's transaction.
- The full integration suite passed with 538 tests and 14835 assertions; one existing test is skipped.
- An initial full-suite run exposed unscoped source constraints. The managed-table filter was added and the full suite then passed. This failed-first result is retained here as implementation history.

## Remaining scope

PostgreSQL and SQLite live constraint readers are not qualified by #862D. They remain explicit cross-driver work under the later qualification checkpoint. Plan #862E can now use canonical key/FK evidence to complete the SSO app-user generation-readiness gate for the qualified path.
