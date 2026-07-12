# Sample19 material insight preview preflight

Date: 2026-07-12

## Summary

#810 fixes the boundary for the first read-only material insight preview route.

The preview should render the validated Sample19 `material_insight_v0` artifact as human-readable Q&A cards plus a UI outline. It must remain fixture-backed, default-off, authenticated, and non-mutating.

## Route boundary

Recommended route:

- `GET /projects/SAMPLE19/material-insight`
- route name: `project_sample19_material_insight_preview`
- feature flag: `MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED`
- default state: off
- method: GET only
- auth: same project-admin authentication/authorization boundary used by Sample19 preview/review pages

The route should return not found when disabled or when project key is not `SAMPLE19`.

## Loader boundary

The loader reads only fixed repository fixtures:

- `sample/tutorials/sample19-json-first-content-model-demo/proposal/source/article.json`
- `sample/tutorials/sample19-json-first-content-model-demo/golden/schema-proposal.json`
- `sample/tutorials/sample19-json-first-content-model-demo/golden/canonical-schema-snapshot.json`

It then:

1. decodes the schema proposal;
2. builds `material_insight_v0`;
3. validates source/canonical hashes;
4. fails closed with a stable error marker if any stage fails.

No live canonical metadata snapshot, DB/config write, import, build, publish, or AI call belongs in this slice.

## Render boundary

The HTML should expose stable markers for fast tests:

- page root: `data-material-insight-preview="true"`;
- source hash: `data-material-insight-source-hash`;
- basis: `data-material-insight-basis`;
- each Q&A card: `data-material-insight-qa-card`;
- each UI outline screen: `data-material-insight-ui-screen`;
- prohibited actions: `data-material-insight-prohibited-action`;
- no generated execution controls.

## Test boundary

First slice tests should be fast PHP tests:

- feature flag default-off and truthy values;
- loader success from fixed Sample19 fixtures;
- loader fail-closed on bad source/canonical hash or invalid artifact;
- rendered HTML contains source/basis/Q&A/UI/prohibited markers;
- rendered HTML does not contain `data-runtime-execute`, `data-guarded-click-submit`, forms, apply/import/build/publish controls, or POST endpoints.

Browser smoke is not required until after the route is implemented and the first fast contract passes.

## Next lane

#811: implement the Sample19 material insight read-only preview route first slice.
