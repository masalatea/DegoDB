# Live Mtool Source Output Inspection Preflight

Status: `DONE`

## Result

The first G-L3 implementation boundary is fixed before code changes. It is a parallel, default-off, GET-only admin inspection route backed by real Source Output repository rows.

## Route and authorization

- Route: `GET /projects/MTOOL/source-outputs/no-code-inspection`.
- Route name: `project_source_outputs_no_code_inspection`.
- Site: `admin` only; lab/public exposure is not part of the first slice.
- Authentication and project authorization reuse `app_project_source_output_route_bootstrap($app, $request, ['GET'])`.
- The normalized project key must equal `MTOOL`; any other project returns not found rather than exposing a generic self-inspection surface.
- The specific route must be matched before the generic `/source-outputs/{source_output_key}` route.

This deliberately retains the canonical Source Output page's current `admin|config` role and `source_output.publish` permission boundary. Relaxing read permission is a separate policy decision.

## Default-off switch

`MTOOL_NO_CODE_SELF_INSPECTION_ENABLED` controls route availability. Only the normalized truthy values already used by local feature helpers should enable it; absence and malformed values remain false.

When disabled, the route returns not found and no repository read or generated render is performed. Rollback is to unset the switch and remove the optional link; no stored state changes.

## Live read adapter

The route calls `app_fetch_project_source_output_catalog($app, 'MTOOL')` exactly once. A small adapter maps each returned item to only the dogfooding contract fields:

- `source_output_key`;
- `name`;
- `class_type`;
- `artifact_strategy`;
- `target_binding_type`;
- `spec_visibility`;
- `source_output_dir`.

Unknown repository fields are ignored. No fixture fallback is allowed after repository failure. An empty successful catalog is a valid empty state; repository failure is an explicit server error.

## Rendering boundary

Use `app_no_code_mtool_dogfooding_probe_screen_definition($principal)` for the shared contract and `app_no_code_runtime_render_screen()` for live rows.

The first route renders only:

- `mtool_source_output_review_list` with all normalized rows;
- `mtool_source_output_review_detail` with the selected row, defaulting to the first row only when no selector is supplied.

The selector is `source_output_key` from the query string and must match a row exactly after standard key normalization. Unknown selectors fail closed to an explicit not-found/empty detail; they must not silently select another row.

The form screen is excluded because an editable-looking control would be misleading in a read-only workflow. Custom operations remain disabled and must not receive a binding, availability endpoint, or POST handler on this route. The page includes a clear return link to canonical `/projects/MTOOL/source-outputs`.

## Response and state behavior

| Case | Expected result |
| --- | --- |
| Switch off/malformed | 404, zero repository reads, zero rendered actions |
| Unauthenticated | Existing login redirect |
| Wrong site/role/project permission | Existing bootstrap forbidden behavior |
| Non-MTOOL project | 404 |
| Repository success with rows | 200, list plus selected detail |
| Repository success empty | 200, explicit empty list/detail state |
| Unknown `source_output_key` | Explicit missing selection, no fallback row |
| Repository failure | 500, error marker, no stale/fixture rows |
| Any rendered state | No form submit and zero executable action binding |

## Test boundary

The first implementation requires fast coverage for:

- route ordering and auth-required registration;
- default-off and non-MTOOL fail-closed behavior;
- declared-field mapping and ignored extra fields;
- real catalog success, empty, unknown selector, and repository error;
- list/detail output markers and canonical return link;
- absence of form, enabled custom action, runtime execution URL, and POST binding.

Run the full suite before the code commit. A browser smoke is not required in #757; it is the promotion gate after fast HTTP behavior is stable.

## Explicit exclusions

- No canonical admin-page replacement.
- No review-request persistence or audit append.
- No Source Output edit/create/delete/reorder/build operation.
- No public/current/alias publication.
- No lab route or broader project-general feature.
- No change to Source Output authorization semantics.

## Next

#757 implements this exact boundary. Any need for mutation, a broader permission, or a new generic runtime-data endpoint must return to planning rather than expanding the slice implicitly.
