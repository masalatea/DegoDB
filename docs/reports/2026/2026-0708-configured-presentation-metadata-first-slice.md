# 2026-07-08 Configured presentation metadata first slice

Status: `FIRST_SLICE_DONE`

## Summary

#437 adds the first configured presentation layer after the Mtool no-code dogfooding probe.

The goal is not to hand-edit generated HTML. The goal is to let the same interface carry an explicit presentation profile that later renderers can consume consistently.

## Accepted Capability

- `contract_metadata.presentation_profile` can declare a configured presentation layer separately from usage intent and view variant preference.
- Screen definitions now expose normalized `presentation_profile` metadata at the contract level.
- Each generated list/detail/form screen now carries a `presentation_hint`.
- Runtime preview JSON carries `presentation_hint` forward so HTML runtime, React bridge, or future renderers can consume the same metadata.
- The Mtool Source Output review probe now uses a compact review profile with primary fields, secondary fields, and identity/artifact field groups.

## Metadata Shape

The first slice supports:

- `profile_key`
- `density`: `compact`, `standard`, or `comfortable`
- `emphasis`: `balanced`, `review`, or `data_entry`
- `primary_fields`
- `secondary_fields`
- `field_groups`

Unknown fields are filtered out when normalizing field lists and groups. Invalid density or emphasis values fall back to the standard derived defaults.

## Out Of Scope

- No visual HTML density change yet.
- No React bridge component slot rendering yet.
- No persistent admin edit UI for presentation profiles yet.
- No custom UI slot implementation yet.

Those are separate follow-up slices so configured presentation remains a metadata layer rather than a generated artifact hand-edit path.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- focused PHPUnit: `OK (7 tests, 82 assertions)`
- `git diff --check`
- full `make test`: `344 tests`, `11234 assertions`, `Skipped: 1`

The PHPUnit warning about `.phpunit.result.cache` being read-only is the existing container cache warning and does not affect the test result.

## Recommended Next Step

Next implementation candidate is #438: custom UI slot manifest first slice.

The configured presentation layer now covers density, emphasis, primary/secondary fields, and field grouping. The next missing boundary is where generated screens should declare extension slots such as related settings, artifact status, and operator actions without embedding custom code directly into generated HTML.
