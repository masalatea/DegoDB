# Runtime-data date-time policy closure

Date: 2026-07-07

## Summary

#366 closes the runtime-data date/time policy lane.

The accepted first-slice contract now covers:

- strict ordered filters and sorts for explicit `date`, `datetime`, and `time` fields;
- generated browser ordered-operator exposure only for explicit numeric/date/time fields;
- local offset-less `datetime` values only;
- fail-closed timezone-offset `datetime` values;
- fail-closed null/empty ordered date/time values.

## Completed Lane

- #359 added explicit sample31 `needed_by` date metadata and strict date/time ordered endpoint semantics.
- #360 closed the first date/time endpoint semantics lane and recorded remaining candidates.
- #361 added type-driven browser ordered-operator choices.
- #362 closed the browser operator-choice lane.
- #363 reviewed the local runtime-data stack before push.
- #364 fixed the timezone-offset policy as local and offset-less only.
- #365 fixed the null/empty ordered date/time policy as fail-closed.

## Verification Baseline

The latest implementation verification across the lane is:

- #361: `php -l`, `node --check`, `git diff --check`, sample28/sample29/sample31 public runtime browser smokes, and full `make test`.
- #364: targeted `OpenApiSourceOutputContractTest.php`, PHP lint, and `git diff --check`.
- #365: targeted `OpenApiSourceOutputContractTest.php`, PHP lint, and `git diff --check`.

#366 is docs-only and records no new behavior.

## Remaining Candidates

No MUST candidate remains inside the current first-slice date/time policy lane.

Future separate lanes may still add:

- offset-aware `datetime` normalization;
- nullable date/time ordering controls;
- richer generated input hints for date/time accepted formats;
- broader sample coverage with native `datetime` and `time` columns.

These should be treated as new contract changes, not amendments to the closed first-slice policy.

## Push Status

No push was performed for #366.
