# SQLite-to-MySQL Export First Slice

Status: `DONE_FIRST_SLICE`

## Result

#870 added `mtool/app/sqlite_mysql_export.php` as a deterministic, read-only SQLite export core.

- It requires a ready promotion manifest and SQLite PDO source.
- It opens a read transaction when the caller does not already own one.
- Tables follow manifest/FK load order and rows follow declared primary-key order.
- A per-table PK cursor supports keyset resume.
- Full source row counts are checked even for resumed exports.
- Chunk size is bounded and chunks may be passed directly to a consumer without retaining row payloads in the result.
- Each chunk records row count, SHA-256, and the final primary-key cursor.
- BIGINT and DECIMAL remain exact strings; boolean becomes `0`/`1`; JSON becomes a canonical key-sorted value envelope; BLOB becomes base64 with byte length; DATETIME accepts only the declared unambiguous format.
- Conversion or source-count failure fails closed and never changes the source DB.
- The core writes no filesystem artifact and connects to no target DB.

## Boundary

The callback is the future artifact/checkpoint integration boundary. #871 may consume the chunk directly inside a MySQL transaction or persist it through an explicitly selected artifact writer. Credentials and connection configuration are not part of chunk payloads.

V1 assumes the SQLite source is write-frozen for the duration. Cross-process enforcement of the operational write freeze belongs to the cutover workflow, not this reader.

## Verification

- PHP syntax checks passed.
- Focused PHPUnit: `OK (4 tests, 20 assertions)`.
- Full `make test`: `Tests: 562, Assertions: 14952, Skipped: 2`.
- `git diff --check` passed.

#871, transactional MySQL import and resume, is now active next.
