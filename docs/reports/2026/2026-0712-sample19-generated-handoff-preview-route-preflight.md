# Sample19 Generated Handoff Preview Route Preflight

Date: 2026-07-12

## Decision

Add a default-off authenticated inspection route in the next slice.

Route:

- method: `GET`
- path: `/projects/{project_key}/material-insight/no-code-handoff`
- route name: `project_sample19_material_insight_no_code_handoff_preview`
- allowed project: `SAMPLE19` only
- feature flag: `MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED`
- auth: same route auth model as `project_sample19_material_insight_preview`

## Purpose

Expose the already test-proven generated handoff metadata for inspection:

- `no-code-screen-definition-v0`
- `no-code-runtime-v0`
- material insight traceability
- read-only screen list
- empty action/custom operation invariants

This is an inspection route, not a generated app route.

## Implementation boundary for #824

Reuse:

- `app_material_insight_preview_load()` to build and validate the material insight fixture.
- `app_material_insight_no_code_handoff()` to produce no-code metadata.
- `app_project_shared_contract_route_bootstrap()` for authenticated GET route bootstrap.
- `app_material_insight_preview_h()` or equivalent escaping for HTML.

Render stable markers:

- `data-material-insight-no-code-handoff="true"`
- `data-no-code-screen-definition-version="no-code-screen-definition-v0"`
- `data-no-code-runtime-version="no-code-runtime-v0"`
- `data-no-code-handoff-screen="material_entity_list"`
- `data-no-code-handoff-screen="material_qa_cards"`
- `data-no-code-handoff-actions="0"`
- `data-no-code-handoff-custom-operations="0"`
- `data-no-code-handoff-ai-call="false"`
- `data-no-code-handoff-mutation="false"`

Failure behavior:

- flag off: 404
- unauthenticated: login redirect
- non-`SAMPLE19` project: 404
- fixture/material insight/handoff error: 500 with escaped error marker

## Tests for #824

Fast PHPUnit route/HTML tests should cover:

- flag default-off behavior
- flag-on route registration/name
- marker rendering for versions and screens
- empty actions/custom operations
- no AI/mutation markers
- invalid project returns 404 or route-level not-found behavior

Browser smoke is not required for #824 unless route behavior needs real-stack evidence after fast tests. If browser evidence is added later, run it headless by default.

## Non-goals

Do not add:

- AI/Ollama calls
- import/apply/build/publish
- DB/config writes
- generated submit controls
- generated execution
- public no-code runtime publishing
