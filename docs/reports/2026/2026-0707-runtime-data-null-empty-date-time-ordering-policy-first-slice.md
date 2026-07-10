# Runtime-data null/empty date-time ordering policy first slice

Date: 2026-07-07

## Summary

#365 fixes the first-slice null/empty policy for runtime-data ordered date/time comparisons.

The current contract is strict:

- ordered date/time filter query values must be parseable and non-empty;
- ordered date/time sort row values must be parseable and non-empty;
- null/empty values fail closed;
- null/empty values are not implicitly sorted first or last;
- null/empty filter values are not converted to wildcard behavior.

This records existing parser behavior as an intentional contract and adds direct test coverage for null/empty rejection.

## Reasoning

The runtime-data date/time first slice uses strict parsing before ordered comparison. Allowing null/empty values would require an explicit ordering policy:

- whether null sorts before or after real values;
- whether empty strings behave like null;
- whether query filters with empty values are ignored or rejected;
- how nullable source fields are represented in generated browser controls.

Implicit behavior would make current/alias runtime-data reads less predictable. The safer first-slice boundary is to fail closed for ordered date/time comparisons when a required comparison value is not parseable.

## Changes

- Added contract coverage that null/empty `date`, `datetime`, and `time` values fail closed for ordered filter/sort contexts.
- Updated `docs/current-plans.md` to mark #365 as done.

## Unchanged

- Endpoint routes are unchanged.
- Generated browser controls are unchanged.
- Artifact-key preview behavior is unchanged.
- Mutation, retry, outbox processing, and status polling are unchanged.
- Timezone-offset policy remains local and offset-less only from #364.

## Verification

Targeted verification for #365:

- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `git diff --check`

The targeted PHPUnit file passed with `24 tests` and `1894 assertions`.

## Push Status

No push was performed for #365.
