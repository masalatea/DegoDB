# 2026-07-08 Mtool dogfooding inspection pass

Status: `FIRST_SLICE_DONE`

## Summary

#439 adds a small inspection pass for the Mtool no-code dogfooding probe.

The pass builds the normal no-code runtime emitted files and summarizes what the generated artifact path now exposes after configured presentation metadata and custom UI slot metadata landed.

## Accepted Capability

- `app_no_code_mtool_dogfooding_probe_inspection_summary()` builds the standard emitted files:
  - `screen-definition.json`
  - `runtime-preview.json`
  - `runtime-preview.html`
  - `README.md`
- The summary reports:
  - contract key
  - interface usage intent
  - view variant preference
  - presentation profile key and density
  - extension slot types
  - screen-level extension slot distribution
  - HTML boundary
- The inspection confirms that configured presentation and extension slots are available in the JSON artifact path.
- The inspection also records that generated HTML has not yet rendered visible custom slot regions.

## Findings

| Area | Finding | Treatment |
| --- | --- | --- |
| Screen definition JSON | Carries `presentation_profile` and contract-level `extension_slots` | Accepted |
| Runtime preview JSON | Carries per-screen `presentation_hint` and screen-relevant `extension_slots` | Accepted |
| Generated HTML | Embeds runtime-preview JSON but does not render custom slot region markup yet | Keep as metadata-only boundary |
| First visible slot candidate | Related settings or artifact status is safer than operator actions | Defer to next visible-rendering slice |

## Out Of Scope

- No generated HTML placeholder rendering yet.
- No React bridge slot mapping yet.
- No custom operation execution behavior.
- No persistent admin edit UI for slots.

## Verification

- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- focused PHPUnit: `OK (8 tests, 102 assertions)`
- `git diff --check`
- full `make test`: `345 tests`, `11254 assertions`, `Skipped: 1`

The PHPUnit warning about `.phpunit.result.cache` being read-only is the existing container cache warning and does not affect the test result.

## Recommended Next Step

Next implementation candidate is #440: close the configured presentation / custom slot metadata lane with commit-stack guidance, or choose a small visible slot placeholder slice if we want one more implementation step before the push checkpoint.
