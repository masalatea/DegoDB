# Sample19 Generated UI Handoff Foundation

Date: 2026-07-12

## Summary

This slice adds a test-only adapter from the fixture-backed Sample19 `material_insight_v0` artifact to the existing no-code runtime metadata shapes.

Added:

- `app_material_insight_no_code_handoff()`
- `no-code-screen-definition-v0` output for a read-only Sample19 material insight contract
- `no-code-runtime-v0` preview output rendered through existing runtime helpers
- traceability from no-code output back to material insight source version, project, source hash, basis, proposal id, canonical snapshot hash, prohibited actions, screen section, entity refs, and Q&A refs
- PHPUnit coverage for successful handoff and invalid-artifact rejection

## Boundary

This is intentionally not a product route yet.

It does not add:

- browser route or navigation entry point
- AI/Ollama calls
- import/apply/build/publish behavior
- DB/config writes
- generated submit/action controls
- mutation or generated execution

The handoff is default-off by absence of a UI entry point. It is available only as a PHP adapter and test contract.

## Adapter behavior

Input:

- validated `material_insight_v0`

Output:

- `screen_definition.definition_version = no-code-screen-definition-v0`
- `runtime_preview.runtime_version = no-code-runtime-v0`
- `project_key = SAMPLE19`
- contract key `sample19_material_insight`
- screens `material_entity_list` and `material_qa_cards`
- read-only fields
- empty actions and empty custom operations
- preview rows derived from material insight entities and Q&A cards

Invalid artifacts are rejected before handoff. For example, non-empty `ui_outline.actions` returns a `material_insight_invalid:*` error and no output metadata.

## Verification

- `php -l mtool/app/material_insight_no_code_handoff.php`
- `php -l tests/Integration/MaterialInsightNoCodeHandoffTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample19-json-first-content-model-demo/compose.yaml --run-script=sample/tutorials/sample19-json-first-content-model-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/MaterialInsightNoCodeHandoffTest.php`
- `git diff --check`
- `make test`

Targeted result:

- 2 tests
- 35 assertions

Full-suite result:

- 478 tests
- 14,330 assertions
- 1 skipped

## Next

Close the handoff foundation lane and choose one of:

- documentation for the AI/Codex/Claude prompt-facing validation pipeline,
- a default-off route for the generated no-code handoff preview,
- or a narrower runtime metadata hardening slice if the adapter output needs more structure before routing.
