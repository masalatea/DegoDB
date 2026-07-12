# Live Mtool Source Output Inspection First Implementation

Status: `FIRST_SLICE_DONE`

## Result

The first contained Mtool self no-code workflow now exists as a parallel, default-off, authenticated, GET-only Source Output inspection route.

`GET /projects/MTOOL/source-outputs/no-code-inspection` uses the existing dogfooding screen definition and current Source Output catalog data. It does not replace the canonical admin page or enable any generated mutation.

## Implementation

- Added the authenticated route `project_source_outputs_no_code_inspection` before the generic Source Output detail route.
- Added `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED`, defaulting off and accepting only explicit normalized truthy values.
- Reused `app_project_source_output_route_bootstrap()` for admin site, role, project, permission, and GET-only enforcement.
- Restricted the route to normalized project key `MTOOL`.
- Read `app_fetch_project_source_output_catalog()` once and mapped only:
  - `source_output_key`;
  - `name`;
  - `class_type`;
  - `artifact_strategy`;
  - `target_binding_type`;
  - `spec_visibility`;
  - `source_output_dir`.
- Rendered the shared generated list and detail screens with executable screen actions removed.
- Excluded the generated form screen and runtime execution binding.
- Added a canonical Source Outputs return link.

## Fail-closed behavior

- Switch off or malformed: route returns not found.
- Non-MTOOL project: not found.
- Unknown explicit selector: reports the missing selection and does not fall back to the first row.
- No selector: selects the first row only when one exists.
- Empty catalog: explicit empty list and detail state.
- Repository or generated-render failure: explicit 500 marker; no fixture/stale fallback.

## Fast coverage

The new integration test verifies:

- switch normalization and default-off behavior;
- route ordering and authentication registration;
- declared-field-only mapping;
- absent, exact, and unknown selection behavior;
- generated list/detail output and canonical link;
- absence of generated form, guarded submit, runtime execution binding, and execution URL;
- explicit empty and missing-selection states.

## Verification

- PHP syntax checks: passed.
- `git diff --check`: passed.
- Full suite: 430 tests, 13,916 assertions, 1 skipped.

One intermediate full-suite run exposed that the shared detail renderer represents an empty item as blank fields. The route now adds its own explicit empty-state marker, and the final full suite passes.

## Boundary

- No canonical admin-page replacement or forward navigation entry.
- No POST, Source Output mutation, review request, audit append, build, or publish.
- No public, current/alias, or lab exposure.
- No authorization relaxation.

## Next

#758 promotes the fast proof to the real admin stack with a small HTTP/browser smoke. It must prove disabled and unauthenticated behavior, authenticated real rows and selection, navigation back to the canonical page, and zero POST before G-L3 qualification is considered.
