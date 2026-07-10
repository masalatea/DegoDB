# Runtime-data datetime timezone-offset policy first slice

Date: 2026-07-07

## Summary

#364 fixes the first-slice timezone-offset policy for runtime-data `datetime` ordered comparisons.

The current contract is local and offset-less only:

- accepted: `YYYY-MM-DDTHH:MM:SS`
- accepted: `YYYY-MM-DD HH:MM:SS`
- rejected: `YYYY-MM-DDTHH:MM:SS+09:00`
- rejected: `YYYY-MM-DDTHH:MM:SSZ`

This records existing parser behavior as an intentional contract and adds direct test coverage for timezone-offset rejection.

## Reasoning

The runtime-data date/time first slice compares normalized local values as strings after strict parsing. Accepting timezone offsets would require an explicit normalization policy:

- which timezone is canonical;
- whether `Z` and offsets are converted to UTC or application local time;
- how local offset-less values interact with offset-aware values;
- how generated browser controls should present or accept those values.

Implicit normalization would make the current read-only endpoint contract less predictable. The safer first-slice boundary is to fail closed on offset-aware values and leave offset-aware normalization as a separate contract change.

## Changes

- Added contract coverage in `OpenApiSourceOutputContractTest` that accepts local offset-less datetime values.
- Added contract coverage that `+09:00` and `Z` datetime values fail closed.
- Updated `docs/current-plans.md` to mark #364 as done.

## Unchanged

- Endpoint routes are unchanged.
- Generated browser controls are unchanged.
- Artifact-key preview behavior is unchanged.
- Mutation, retry, outbox processing, and status polling are unchanged.
- Date-only and time-only parsing semantics are unchanged.

## Verification

Targeted verification for #364:

- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `git diff --check`

The targeted PHPUnit file passed with `23 tests` and `1888 assertions`.

## Push Status

No push was performed for #364.
