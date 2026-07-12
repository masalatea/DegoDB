# Sample19 Material-to-No-Code Validation Pipeline

English companion:
This document is the function-level entry point for AI-assisted Sample19 material-to-no-code review.

Use it when Codex, Claude, or another prompt-driven assistant needs to inspect the current deterministic pipeline without calling a provider API. Ollama or another local scan may be used as a fallback reader, but the authoritative checks are the PHP validators and tests listed here.

## Boundary

This pipeline is review-first and read-only.

It does not authorize:

- AI/provider calls from Mtool
- Ollama calls from Mtool
- DB/config writes
- import/apply/build/publish
- generated submit/action execution
- route mutation
- full sample conversion

The current material-to-no-code handoff is available as PHP functions and tests. Browser behavior exists only for the separate default-off material insight preview route; any future browser smoke should run headless by default.

## Pipeline

| Stage | Purpose | Function / file | Current proof |
| --- | --- | --- | --- |
| Schema proposal validation | Confirm the Sample19 proposal artifact is structurally valid before deriving review data. | `app_schema_proposal_validate()` in `mtool/app/schema_proposal.php` | `tests/Integration/MaterialInsightTest.php` |
| Canonical diff derivation | Compare proposal intent with the canonical snapshot without applying changes. | `app_schema_proposal_derive_canonical_diff()` in `mtool/app/schema_proposal.php` | `tests/Integration/MaterialInsightTest.php` |
| Material insight build | Build `material_insight_v0` from source material, proposal, and canonical snapshot. | `app_material_insight_from_schema_proposal()` in `mtool/app/material_insight.php` | `tests/Integration/MaterialInsightTest.php` |
| Material insight validation | Fail closed on identity mismatch, invalid refs, missing evidence, unsupported categories/sections, non-empty actions, or mutation flags. | `app_material_insight_validate()` in `mtool/app/material_insight.php` | `tests/Integration/MaterialInsightTest.php` |
| Read-only preview load | Load fixture-backed material insight preview for the default-off authenticated Sample19 route. | `app_material_insight_preview_load()` in `mtool/app/material_insight_preview_page.php` | `tests/Integration/MaterialInsightPreviewPageTest.php` |
| Read-only preview render | Render source/basis/Q&A/UI/prohibited-action markers without submit controls. | `app_material_insight_preview_html()` in `mtool/app/material_insight_preview_page.php` | `tests/Integration/MaterialInsightPreviewPageTest.php`; headless browser evidence report |
| No-code handoff | Adapt validated `material_insight_v0.ui_outline` to existing no-code metadata. | `app_material_insight_no_code_handoff()` in `mtool/app/material_insight_no_code_handoff.php` | `tests/Integration/MaterialInsightNoCodeHandoffTest.php` |
| Screen definition output | Emit `no-code-screen-definition-v0` for read-only Sample19 material insight screens. | `app_material_insight_no_code_screen_definition()` in `mtool/app/material_insight_no_code_handoff.php` | `tests/Integration/MaterialInsightNoCodeHandoffTest.php` |
| Runtime preview output | Render `no-code-runtime-v0` preview data through existing runtime helpers. | `app_material_insight_no_code_runtime_preview()` and `app_no_code_runtime_render_screen()` | `tests/Integration/MaterialInsightNoCodeHandoffTest.php` |

## Minimum AI review checklist

When an assistant reviews this lane, it should verify:

1. The source/proposal/snapshot files are the expected Sample19 fixtures.
2. `app_schema_proposal_validate()` succeeds before material insight build.
3. `app_material_insight_validate()` succeeds before no-code handoff.
4. `ui_outline.mode` remains `read_only_review`.
5. `ui_outline.actions` remains empty.
6. Q&A cards have allowed `answer_category` values: `structure`, `relationship`, `ui_outline`.
7. Q&A cards have valid JSON pointer evidence refs.
8. UI screens have allowed sections: `entity_review`, `qa_review`.
9. No-code handoff output uses existing versions:
   - `no-code-screen-definition-v0`
   - `no-code-runtime-v0`
10. Output screens are read-only and have no actions/custom operations.
11. Traceability carries material insight source version, project key, source hash, proposal id, canonical snapshot hash, and prohibited actions.

If any of these fail, the assistant should stop at review/advice. It should not suggest applying generated changes or enabling execution as an automatic next step.

## Fallback local scan role

A fallback local scan may help summarize candidate files or point an assistant to the right function names. It is not the source of truth.

Good fallback output:

- candidate file list
- function names
- missing marker hints
- simple text warnings such as "non-empty actions found"

Not authoritative fallback output:

- "safe to execute"
- "safe to publish"
- "safe to mutate DB"
- "safe to replace the sample route"

Execution, publishing, and mutation require separate route, authority, CSRF, audit, idempotency, and transaction design.

## Test commands

Targeted Sample19 handoff test:

```sh
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample19-json-first-content-model-demo/compose.yaml \
  --run-script=sample/tutorials/sample19-json-first-content-model-demo/run.sh \
  --apply-pack-seed \
  --phpunit-target=/var/www/tests/Integration/MaterialInsightNoCodeHandoffTest.php
```

Full integration gate:

```sh
make test
```

Browser smoke is not part of the inner loop. If a browser check is promoted later, use headless mode by default.
