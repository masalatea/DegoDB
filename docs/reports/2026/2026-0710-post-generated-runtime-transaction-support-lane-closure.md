# Post Generated Runtime Transaction Support Lane Closure

Date: 2026-07-10
Plan: #656
Status: DONE

## Summary

#656 closes the PDO-first generated runtime transaction support lane.

#655 is accepted as the current generated runtime transaction surface before generated-submit route execution is enabled.

## Accepted Capability From #655

- Generated DBAccess runtime support now exposes `beginTransaction`, `commit`, `rollBack`, and `inTransaction`.
- The implementation is PDO-first and preserves legacy `$mtooldb->query`, `$mtooldb->execute`, `errno`, and `error`.
- Sample reference outputs that carry generated runtime support are synchronized with the generator output.
- SQLite/PDO coverage proves commit and rollback behavior.
- Full `make test` passes after digest synchronization.

## Decision

Promote DB-backed transaction binding coverage preflight next.

Reason:

- The transaction binding helper is currently proven with fake transaction runtime objects.
- The generated runtime now has the transaction methods needed by that helper.
- Before route feature-flag integration, one DB-backed coverage slice should prove the generated runtime, binding callables, and real-compatible DBAccess invocation work together.

## Next

#657 should define the first DB-backed transaction binding coverage.

Required boundaries:

- no generated-submit route wiring yet;
- no default-on executor behavior;
- use isolated SQLite/PDO or sample pack DB setup;
- prove transaction commit persists a generated DBAccess mutation;
- prove transaction rollback removes a failed generated DBAccess mutation;
- keep post-commit recording route integration deferred.

## Verification

- `git diff --check`
