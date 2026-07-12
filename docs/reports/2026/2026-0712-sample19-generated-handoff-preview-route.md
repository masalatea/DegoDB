# Sample19 Generated Handoff Preview Route

Date: 2026-07-12

## Summary

Added a default-off authenticated Sample19 generated handoff preview route:

- `GET /projects/SAMPLE19/material-insight/no-code-handoff`
- route name: `project_sample19_material_insight_no_code_handoff_preview`
- feature flag: `MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED`

The route is inspection-only. It reuses the fixture-backed material insight loader and the test-proven no-code handoff adapter, then renders stable read-only markers for the generated metadata.

## Added markers

- `data-material-insight-no-code-handoff="true"`
- `data-no-code-screen-definition-version="no-code-screen-definition-v0"`
- `data-no-code-runtime-version="no-code-runtime-v0"`
- `data-no-code-handoff-screen="material_entity_list"`
- `data-no-code-handoff-screen="material_qa_cards"`
- `data-no-code-handoff-actions="0"`
- `data-no-code-handoff-custom-operations="0"`
- `data-no-code-handoff-ai-call="false"`
- `data-no-code-handoff-mutation="false"`

## Boundary

This route does not add:

- AI/Ollama calls
- DB/config writes
- import/apply/build/publish
- mutation
- generated submit controls
- generated execution
- public no-code runtime publishing

It is default-off by feature flag and requires the same authenticated route model as the material insight preview.

## Verification

- `php -l mtool/app/material_insight_no_code_handoff_preview_page.php`
- `php -l tests/Integration/MaterialInsightNoCodeHandoffPreviewPageTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample19-json-first-content-model-demo/compose.yaml --run-script=sample/tutorials/sample19-json-first-content-model-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/MaterialInsightNoCodeHandoffPreviewPageTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample19-json-first-content-model-demo/compose.yaml --run-script=sample/tutorials/sample19-json-first-content-model-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/DocsEntranceContractTest.php`
- `git diff --check`
- `make test`

Targeted result:

- 4 tests
- 31 assertions

Docs contract recheck:

- 12 tests
- 590 assertions

Full-suite result:

- 482 tests
- 14,364 assertions
- 1 skipped

## Next

Close the route slice and decide whether the next step should be:

- headless browser evidence for the new inspection route,
- metadata hardening,
- or roadmap review.
